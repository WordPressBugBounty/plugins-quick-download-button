<?php
defined( 'ABSPATH' ) || exit;

/**
 * Expiring / Signed Links module.
 *
 * Generates HMAC-signed download tokens that expire after a time window
 * and/or a maximum number of clicks. The signed token is validated in
 * qdb_can_download before the file is served (cannot be bypassed).
 *
 * Flow:
 *   1. Render time — injects data-qdb-expiry="1" onto the button and stores
 *      expiry settings in a transient keyed by btn_id.
 *   2. JS click    — Pro frontend calls AJAX qdbp_get_token to obtain a
 *      short-lived signed token, then appends &qdbp_token=TOKEN to the URL.
 *   3. Server side — validate_token() is called by qdb_can_download; if the
 *      token is missing, expired, or click-limited, download is blocked.
 *   4. Post-download — record_click() increments the click counter.
 *
 * Shortcode attributes: expiry_hours, expiry_clicks, expiry_msg
 * Block attributes:     expiryHours,  expiryClicks,  expiryMsg
 */
class QDBP_Expiring_Links {

    const OPTION_SECRET  = 'qdbp_link_secret';
    const TABLE          = 'qdb_link_tokens';
    const SETTINGS_TTL   = DAY_IN_SECONDS;

    public static function init() {
        // Ensure a persistent HMAC secret exists.
        if ( ! get_option( self::OPTION_SECRET ) ) {
            update_option( self::OPTION_SECRET, wp_generate_password( 64, true, true ) );
        }

        add_filter( 'qdb_shortcode_atts',   array( __CLASS__, 'register_atts'  ), 10, 1 );
        add_filter( 'qdb_button_data_atts', array( __CLASS__, 'add_data_attr'  ), 10, 2 );
        add_filter( 'qdb_can_download',     array( __CLASS__, 'validate_token' ), 20, 2 );
        add_action( 'qdb_before_download',  array( __CLASS__, 'record_click'   ), 10, 1 );

        add_action( 'wp_ajax_qdbp_get_token',        array( __CLASS__, 'ajax_get_token' ) );
        add_action( 'wp_ajax_nopriv_qdbp_get_token', array( __CLASS__, 'ajax_get_token' ) );

        // Piggyback on the existing daily cleanup cron.
        add_action( 'qdbp_daily_cleanup', array( __CLASS__, 'cleanup_expired_tokens' ) );
    }

    // ── Shortcode attribute registration ───────────────────────────────────

    public static function register_atts( $atts, $raw = array() ) {
        $atts['expiry_hours']  = isset( $raw['expiry_hours'] )  ? (int) $raw['expiry_hours']  : 0;
        $atts['expiry_clicks'] = isset( $raw['expiry_clicks'] ) ? (int) $raw['expiry_clicks'] : 0;
        $atts['expiry_msg']    = isset( $raw['expiry_msg'] )
            ? $raw['expiry_msg']
            : __( 'This download link has expired.', 'quick-download-button' );
        return $atts;
    }

    // ── Shortcode: inject data-qdb-expiry onto the button ──────────────────

    public static function add_data_attr( $extra, $atts ) {
        $hours  = (int) ( $atts['expiry_hours']  ?? 0 );
        $clicks = (int) ( $atts['expiry_clicks'] ?? 0 );

        if ( $hours <= 0 && $clicks <= 0 ) return $extra;

        $btn_id = $atts['btn_id'] ?? '';
        $msg    = $atts['expiry_msg'] ?? '';

        if ( $btn_id ) {
            self::store_settings( $btn_id, $hours, $clicks, $msg );
        }

        $extra['data-qdb-expiry'] = '1';
        return $extra;
    }

    /**
     * Store per-button expiry settings as a transient so ajax_get_token()
     * and validate_token() can enforce them without the shortcode attributes.
     */
    public static function store_settings( $btn_id, $hours, $clicks, $msg = '' ) {
        set_transient( 'qdbp_expiry_' . $btn_id, array(
            'hours'  => (int) $hours,
            'clicks' => (int) $clicks,
            'msg'    => $msg,
        ), self::SETTINGS_TTL );
    }

    // ── AJAX: generate a token on demand ───────────────────────────────────

    public static function ajax_get_token() {
        check_ajax_referer( 'qdbp_nonce', 'security' );

        $btn_id = isset( $_POST['btn_id'] ) ? sanitize_text_field( wp_unslash( $_POST['btn_id'] ) ) : '';
        if ( ! $btn_id ) {
            wp_send_json_error( array( 'message' => 'Missing button ID.' ) );
        }

        $settings = get_transient( 'qdbp_expiry_' . $btn_id );
        if ( ! $settings ) {
            wp_send_json_error( array( 'message' => 'Expiry settings not found.' ) );
        }

        $token = self::generate_token(
            $btn_id,
            (int) $settings['hours'],
            (int) $settings['clicks'],
            $settings['msg'] ?? ''
        );

        wp_send_json_success( array( 'token' => $token ) );
    }

    // ── Token generation ───────────────────────────────────────────────────

    /**
     * Generate a signed token and store it in the DB.
     *
     * @param  string $btn_id
     * @param  int    $expiry_hours  0 = no time limit
     * @param  int    $expiry_clicks 0 = unlimited
     * @param  string $expiry_msg    Message shown when link has expired
     * @return string Opaque token to append to the redirect URL
     */
    public static function generate_token( $btn_id, $expiry_hours = 0, $expiry_clicks = 0, $expiry_msg = '' ) {
        global $wpdb;
        $secret  = get_option( self::OPTION_SECRET );
        $expires = $expiry_hours > 0 ? time() + ( $expiry_hours * HOUR_IN_SECONDS ) : 0;
        $token   = bin2hex( random_bytes( 16 ) );
        $sig     = hash_hmac( 'sha256', $token, $secret );

        $wpdb->insert(
            $wpdb->prefix . self::TABLE,
            array(
                'token'      => $token,
                'signature'  => $sig,
                'btn_id'     => $btn_id,
                'expires_at' => $expires > 0 ? gmdate( 'Y-m-d H:i:s', $expires ) : null,
                'max_clicks' => (int) $expiry_clicks,
                'click_count'=> 0,
                'expiry_msg' => $expiry_msg,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
        );
        return $token;
    }

    // ── Server-side enforcement ─────────────────────────────────────────────

    /**
     * Validate the token present in the download request.
     * Only blocks when a token IS present — links without tokens pass through
     * (non-expiring buttons are unaffected).
     */
    public static function validate_token( $can_download, $context ) {
        if ( ! $can_download ) return false;

        $token = isset( $_GET['qdbp_token'] ) ? sanitize_text_field( wp_unslash( $_GET['qdbp_token'] ) ) : '';
        if ( ! $token ) return $can_download; // No token — not an expiring link.

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE token = %s LIMIT 1", $token ) ); // phpcs:ignore

        if ( ! $row ) {
            wp_die(
                esc_html__( 'Invalid or expired download link.', 'quick-download-button' ),
                esc_html__( 'Link Invalid', 'quick-download-button' ),
                array( 'response' => 403 )
            );
        }

        $secret = get_option( self::OPTION_SECRET );
        if ( ! hash_equals( hash_hmac( 'sha256', $row->token, $secret ), $row->signature ) ) {
            wp_die(
                esc_html__( 'Invalid download link.', 'quick-download-button' ),
                esc_html__( 'Link Invalid', 'quick-download-button' ),
                array( 'response' => 403 )
            );
        }

        $expiry_msg = ! empty( $row->expiry_msg )
            ? $row->expiry_msg
            : __( 'This download link has expired.', 'quick-download-button' );

        if ( $row->expires_at && strtotime( $row->expires_at ) < time() ) {
            wp_die(
                esc_html( $expiry_msg ),
                esc_html__( 'Link Expired', 'quick-download-button' ),
                array( 'response' => 403 )
            );
        }

        if ( $row->max_clicks > 0 && $row->click_count >= $row->max_clicks ) {
            wp_die(
                esc_html( $expiry_msg ),
                esc_html__( 'Download Limit Reached', 'quick-download-button' ),
                array( 'response' => 403 )
            );
        }

        return true;
    }

    /** Increment click count after a valid download. */
    public static function record_click( $context ) {
        $token = isset( $_GET['qdbp_token'] ) ? sanitize_text_field( wp_unslash( $_GET['qdbp_token'] ) ) : '';
        if ( ! $token ) return;

        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET click_count = click_count + 1 WHERE token = %s", $token ) ); // phpcs:ignore
    }

    // ── Token cleanup ──────────────────────────────────────────────────────

    /**
     * Delete tokens that are no longer usable — expired by time or max clicks.
     * Called daily via the qdbp_daily_cleanup cron action.
     */
    public static function cleanup_expired_tokens() {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;
        $wpdb->query( // phpcs:ignore
            $wpdb->prepare(
                "DELETE FROM {$table}
                 WHERE ( expires_at IS NOT NULL AND expires_at < %s )
                    OR ( max_clicks > 0 AND click_count >= max_clicks )",
                current_time( 'mysql' )
            )
        );
    }

    // ── Table creation ─────────────────────────────────────────────────────

    public static function create_table() {
        global $wpdb;
        $table           = $wpdb->prefix . self::TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            token       VARCHAR(64)         NOT NULL,
            signature   VARCHAR(64)         NOT NULL,
            btn_id      VARCHAR(64)         NOT NULL DEFAULT '',
            expires_at  DATETIME            NULL,
            max_clicks  INT UNSIGNED        NOT NULL DEFAULT 0,
            click_count INT UNSIGNED        NOT NULL DEFAULT 0,
            expiry_msg  TEXT                NOT NULL,
            created_at  DATETIME            NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY token (token),
            KEY btn_id (btn_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
