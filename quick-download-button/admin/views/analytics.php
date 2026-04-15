<?php
defined( 'ABSPATH' ) || exit;

QDBP_Analytics_Table::process_bulk_action();

$table       = new QDBP_Analytics_Table();
$table->prepare_items();
$date_filter = isset( $_GET['date_range'] ) ? sanitize_key( $_GET['date_range'] ) : 'all';
$page_url    = admin_url( 'admin.php?page=qdbp-log' );
?>
<div class="wrap qdbp-wrap">

    <div class="qdbp-page-header">
        <h1><?php esc_html_e( 'Download Log', 'quick-download-button' ); ?></h1>
    </div>

    <?php QDBP_Admin::analytics_nav( 'log' ); ?>

    <!-- Date filter tabs -->
    <div class="qdbp-date-tabs">
        <?php
        $ranges = array(
            'all'    => __( 'All Time', 'quick-download-button' ),
            'today'  => __( 'Today', 'quick-download-button' ),
            '7days'  => __( 'Last 7 Days', 'quick-download-button' ),
            '30days' => __( 'Last 30 Days', 'quick-download-button' ),
        );
        foreach ( $ranges as $key => $label ) {
            $active = ( $date_filter === $key ) ? ' class="current"' : '';
            $url    = esc_url( add_query_arg( 'date_range', $key, $page_url ) );
            echo '<a href="' . $url . '"' . $active . '>' . esc_html( $label ) . '</a>';
        }
        ?>
    </div>

    <form method="post">
        <?php wp_nonce_field( 'bulk-downloads' ); ?>
        <?php $table->display(); ?>
    </form>

</div>
