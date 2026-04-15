<?php
defined( 'ABSPATH' ) || exit;

/**
 * Bootstraps all feature modules and registers their hooks.
 *
 * Each module is self-contained. To disable a feature, comment out
 * its require_once and hook registration below.
 */
class QDBP_Loader {

    public static function init() {

        // ── Feature modules ────────────────────────────────────────────────
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-settings.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-analytics.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-email-gate.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-download-limit.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-passcode.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-expiring-links.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-block-filter.php';
        require_once QDBN__PLUGIN_DIR . 'includes/class-qdbp-download-counter.php';

        // ── Admin ──────────────────────────────────────────────────────────
        if ( is_admin() ) {
            require_once QDBN__PLUGIN_DIR . 'admin/class-qdbp-admin.php';
            QDBP_Admin::init();
        }

        // ── Ensure tables exist (handles missed activation hook / schema bump) ─
        if ( get_option( 'qdbp_db_version' ) !== QDBN__DB_VERSION ) {
            QDBP_Analytics::create_table();
            QDBP_Email_Gate::create_table();
            QDBP_Expiring_Links::create_table();
            QDBP_Analytics::backfill_counts();
            QDBP_Analytics::schedule_cleanup();
            update_option( 'qdbp_db_version', QDBN__DB_VERSION );
        }

        // ── Boot each module ───────────────────────────────────────────────
        QDBP_Analytics::init();
        QDBP_Email_Gate::init();
        QDBP_Download_Limit::init();
        QDBP_Passcode::init();
        QDBP_Expiring_Links::init();
        QDBP_Block_Filter::init();
        QDBP_Download_Counter::init();

        // ── Enqueue frontend assets ────────────────────────────────────────
        add_action( 'wp_enqueue_scripts',          array( __CLASS__, 'enqueue_frontend' ) );
        add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor' ) );
    }

    public static function enqueue_frontend() {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! function_exists( 'has_quick_download_button' ) ) return;
        if ( ! has_quick_download_button( $post->ID ) ) return;

        wp_enqueue_script(
            'qdbp-frontend',
            QDBN__PLUGIN_URI . 'assets/js/pro-frontend.js',
            array( 'quick-download-button-frontend-script' ),
            QDBN__VERSION,
            true
        );

        wp_enqueue_style(
            'qdbp-frontend-style',
            QDBN__PLUGIN_URI . 'assets/css/pro-frontend.css',
            array( 'quick-download-button-front-end-styles' ),
            QDBN__VERSION
        );

        /**
         * Filter: qdbp_localize_data
         *
         * Modify data passed to the frontend script.
         *
         * @param array $data Localized data.
         */
        $data = apply_filters( 'qdbp_localize_data', array(
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'security'    => wp_create_nonce( 'qdbp_nonce' ),
            'count_label' => QDBP_Settings::get( 'download_count_label', '{count} downloads' ),
        ) );
        wp_localize_script( 'qdbp-frontend', 'qdbp_data', $data );
    }

    public static function enqueue_editor() {
        wp_enqueue_script(
            'qdbp-editor',
            QDBN__PLUGIN_URI . 'assets/js/pro-editor.js',
            array( 'wp-hooks', 'wp-compose', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n' ),
            QDBN__VERSION,
            true
        );
        wp_localize_script( 'qdbp-editor', 'qdbp_editor_data', array(
            'show_download_count' => (bool) QDBP_Settings::get( 'show_download_count' ),
        ) );
    }
}
