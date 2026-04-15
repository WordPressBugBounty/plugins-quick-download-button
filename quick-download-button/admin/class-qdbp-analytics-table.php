<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WP_List_Table for the download analytics log.
 *
 * Joins qdb_download_log with qdb_download_counts so each row shows the
 * cumulative total for that button alongside the individual event detail.
 * The total_downloads column is sortable and reads from the permanent
 * counts table, so it survives log deletion.
 */
class QDBP_Analytics_Table extends WP_List_Table {

    /** @var string Date range filter: today|7days|30days|all */
    private $date_filter;

    public function __construct() {
        parent::__construct( array(
            'singular' => 'download',
            'plural'   => 'downloads',
            'ajax'     => false,
        ) );
        $this->date_filter = isset( $_GET['date_range'] ) ? sanitize_key( $_GET['date_range'] ) : 'all';
    }

    // ── Column definitions ──────────────────────────────────────────────────

    public function get_columns() {
        return array(
            'cb'               => '<input type="checkbox" />',
            'file'             => __( 'File / URL', 'quick-download-button' ),
            'total_downloads'  => __( 'Total Downloads', 'quick-download-button' ),
            'user'             => __( 'User', 'quick-download-button' ),
            'ip'               => __( 'IP Address', 'quick-download-button' ),
            'downloaded_at'    => __( 'Date', 'quick-download-button' ),
        );
    }

    public function get_sortable_columns() {
        return array(
            'file'            => array( 'btn_id', false ),
            'total_downloads' => array( 'total_downloads', false ),
            'downloaded_at'   => array( 'downloaded_at', true ),
        );
    }

    protected function get_bulk_actions() {
        return array(
            'delete' => __( 'Delete', 'quick-download-button' ),
        );
    }

    // ── Column renderers ────────────────────────────────────────────────────

    protected function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="log_id[]" value="%d" />', absint( $item->id ) );
    }

    protected function column_file( $item ) {
        $file    = '—';
        $file_id = '';

        if ( $item->attachment_id ) {
            $title   = get_the_title( $item->attachment_id );
            $url     = wp_get_attachment_url( $item->attachment_id );
            $name    = $title ?: ( $url ? basename( $url ) : '#' . $item->attachment_id );
            $file    = $url
                ? '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $name ) . '</a>'
                : esc_html( $name );
            $file_id = ' <span class="qdbp-file-id">#' . absint( $item->attachment_id ) . '</span>';
        } elseif ( $item->external_url ) {
            $label = strlen( $item->external_url ) > 60
                ? substr( $item->external_url, 0, 57 ) . '…'
                : $item->external_url;
            $file  = '<a href="' . esc_url( $item->external_url ) . '" target="_blank">' . esc_html( $label ) . '</a>';
        }

        // Button ID sub-line
        $btn = $item->btn_id
            ? '<br><span class="qdbp-btn-id">btn: <code>' . esc_html( $item->btn_id ) . '</code></span>'
            : '';

        // Source page sub-line — only when recorded (new rows only)
        $page = '';
        if ( ! empty( $item->page_url ) ) {
            $parsed = wp_parse_url( $item->page_url );
            $short  = ( $parsed['host'] ?? '' ) . ( $parsed['path'] ?? '' );
            if ( strlen( $short ) > 52 ) {
                $short = substr( $short, 0, 49 ) . '…';
            }
            $page = '<br><span class="qdbp-page-url">'
                . '<a href="' . esc_url( $item->page_url ) . '" target="_blank" title="' . esc_attr( $item->page_url ) . '">'
                . esc_html( $short )
                . '</a></span>';
        }

        return $file . $file_id . $btn . $page;
    }

    protected function column_total_downloads( $item ) {
        $total = (int) $item->total_downloads;
        return '<strong>' . number_format_i18n( $total ) . '</strong>';
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

    protected function column_downloaded_at( $item ) {
        return esc_html( get_date_from_gmt(
            $item->downloaded_at,
            get_option( 'date_format' ) . ' ' . get_option( 'time_format' )
        ) );
    }

    protected function column_default( $item, $column_name ) {
        return isset( $item->$column_name ) ? esc_html( $item->$column_name ) : '—';
    }

    // ── Data ────────────────────────────────────────────────────────────────

    public function prepare_items() {
        global $wpdb;

        $log    = $wpdb->prefix . QDBP_Analytics::TABLE;
        $counts = $wpdb->prefix . QDBP_Analytics::COUNTS_TABLE;

        $per_page     = 25;
        $current_page = $this->get_pagenum();

        $allowed_orderby = array( 'btn_id', 'downloaded_at', 'total_downloads' );
        $orderby = isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], $allowed_orderby, true )
            ? sanitize_key( $_GET['orderby'] )
            : 'downloaded_at';
        $order   = isset( $_GET['order'] ) && 'asc' === strtolower( $_GET['order'] ) ? 'ASC' : 'DESC';

        // Map sortable column to SQL expression
        $orderby_sql = 'l.downloaded_at';
        if ( 'total_downloads' === $orderby ) {
            $orderby_sql = 'total_downloads';
        } elseif ( 'btn_id' === $orderby ) {
            $orderby_sql = 'l.btn_id';
        }

        $where = $this->date_where_clause();

        $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log} l{$where}" ); // phpcs:ignore

        $this->set_pagination_args( array(
            'total_items' => $total,
            'per_page'    => $per_page,
        ) );

        $offset = ( $current_page - 1 ) * $per_page;

        // JOIN counts table so total_downloads is available per row.
        $sql = "SELECT l.*, COALESCE(c.total, 0) AS total_downloads
                FROM {$log} l
                LEFT JOIN {$counts} c ON l.btn_id = c.btn_id
                {$where}
                ORDER BY {$orderby_sql} {$order}
                LIMIT %d OFFSET %d"; // phpcs:ignore

        $this->items = $wpdb->get_results( $wpdb->prepare( $sql, $per_page, $offset ) ); // phpcs:ignore

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
    }

    private function date_where_clause() {
        // downloaded_at is stored using current_time('mysql') (site local time),
        // so comparisons must also use local time, not gmdate/UTC.
        switch ( $this->date_filter ) {
            case 'today':
                return " WHERE DATE(l.downloaded_at) = '" . current_time( 'Y-m-d' ) . "'";
            case '7days':
                return " WHERE l.downloaded_at >= '" . date( 'Y-m-d H:i:s', strtotime( '-7 days', current_time( 'timestamp' ) ) ) . "'"; // phpcs:ignore WordPress.DateTime.RestrictedFunctions
            case '30days':
                return " WHERE l.downloaded_at >= '" . date( 'Y-m-d H:i:s', strtotime( '-30 days', current_time( 'timestamp' ) ) ) . "'"; // phpcs:ignore WordPress.DateTime.RestrictedFunctions
            default:
                return '';
        }
    }

    // ── Stat helpers used by the view ───────────────────────────────────────

    public static function get_stat_counts() {
        global $wpdb;
        $log   = $wpdb->prefix . QDBP_Analytics::TABLE;
        $today = current_time( 'Y-m-d' );
        $now   = current_time( 'timestamp' );

        // All stats read from the log so date filters are consistent.
        // Uses local time to match how downloaded_at is stored (current_time('mysql')).
        return array(
            'total' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log}" ), // phpcs:ignore
            'today' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log} WHERE DATE(downloaded_at) = '{$today}'" ), // phpcs:ignore
            'week'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log} WHERE downloaded_at >= '" . date( 'Y-m-d H:i:s', strtotime( '-7 days', $now ) ) . "'" ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions
            'month' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log} WHERE downloaded_at >= '" . date( 'Y-m-d H:i:s', strtotime( '-30 days', $now ) ) . "'" ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions
        );
    }

    // ── Top files (used by Overview page) ───────────────────────────────────

    /**
     * Return the top N buttons by total download count, with file metadata
     * resolved from the most recent log entry for each button.
     *
     * @param  int   $limit
     * @return array Array of stdClass objects: btn_id, total, attachment_id, external_url
     */
    public static function get_top_files( $limit = 5 ) {
        global $wpdb;
        $counts = $wpdb->prefix . QDBP_Analytics::COUNTS_TABLE;
        $log    = $wpdb->prefix . QDBP_Analytics::TABLE;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT c.btn_id, c.total,
                    l.attachment_id, l.external_url
             FROM {$counts} c
             LEFT JOIN {$log} l
                    ON l.id = (
                        SELECT id FROM {$log} l2
                        WHERE l2.btn_id = c.btn_id
                        ORDER BY id DESC LIMIT 1
                    )
             ORDER BY c.total DESC
             LIMIT %d",
            absint( $limit )
        ) );
    }

    // ── Bulk action handler (called from the view) ─────────────────────────

    public static function process_bulk_action() {
        if ( empty( $_POST['log_id'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( ! check_admin_referer( 'bulk-downloads' ) ) return;

        $action = isset( $_POST['action'] ) ? $_POST['action'] : ( isset( $_POST['action2'] ) ? $_POST['action2'] : '' );
        if ( 'delete' !== $action ) return;

        global $wpdb;
        $table = $wpdb->prefix . QDBP_Analytics::TABLE;
        $ids   = array_map( 'absint', (array) $_POST['log_id'] );

        foreach ( $ids as $id ) {
            $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
        }
    }
}
