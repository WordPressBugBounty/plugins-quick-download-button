<?php
defined( 'ABSPATH' ) || exit;

/**
 * Download Limit module.
 *
 * Caps downloads per IP (and per user if logged in) over a rolling window.
 *
 * Enforcement happens at two layers:
 *  1. Render time  — injects data-qdb-limit-reached onto the button AND stores
 *                    limit settings in a transient keyed by btn_id.
 *  2. Server side  — qdb_can_download reads the transient and re-checks the
 *                    count before the file is served (cannot be bypassed by JS).
 *
 * Count logic: always checks by IP.  If the visitor is logged in, also checks
 * by user_id and uses the higher of the two counts.  This prevents a logged-in
 * user from bypassing the limit by logging out (or vice-versa).
 */
class QDBP_Download_Limit {

    /** Transient TTL for stored limit settings (keyed by btn_id). */
    const SETTINGS_TTL = DAY_IN_SECONDS;

    public static function init() {
        add_filter( 'qdb_shortcode_atts',   array( __CLASS__, 'register_atts'  ), 10, 2 );
        add_filter( 'qdb_button_data_atts', array( __CLASS__, 'add_data_attr'  ), 10, 2 );
        add_filter( 'qdb_can_download',     array( __CLASS__, 'check_limit'    ), 10, 2 );
    }

    public static function register_atts( $atts, $raw = array() ) {
        $atts['dl_limit']        = isset( $raw['dl_limit'] )        ? (int) $raw['dl_limit']        : 0;
        $atts['dl_limit_window'] = isset( $raw['dl_limit_window'] ) ? (int) $raw['dl_limit_window'] : 24;
        $atts['dl_limit_msg']    = isset( $raw['dl_limit_msg'] )
            ? $raw['dl_limit_msg']
            : __( 'You have reached the download limit. Please try again later.', 'quick-download-button' );
        return $atts;
    }

    public static function add_data_attr( $extra, $atts ) {
        if ( empty( $atts['dl_limit'] ) || (int) $atts['dl_limit'] <= 0 ) return $extra;

        $btn_id  = $atts['btn_id'] ?? '';
        $limit   = (int) $atts['dl_limit'];
        $window  = (int) $atts['dl_limit_window'];
        $ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        $user_id = get_current_user_id();

        // Store settings server-side so check_limit() can enforce them without
        // needing the shortcode attributes at download time.
        if ( $btn_id ) {
            self::store_settings( $btn_id, $limit, $window, $atts['dl_limit_msg'] );
        }

        $count = self::get_download_count( $ip, $user_id, $window, $btn_id );

        if ( $count >= $limit ) {
            $extra['data-qdb-limit-reached'] = '1';
            $extra['data-qdb-limit-msg']     = esc_attr( $atts['dl_limit_msg'] );
        }
        return $extra;
    }

    /**
     * Server-side enforcement via qdb_can_download.
     * Reads stored settings from the transient — cannot be bypassed by JS.
     */
    public static function check_limit( $can_download, $context ) {
        if ( ! $can_download ) return false;

        $btn_id = $context['btn_id'] ?? '';
        if ( ! $btn_id ) return $can_download;

        $settings = get_transient( 'qdbp_dlimit_' . $btn_id );
        if ( ! $settings || empty( $settings['limit'] ) ) return $can_download;

        $ip      = $context['ip']      ?? '';
        $user_id = (int) ( $context['user_id'] ?? 0 );
        $count   = self::get_download_count( $ip, $user_id, $settings['window'], $btn_id );

        return $count < $settings['limit'];
    }

    /**
     * Store per-button limit settings as a transient so check_limit() can
     * enforce them at download time without the shortcode attributes.
     */
    public static function store_settings( $btn_id, $limit, $window, $msg = '' ) {
        set_transient( 'qdbp_dlimit_' . $btn_id, array(
            'limit'  => (int) $limit,
            'window' => (int) $window,
            'msg'    => $msg,
        ), self::SETTINGS_TTL );
    }

    /** Public wrapper used by QDBP_Block_Filter. */
    public static function get_count_for_ip( $ip, $user_id, $window_hours, $btn_id = '' ) {
        return self::get_download_count( $ip, $user_id, $window_hours, $btn_id );
    }

    /**
     * Count downloads within the rolling window.
     *
     * Always checks by IP.  For logged-in users, also checks by user_id and
     * returns the higher value so neither login state can be used to bypass.
     *
     * Queries are scoped to btn_id when available for accuracy.
     */
    private static function get_download_count( $ip, $user_id, $window_hours, $btn_id = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . QDBP_Analytics::TABLE;
        $since = gmdate( 'Y-m-d H:i:s', time() - ( (int) $window_hours * HOUR_IN_SECONDS ) );

        $btn_clause    = $btn_id ? $wpdb->prepare( ' AND btn_id = %s', $btn_id ) : '';
        $ip_clause     = $wpdb->prepare( 'ip = %s AND downloaded_at >= %s', $ip, $since );
        $ip_count      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$ip_clause}{$btn_clause}" ); // phpcs:ignore

        if ( $user_id > 0 ) {
            $user_clause  = $wpdb->prepare( 'user_id = %d AND downloaded_at >= %s', $user_id, $since );
            $user_count   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$user_clause}{$btn_clause}" ); // phpcs:ignore
            return max( $ip_count, $user_count );
        }

        return $ip_count;
    }
}
