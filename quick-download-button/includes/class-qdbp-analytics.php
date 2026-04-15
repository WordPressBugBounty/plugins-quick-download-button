<?php
defined( 'ABSPATH' ) || exit;

/**
 * Analytics module.
 *
 * Two tables:
 *   {prefix}qdb_download_log    — full per-download audit log (can be purged)
 *   {prefix}qdb_download_counts — permanent cumulative counter per btn_id (never purged)
 *
 * get_count() always reads from the counts table so totals survive log deletion.
 */
class QDBP_Analytics {

    const TABLE        = 'qdb_download_log';
    const COUNTS_TABLE = 'qdb_download_counts';

    public static function init() {
        add_action( 'qdb_before_download', array( __CLASS__, 'log_download' ) );
        add_action( 'qdbp_daily_cleanup',  array( __CLASS__, 'purge_old_logs' ) );
    }

    public static function schedule_cleanup() {
        if ( ! wp_next_scheduled( 'qdbp_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'qdbp_daily_cleanup' );
        }
    }

    public static function unschedule_cleanup() {
        $timestamp = wp_next_scheduled( 'qdbp_daily_cleanup' );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, 'qdbp_daily_cleanup' );
        }
    }

    public static function purge_old_logs() {
        $days = (int) QDBP_Settings::get( 'log_retention_days' );
        if ( $days <= 0 ) return;

        global $wpdb;
        $table  = $wpdb->prefix . self::TABLE;
        $cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        // Note: only the log is purged — qdb_download_counts is never deleted.
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE downloaded_at < %s", $cutoff ) ); // phpcs:ignore
    }

    /**
     * Log a download event and increment the permanent counter.
     *
     * @param array $context {
     *     @type string $btn_id
     *     @type int    $attachment_id
     *     @type string $external_url
     *     @type int    $user_id
     *     @type string $ip
     *     @type string $user_agent
     *     @type string $timestamp
     * }
     */
    public static function log_download( $context ) {
        global $wpdb;

        $btn_id = sanitize_text_field( $context['btn_id'] ?? '' );

        // ── Audit log (purgeable) ──────────────────────────────────────────
        $log_table = $wpdb->prefix . self::TABLE;
        // page_url: the page the visitor was on when they clicked the button.
        // HTTP_REFERER is set by the browser when the download redirect fires.
        $page_url = isset( $_SERVER['HTTP_REFERER'] )
            ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) )
            : '';

        $wpdb->insert(
            $log_table,
            array(
                'btn_id'        => $btn_id,
                'attachment_id' => (int) ( $context['attachment_id'] ?? 0 ),
                'external_url'  => esc_url_raw( $context['external_url'] ?? '' ),
                'user_id'       => (int) ( $context['user_id'] ?? 0 ),
                'ip'            => sanitize_text_field( $context['ip'] ?? '' ),
                'user_agent'    => sanitize_text_field( $context['user_agent'] ?? '' ),
                'page_url'      => $page_url,
                'downloaded_at' => $context['timestamp'] ?? current_time( 'mysql' ),
            ),
            array( '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s' )
        );

        // ── Permanent counter (never deleted) ─────────────────────────────
        if ( $btn_id ) {
            $counts_table = $wpdb->prefix . self::COUNTS_TABLE;
            $wpdb->query( // phpcs:ignore
                $wpdb->prepare(
                    "INSERT INTO {$counts_table} (btn_id, total) VALUES (%s, 1)
                     ON DUPLICATE KEY UPDATE total = total + 1",
                    $btn_id
                )
            );
        }
    }

    /**
     * Get cumulative download count for a button.
     * Reads from the permanent counts table — survives log deletion.
     *
     * @param  string $btn_id
     * @return int
     */
    public static function get_count( $btn_id ) {
        if ( ! $btn_id ) return 0;
        global $wpdb;
        $table = $wpdb->prefix . self::COUNTS_TABLE;
        return (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT total FROM {$table} WHERE btn_id = %s", $btn_id )
        );
    }

    /**
     * Backfill qdb_download_counts from the existing log.
     *
     * Safe to call multiple times — GREATEST() ensures we never reduce a count
     * below what the log shows while keeping any counts already accumulated.
     * Called after create_table() on DB version upgrades.
     */
    public static function backfill_counts() {
        global $wpdb;
        $log    = $wpdb->prefix . self::TABLE;
        $counts = $wpdb->prefix . self::COUNTS_TABLE;

        $wpdb->query( // phpcs:ignore
            "INSERT INTO {$counts} (btn_id, total)
             SELECT btn_id, COUNT(*) AS total
             FROM {$log}
             WHERE btn_id != ''
             GROUP BY btn_id
             ON DUPLICATE KEY UPDATE total = GREATEST(total, VALUES(total))"
        );
    }

    /**
     * Create both tables on plugin activation / version upgrade.
     *
     * NOTE: Do NOT use "CREATE TABLE IF NOT EXISTS" here — dbDelta() parses
     * the table name by grabbing the first token after "CREATE TABLE ", so
     * "IF" would be read as the table name and no ALTER TABLE would ever be
     * generated for schema upgrades.  dbDelta() is already idempotent.
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Audit log
        $log = $wpdb->prefix . self::TABLE;
        dbDelta( "CREATE TABLE {$log} (
            id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            btn_id        VARCHAR(32)         NOT NULL DEFAULT '',
            attachment_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            external_url  TEXT                NOT NULL,
            user_id       BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            ip            VARCHAR(45)         NOT NULL DEFAULT '',
            user_agent    VARCHAR(255)        NOT NULL DEFAULT '',
            page_url      VARCHAR(2048)       NOT NULL DEFAULT '',
            downloaded_at DATETIME            NOT NULL,
            PRIMARY KEY  (id),
            KEY btn_id (btn_id),
            KEY downloaded_at (downloaded_at)
        ) {$charset_collate};" );

        // Permanent counts
        $counts = $wpdb->prefix . self::COUNTS_TABLE;
        dbDelta( "CREATE TABLE {$counts} (
            btn_id VARCHAR(32)         NOT NULL,
            total  BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (btn_id)
        ) {$charset_collate};" );
    }
}
