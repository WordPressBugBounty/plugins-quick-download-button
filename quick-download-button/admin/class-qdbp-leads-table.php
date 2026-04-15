<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WP_List_Table for captured email leads.
 */
class QDBP_Leads_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'lead',
            'plural'   => 'leads',
            'ajax'     => false,
        ) );
    }

    // ── Column definitions ──────────────────────────────────────────────────

    public function get_columns() {
        return array(
            'cb'         => '<input type="checkbox" />',
            'email'      => __( 'Email', 'quick-download-button' ),
            'user'       => __( 'User', 'quick-download-button' ),
            'ip'         => __( 'IP Address', 'quick-download-button' ),
            'source_url' => __( 'Source Page', 'quick-download-button' ),
            'created_at' => __( 'Date', 'quick-download-button' ),
        );
    }

    public function get_sortable_columns() {
        return array(
            'email'      => array( 'email', false ),
            'created_at' => array( 'created_at', true ),
        );
    }

    protected function get_bulk_actions() {
        return array(
            'delete' => __( 'Delete', 'quick-download-button' ),
        );
    }

    // ── Column renderers ────────────────────────────────────────────────────

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="lead_id[]" value="%d" />', absint( $item->id ) );
    }

    protected function column_email( $item ) {
        return '<a href="mailto:' . esc_attr( $item->email ) . '">' . esc_html( $item->email ) . '</a>';
    }

    protected function column_user( $item ) {
        if ( $item->user_id ) {
            $user = get_userdata( $item->user_id );
            return $user ? esc_html( $user->user_login ) : '#' . absint( $item->user_id );
        }
        return __( 'Guest', 'quick-download-button' );
    }

    protected function column_ip( $item ) {
        return esc_html( $item->ip );
    }

    protected function column_source_url( $item ) {
        if ( ! $item->source_url ) return '—';
        $label = strlen( $item->source_url ) > 55
            ? substr( $item->source_url, 0, 52 ) . '…'
            : $item->source_url;
        return '<a href="' . esc_url( $item->source_url ) . '" target="_blank">' . esc_html( $label ) . '</a>';
    }

    protected function column_created_at( $item ) {
        return esc_html( get_date_from_gmt( $item->created_at, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) );
    }

    protected function column_default( $item, $column_name ) {
        return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '—';
    }

    // ── Data ────────────────────────────────────────────────────────────────

    public function prepare_items() {
        global $wpdb;
        $table = $wpdb->prefix . QDBP_Email_Gate::TABLE;

        $per_page     = 25;
        $current_page = $this->get_pagenum();
        $orderby      = isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array( 'email', 'created_at' ), true )
            ? sanitize_key( $_GET['orderby'] ) : 'created_at';
        $order        = isset( $_GET['order'] ) && 'asc' === strtolower( $_GET['order'] ) ? 'ASC' : 'DESC';

        // Search
        $search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
        $where  = $search
            ? $wpdb->prepare( ' WHERE email LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' )
            : '';

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}{$where}" ); // phpcs:ignore

        $this->set_pagination_args( array(
            'total_items' => $total,
            'per_page'    => $per_page,
        ) );

        $offset = ( $current_page - 1 ) * $per_page;
        $sql    = "SELECT * FROM {$table}{$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d"; // phpcs:ignore

        $this->items = $wpdb->get_results( $wpdb->prepare( $sql, $per_page, $offset ) ); // phpcs:ignore

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
    }

    // ── Bulk action handler ─────────────────────────────────────────────────

    public static function process_bulk_action() {
        if ( empty( $_POST['lead_id'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( ! check_admin_referer( 'bulk-leads' ) ) return;

        $action = isset( $_POST['action'] ) ? $_POST['action'] : ( isset( $_POST['action2'] ) ? $_POST['action2'] : '' );
        if ( 'delete' !== $action ) return;

        global $wpdb;
        $table = $wpdb->prefix . QDBP_Email_Gate::TABLE;
        $ids   = array_map( 'absint', (array) $_POST['lead_id'] );

        foreach ( $ids as $id ) {
            $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
        }
    }

    // ── CSV export ──────────────────────────────────────────────────────────

    public static function maybe_export_csv() {
        if ( empty( $_GET['qdbp_export'] ) || 'leads' !== $_GET['qdbp_export'] ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        check_admin_referer( 'qdbp_export_leads' );

        global $wpdb;
        $table = $wpdb->prefix . QDBP_Email_Gate::TABLE;
        $rows  = $wpdb->get_results( "SELECT email, user_id, ip, source_url, created_at FROM {$table} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="qdb-leads-' . gmdate( 'Y-m-d' ) . '.csv"' );
        header( 'Pragma: no-cache' );

        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, array( 'Email', 'User ID', 'IP', 'Source URL', 'Date' ) );
        foreach ( $rows as $row ) {
            fputcsv( $out, $row );
        }
        fclose( $out );
        exit;
    }
}
