<?php
defined( 'ABSPATH' ) || exit;

// Handle save
if ( isset( $_POST['qdbp_save_settings'] ) && current_user_can( 'manage_options' ) && check_admin_referer( 'qdbp_settings_save' ) ) {
    QDBP_Settings::save( $_POST );
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'quick-download-button' ) . '</p></div>';
}

$s = QDBP_Settings::all();
?>
<div class="wrap qdbp-wrap">
    <h1><?php esc_html_e( 'Quick Download Button Settings', 'quick-download-button' ); ?></h1>

    <form method="post">
        <?php wp_nonce_field( 'qdbp_settings_save' ); ?>

        <!-- ── Download Counter ──────────────────────────────────── -->
        <h2><?php esc_html_e( 'Download Counter', 'quick-download-button' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Show download count', 'quick-download-button' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="show_download_count" value="1" <?php checked( 1, $s['show_download_count'] ); ?> />
                        <?php esc_html_e( 'Display total download count below each button', 'quick-download-button' ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="download_count_label"><?php esc_html_e( 'Count label', 'quick-download-button' ); ?></label></th>
                <td>
                    <input type="text" id="download_count_label" name="download_count_label" class="regular-text"
                           value="<?php echo esc_attr( $s['download_count_label'] ); ?>" />
                    <p class="description"><?php esc_html_e( 'Use {count} as a placeholder. Example: {count} downloads', 'quick-download-button' ); ?></p>
                </td>
            </tr>
        </table>

        <!-- ── Email Leads ──────────────────────────────────────── -->
        <h2><?php esc_html_e( 'Email Leads', 'quick-download-button' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Admin notification', 'quick-download-button' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="admin_email_notify" value="1" <?php checked( 1, $s['admin_email_notify'] ); ?> />
                        <?php esc_html_e( 'Send an email when a new lead is captured', 'quick-download-button' ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="notify_email"><?php esc_html_e( 'Notification email', 'quick-download-button' ); ?></label></th>
                <td>
                    <input type="email" id="notify_email" name="notify_email" class="regular-text"
                           value="<?php echo esc_attr( $s['notify_email'] ); ?>"
                           placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
                    <p class="description"><?php esc_html_e( 'Leave blank to use the WordPress admin email.', 'quick-download-button' ); ?></p>
                </td>
            </tr>
        </table>

        <!-- ── Data Retention ───────────────────────────────────── -->
        <h2><?php esc_html_e( 'Data Retention', 'quick-download-button' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="log_retention_days"><?php esc_html_e( 'Auto-delete logs after', 'quick-download-button' ); ?></label></th>
                <td>
                    <input type="number" id="log_retention_days" name="log_retention_days" min="0" step="1" class="small-text"
                           value="<?php echo absint( $s['log_retention_days'] ); ?>" />
                    <span><?php esc_html_e( 'days (0 = keep forever)', 'quick-download-button' ); ?></span>
                    <p class="description"><?php esc_html_e( 'Applies to download logs. Leads are never auto-deleted.', 'quick-download-button' ); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button( __( 'Save Settings', 'quick-download-button' ), 'primary', 'qdbp_save_settings' ); ?>
    </form>
</div>
