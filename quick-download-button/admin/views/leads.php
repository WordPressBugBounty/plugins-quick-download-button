<?php
defined( 'ABSPATH' ) || exit;

QDBP_Leads_Table::process_bulk_action();

$table    = new QDBP_Leads_Table();
$table->prepare_items();
$page_url = admin_url( 'admin.php?page=qdbp-leads' );

$export_url = wp_nonce_url(
    add_query_arg( 'qdbp_export', 'leads', $page_url ),
    'qdbp_export_leads'
);
?>
<div class="wrap qdbp-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Email Leads', 'quick-download-button' ); ?></h1>
    <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">
        <?php esc_html_e( 'Export CSV', 'quick-download-button' ); ?>
    </a>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="qdbp-leads" />
        <?php $table->search_box( __( 'Search emails', 'quick-download-button' ), 'lead-search' ); ?>
    </form>

    <form method="post">
        <?php wp_nonce_field( 'bulk-leads' ); ?>
        <?php $table->display(); ?>
    </form>
</div>
