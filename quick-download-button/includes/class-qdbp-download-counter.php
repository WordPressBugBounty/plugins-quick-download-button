<?php
defined( 'ABSPATH' ) || exit;

/**
 * Download Counter module.
 *
 * Injects a download count badge INSIDE <quick-download-button-info> for both
 * shortcode and Gutenberg block outputs.
 *
 * Visibility logic (most specific wins):
 *   Per-button show_count attr / block showCount attr
 *     → overrides global "Show download count" setting
 *   Global setting
 *     → default when no per-button override exists
 *
 * Shortcode attribute:
 *   show_count="1"  — always show for this button
 *   show_count="0"  — always hide for this button
 *   (omitted)       — inherit global setting
 *
 * Block attribute:
 *   showCount: true/false (set via "⚡ Pro Gates" panel toggle)
 *   When not explicitly set, inherits the global setting (determined server-side).
 */
class QDBP_Download_Counter {

    public static function init() {
        // Register per-button shortcode attribute.
        add_filter( 'qdb_shortcode_atts', array( __CLASS__, 'register_atts' ) );

        // Inject counter into final shortcode HTML.
        add_filter( 'qdb_shortcode_output', array( __CLASS__, 'inject_into_shortcode' ), 20, 2 );

        // AJAX endpoint so JS can refresh stale counts on cached pages.
        add_action( 'wp_ajax_qdbp_get_count',        array( __CLASS__, 'ajax_get_count' ) );
        add_action( 'wp_ajax_nopriv_qdbp_get_count', array( __CLASS__, 'ajax_get_count' ) );
    }

    /** Return the current download count for a button. */
    public static function ajax_get_count() {
        check_ajax_referer( 'qdbp_nonce', 'security' );
        $btn_id = isset( $_POST['btn_id'] ) ? sanitize_text_field( wp_unslash( $_POST['btn_id'] ) ) : '';
        if ( ! $btn_id ) {
            wp_send_json_error();
        }
        wp_send_json_success( array( 'count' => QDBP_Analytics::get_count( $btn_id ) ) );
    }

    /**
     * Register the show_count shortcode attribute.
     * Defaults to the global setting so existing shortcodes inherit it.
     */
    public static function register_atts( $atts, $raw = array() ) {
        $global = QDBP_Settings::get( 'show_download_count' ) ? '1' : '0';
        $atts['show_count'] = isset( $raw['show_count'] ) ? $raw['show_count'] : $global;
        return $atts;
    }

    /**
     * Inject counter badge inside <quick-download-button-info> for shortcodes.
     *
     * @param  string $html Final shortcode HTML.
     * @param  array  $atts Shortcode attributes (includes btn_id and show_count).
     * @return string
     */
    public static function inject_into_shortcode( $html, $atts ) {
        if ( empty( $atts['show_count'] ) || '0' === (string) $atts['show_count'] ) {
            return $html;
        }

        $btn_id = $atts['btn_id'] ?? '';
        if ( ! $btn_id ) return $html;

        $count = QDBP_Analytics::get_count( $btn_id );
        $badge = self::build_html( $count );

        // Add has-info to the class attribute.
        $html = preg_replace(
            '/(<quick-download-button-info[^>]*\bclass=")([^"]*)"/',
            '$1$2 has-info"',
            $html,
            1
        );
        // Inject badge content inside the element.
        // Uses preg_replace_callback so the label text (e.g. "$1 downloads")
        // is never misinterpreted as a regex back-reference.
        return preg_replace_callback(
            '/(<quick-download-button-info[^>]*>)(.*?)(<\/quick-download-button-info>)/s',
            function ( $m ) use ( $badge ) {
                return $m[1] . $m[2] . $badge . $m[3];
            },
            $html,
            1
        );
    }

    /**
     * Build the badge HTML string.
     * Used by QDBP_Block_Filter for Gutenberg blocks.
     *
     * @param  int $count
     * @return string
     */
    public static function build_html( $count ) {
        $label = QDBP_Settings::get( 'download_count_label', '{count} downloads' );
        $text  = str_replace( '{count}', number_format_i18n( $count ), $label );
        return '<div class="qdbp-dl-count">' . esc_html( $text ) . '</div>';
    }
}
