<?php
defined( 'ABSPATH' ) || exit;

$stats     = QDBP_Analytics_Table::get_stat_counts();
$top_files = QDBP_Analytics_Table::get_top_files( 5 );
$max_count = ! empty( $top_files ) ? (int) $top_files[0]->total : 1;
?>
<div class="wrap qdbp-wrap">

    <div class="qdbp-page-header">
        <h1><?php esc_html_e( 'Download Analytics', 'quick-download-button' ); ?></h1>
    </div>

    <?php QDBP_Admin::analytics_nav( 'overview' ); ?>

    <!-- ── Stat cards ──────────────────────────────────────────────────── -->
    <div class="qdbp-stat-grid">

        <div class="qdbp-stat-card qdbp-stat-card--indigo">
            <div class="qdbp-stat-card__inner">
                <div class="qdbp-stat-card__body">
                    <span class="qdbp-stat-card__value"><?php echo number_format_i18n( $stats['total'] ); ?></span>
                    <span class="qdbp-stat-card__label"><?php esc_html_e( 'All Time', 'quick-download-button' ); ?></span>
                </div>
                <div class="qdbp-stat-card__icon-wrap qdbp-stat-card__icon-wrap--indigo">
                    <span class="dashicons dashicons-download"></span>
                </div>
            </div>
        </div>

        <div class="qdbp-stat-card qdbp-stat-card--amber">
            <div class="qdbp-stat-card__inner">
                <div class="qdbp-stat-card__body">
                    <span class="qdbp-stat-card__value"><?php echo number_format_i18n( $stats['today'] ); ?></span>
                    <span class="qdbp-stat-card__label"><?php esc_html_e( 'Today', 'quick-download-button' ); ?></span>
                    <span class="qdbp-stat-card__sub"><?php echo esc_html( current_time( 'F j, Y' ) ); ?></span>
                </div>
                <div class="qdbp-stat-card__icon-wrap qdbp-stat-card__icon-wrap--amber">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
            </div>
        </div>

        <div class="qdbp-stat-card qdbp-stat-card--emerald">
            <div class="qdbp-stat-card__inner">
                <div class="qdbp-stat-card__body">
                    <span class="qdbp-stat-card__value"><?php echo number_format_i18n( $stats['week'] ); ?></span>
                    <span class="qdbp-stat-card__label"><?php esc_html_e( 'Last 7 Days', 'quick-download-button' ); ?></span>
                </div>
                <div class="qdbp-stat-card__icon-wrap qdbp-stat-card__icon-wrap--emerald">
                    <span class="dashicons dashicons-chart-line"></span>
                </div>
            </div>
        </div>

        <div class="qdbp-stat-card qdbp-stat-card--blue">
            <div class="qdbp-stat-card__inner">
                <div class="qdbp-stat-card__body">
                    <span class="qdbp-stat-card__value"><?php echo number_format_i18n( $stats['month'] ); ?></span>
                    <span class="qdbp-stat-card__label"><?php esc_html_e( 'Last 30 Days', 'quick-download-button' ); ?></span>
                </div>
                <div class="qdbp-stat-card__icon-wrap qdbp-stat-card__icon-wrap--blue">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Top downloaded files ────────────────────────────────────────── -->
    <?php if ( ! empty( $top_files ) ) : ?>
    <div class="qdbp-panel">
        <div class="qdbp-panel__header">
            <span class="dashicons dashicons-awards qdbp-panel__header-icon"></span>
            <h2 class="qdbp-panel__title"><?php esc_html_e( 'Top Downloaded Files', 'quick-download-button' ); ?></h2>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=qdbp-log' ) ); ?>" class="qdbp-panel__link">
                <?php esc_html_e( 'View full log', 'quick-download-button' ); ?> &rarr;
            </a>
        </div>
        <div class="qdbp-panel__body">
            <?php foreach ( $top_files as $rank => $file ) :
                // Resolve file label
                $label = '';
                $href  = '';
                if ( $file->attachment_id ) {
                    $title = get_the_title( $file->attachment_id );
                    $url   = wp_get_attachment_url( $file->attachment_id );
                    $label = $title ?: ( $url ? basename( $url ) : '' );
                    $href  = $url ?: '';
                } elseif ( $file->external_url ) {
                    $label = $file->external_url;
                    $href  = $file->external_url;
                }
                if ( ! $label ) $label = 'btn:' . $file->btn_id;

                $pct = $max_count > 0 ? round( ( $file->total / $max_count ) * 100 ) : 0;
            ?>
            <div class="qdbp-top-file">
                <span class="qdbp-top-file__rank qdbp-top-file__rank--<?php echo $rank + 1; ?>"><?php echo $rank + 1; ?></span>
                <div class="qdbp-top-file__info">
                    <?php if ( $href ) : ?>
                        <a href="<?php echo esc_url( $href ); ?>" class="qdbp-top-file__name" target="_blank" title="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></a>
                    <?php else : ?>
                        <span class="qdbp-top-file__name" title="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></span>
                    <?php endif; ?>
                    <div class="qdbp-top-file__bar">
                        <div class="qdbp-top-file__fill" style="width:<?php echo absint( $pct ); ?>%"></div>
                    </div>
                </div>
                <span class="qdbp-top-file__count">
                    <?php echo number_format_i18n( $file->total ); ?>
                    <span class="qdbp-top-file__count-label"><?php esc_html_e( 'downloads', 'quick-download-button' ); ?></span>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else : ?>
    <div class="qdbp-empty-state">
        <span class="dashicons dashicons-download qdbp-empty-state__icon"></span>
        <p><?php esc_html_e( 'No downloads recorded yet. Download data will appear here once visitors start downloading files.', 'quick-download-button' ); ?></p>
    </div>
    <?php endif; ?>

</div>
