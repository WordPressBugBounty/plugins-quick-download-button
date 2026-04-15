<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin panel.
 *
 * Registers sub-menu pages under a top-level "QDB" menu.
 * Each page is a thin wrapper that loads the corresponding view template.
 */
class QDBP_Admin {

    public static function init() {
        require_once QDBN__PLUGIN_DIR . 'admin/class-qdbp-analytics-table.php';
        require_once QDBN__PLUGIN_DIR . 'admin/class-qdbp-leads-table.php';

        add_action( 'admin_menu',            array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_init',            array( __CLASS__, 'handle_early_actions' ) );
    }

    public static function handle_early_actions() {
        // CSV export runs before headers are sent
        QDBP_Leads_Table::maybe_export_csv();
    }

    public static function register_menus() {
        add_menu_page(
            __( 'Quick Download Button', 'quick-download-button' ),
            __( 'Quick Download', 'quick-download-button' ),
            'manage_options',
            'qdbp-dashboard',
            array( __CLASS__, 'render_overview' ),
            'dashicons-download',
            81
        );

        add_submenu_page(
            'qdbp-dashboard',
            __( 'Overview', 'quick-download-button' ),
            __( 'Overview', 'quick-download-button' ),
            'manage_options',
            'qdbp-dashboard',
            array( __CLASS__, 'render_overview' )
        );

        add_submenu_page(
            'qdbp-dashboard',
            __( 'Download Log', 'quick-download-button' ),
            __( 'Download Log', 'quick-download-button' ),
            'manage_options',
            'qdbp-log',
            array( __CLASS__, 'render_log' )
        );

        add_submenu_page(
            'qdbp-dashboard',
            __( 'Leads', 'quick-download-button' ),
            __( 'Leads', 'quick-download-button' ),
            'manage_options',
            'qdbp-leads',
            array( __CLASS__, 'render_leads' )
        );

        add_submenu_page(
            'qdbp-dashboard',
            __( 'Shortcode Builder', 'quick-download-button' ),
            __( 'Shortcode Builder', 'quick-download-button' ),
            'manage_options',
            'qdbp-builder',
            array( __CLASS__, 'render_builder' )
        );

        add_submenu_page(
            'qdbp-dashboard',
            __( 'Settings', 'quick-download-button' ),
            __( 'Settings', 'quick-download-button' ),
            'manage_options',
            'qdbp-settings',
            array( __CLASS__, 'render_settings' )
        );
    }

    public static function render_overview() {
        require_once QDBN__PLUGIN_DIR . 'admin/views/overview.php';
    }

    public static function render_log() {
        require_once QDBN__PLUGIN_DIR . 'admin/views/analytics.php';
    }

    public static function render_leads() {
        require_once QDBN__PLUGIN_DIR . 'admin/views/leads.php';
    }

    public static function render_builder() {
        require_once QDBN__PLUGIN_DIR . 'admin/views/shortcode-builder.php';
    }

    public static function render_settings() {
        require_once QDBN__PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Render the analytics section tab navigation.
     *
     * @param string $active  'overview' or 'log'
     */
    public static function analytics_nav( $active ) {
        $tabs = array(
            'overview' => array(
                'label' => __( 'Overview', 'quick-download-button' ),
                'url'   => admin_url( 'admin.php?page=qdbp-dashboard' ),
            ),
            'log'      => array(
                'label' => __( 'Download Log', 'quick-download-button' ),
                'url'   => admin_url( 'admin.php?page=qdbp-log' ),
            ),
        );
        echo '<nav class="qdbp-analytics-nav">';
        foreach ( $tabs as $key => $tab ) {
            $class = ( $key === $active ) ? ' qdbp-analytics-nav__tab--active' : '';
            printf(
                '<a href="%s" class="qdbp-analytics-nav__tab%s">%s</a>',
                esc_url( $tab['url'] ),
                esc_attr( $class ),
                esc_html( $tab['label'] )
            );
        }
        echo '</nav>';
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'qdbp' ) === false ) return;

        wp_enqueue_style(
            'qdbp-admin',
            QDBN__PLUGIN_URI . 'assets/css/pro-admin.css',
            array(),
            QDBN__VERSION
        );

        wp_enqueue_script(
            'qdbp-admin',
            QDBN__PLUGIN_URI . 'assets/js/pro-admin.js',
            array( 'jquery' ),
            QDBN__VERSION,
            true
        );

        if ( strpos( $hook, 'qdbp-builder' ) !== false ) {
            wp_enqueue_media();
        }
    }
}
