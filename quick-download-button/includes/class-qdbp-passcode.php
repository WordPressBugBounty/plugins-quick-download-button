<?php
defined( 'ABSPATH' ) || exit;

/**
 * Passcode Protection module.
 *
 * A button can require a passcode before the download starts.
 * The code can be entered via an inline form (shown by Pro JS) or
 * pre-supplied in the URL as ?qdb_code=<code> (useful for sharing links).
 *
 * Once unlocked, a session cookie stores the cleared state for that
 * button ID so the visitor is not prompted again.
 *
 * Shortcode attributes:
 *   passcode         — the required code (plain text, stored in post meta / options)
 *   passcode_label   — input label
 *   passcode_btn_txt — submit button text
 *   passcode_error   — wrong code error message
 *
 * Hooks used (free plugin):
 *   qdb_shortcode_atts   — register shortcode attributes.
 *   qdb_button_data_atts — inject data-qdb-passcode="1" when a code is set.
 *   qdb_gate_html        — output the hidden passcode form inside the wrapper.
 */
class QDBP_Passcode {

    public static function init() {
        add_filter( 'qdb_shortcode_atts',   array( __CLASS__, 'register_atts' ), 10, 2 );
        add_filter( 'qdb_button_data_atts', array( __CLASS__, 'add_data_attr' ), 10, 2 );
        add_action( 'qdb_gate_html',        array( __CLASS__, 'output_gate_html' ) );
        add_filter( 'qdbp_localize_data',   array( __CLASS__, 'localize_data' ) );

        add_action( 'wp_ajax_qdbp_verify_passcode',        array( __CLASS__, 'handle_ajax' ) );
        add_action( 'wp_ajax_nopriv_qdbp_verify_passcode', array( __CLASS__, 'handle_ajax' ) );
    }

    public static function register_atts( $atts, $raw = array() ) {
        $atts['passcode']         = isset( $raw['passcode'] )         ? $raw['passcode']         : '';
        $atts['passcode_label']   = isset( $raw['passcode_label'] )   ? $raw['passcode_label']   : __( 'Enter passcode to download', 'quick-download-button' );
        $atts['passcode_btn_txt'] = isset( $raw['passcode_btn_txt'] ) ? $raw['passcode_btn_txt'] : __( 'Unlock', 'quick-download-button' );
        $atts['passcode_error']   = isset( $raw['passcode_error'] )   ? $raw['passcode_error']   : __( 'Incorrect passcode. Please try again.', 'quick-download-button' );
        return $atts;
    }

    public static function add_data_attr( $extra, $atts ) {
        if ( empty( $atts['passcode'] ) ) return $extra;

        $btn_id = isset( $atts['btn_id'] ) ? $atts['btn_id'] : '';

        // Auto-unlock via URL param
        $url_code = isset( $_GET['qdb_code'] ) ? sanitize_text_field( wp_unslash( $_GET['qdb_code'] ) ) : '';
        if ( $url_code && hash_equals( $atts['passcode'], $url_code ) ) {
            self::set_unlock_cookie( $btn_id );
            return $extra;
        }

        if ( self::is_unlocked( $btn_id ) ) return $extra;

        $extra['data-qdb-passcode'] = '1';
        return $extra;
    }

    public static function output_gate_html( $atts ) {
        if ( empty( $atts['passcode'] ) ) return;
        $btn_id = isset( $atts['btn_id'] ) ? $atts['btn_id'] : '';
        if ( self::is_unlocked( $btn_id ) ) return;

        // Store the expected passcode and custom error message server-side.
        // Always overwrite so a changed passcode takes effect immediately.
        set_transient( 'qdbp_pc_' . $btn_id, array(
            'code'  => $atts['passcode'],
            'error' => $atts['passcode_error'] ?? '',
        ), DAY_IN_SECONDS );
        ?>
        <div class="qdbp-gate qdbp-passcode-gate" hidden>
            <form class="qdbp-passcode-form">
                <label class="qdbp-gate-label"><?php echo esc_html( $atts['passcode_label'] ); ?></label>
                <div class="qdbp-passcode-field-row">
                    <input type="password" class="qdbp-passcode-input" placeholder="••••••" required />
                    <button type="submit" class="qdbp-gate-submit"><?php echo esc_html( $atts['passcode_btn_txt'] ); ?></button>
                </div>
                <div class="qdbp-gate-error" hidden></div>
            </form>
        </div>
        <?php
    }

    public static function handle_ajax() {
        check_ajax_referer( 'qdbp_nonce', 'security' );

        $submitted = isset( $_POST['passcode'] ) ? sanitize_text_field( wp_unslash( $_POST['passcode'] ) ) : '';
        $btn_id    = isset( $_POST['btn_id'] )   ? sanitize_text_field( wp_unslash( $_POST['btn_id'] ) )   : '';

        if ( ! $submitted || ! $btn_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'quick-download-button' ) ) );
        }

        // Look up the expected passcode stored server-side via transient.
        // The passcode is never sent to the browser — only the btn_id is.
        $stored = get_transient( 'qdbp_pc_' . $btn_id );

        // Support legacy string format (stored before array format was introduced).
        if ( is_string( $stored ) ) {
            $expected  = $stored;
            $error_msg = __( 'Incorrect passcode. Please try again.', 'quick-download-button' );
        } else {
            $expected  = $stored['code']  ?? '';
            $error_msg = ! empty( $stored['error'] )
                ? $stored['error']
                : __( 'Incorrect passcode. Please try again.', 'quick-download-button' );
        }

        if ( ! $expected || ! hash_equals( (string) $expected, $submitted ) ) {
            wp_send_json_error( array( 'message' => $error_msg ) );
        }

        self::set_unlock_cookie( $btn_id );
        wp_send_json_success();
    }

    private static function cookie_key( $btn_id ) {
        return 'qdbp_passcode_' . $btn_id;
    }

    public static function is_unlocked( $btn_id ) {
        return ! empty( $_COOKIE[ self::cookie_key( $btn_id ) ] );
    }

    private static function set_unlock_cookie( $btn_id ) {
        $key    = self::cookie_key( $btn_id );
        $expiry = time() + ( 30 * DAY_IN_SECONDS );
        setcookie( $key, '1', $expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
    }

    public static function localize_data( $data ) {
        $data['passcode_ajax'] = 'qdbp_verify_passcode';
        return $data;
    }
}
