<?php
defined( 'ABSPATH' ) || exit;

/**
 * Email Gate module.
 *
 * When a button has data-qdb-email-gate="1", the Pro JS intercepts the
 * qdb-before-download event, shows an inline email form, and submits
 * via AJAX. On success a session cookie is set and the download proceeds.
 *
 * Always-show mode (email_gate_always_show="1"):
 *   The gate is shown on every click regardless of whether the visitor has
 *   already submitted their email.  If the cookie is present the gate renders
 *   a "returning visitor" thank-you state instead of the email form, and the
 *   visitor just clicks one button to proceed — no re-entry of their email is
 *   required.  Both states are embedded in the HTML so cached pages work
 *   correctly; the Pro JS chooses which to display based on the cookie.
 *
 * Hooks used (free plugin):
 *   qdb_button_data_atts  — inject data-qdb-email-gate onto the button.
 *   qdb_shortcode_atts    — register the email_gate shortcode attribute.
 *   qdb_gate_html         — output the hidden email form inside the wrapper.
 *   qdb_can_download      — secondary server-side gate check (optional).
 *   qdbp_localize_data    — pass gate config to Pro frontend JS.
 *
 * Captured leads are stored in {prefix}qdb_leads.
 */
class QDBP_Email_Gate {

    const TABLE        = 'qdb_leads';
    const COOKIE_KEY   = 'qdbp_email_unlocked';
    const COOKIE_DAYS  = 30;

    public static function init() {
        // Register shortcode attribute
        add_filter( 'qdb_shortcode_atts', array( __CLASS__, 'register_atts' ), 10, 2 );

        // Inject data attribute onto button element
        add_filter( 'qdb_button_data_atts', array( __CLASS__, 'add_data_attr' ), 10, 2 );

        // Inject gate form HTML inside wrapper
        add_action( 'qdb_gate_html', array( __CLASS__, 'output_gate_html' ) );

        // Pass gate settings to frontend
        add_filter( 'qdbp_localize_data', array( __CLASS__, 'localize_data' ) );

        // AJAX handlers
        add_action( 'wp_ajax_qdbp_email_gate',        array( __CLASS__, 'handle_ajax' ) );
        add_action( 'wp_ajax_nopriv_qdbp_email_gate', array( __CLASS__, 'handle_ajax' ) );

        // Table creation is handled centrally by QDBP_Loader / plugin activation.
    }

    /** Add email_gate attribute support to shortcode. */
    public static function register_atts( $atts, $raw = array() ) {
        $atts['email_gate']             = isset( $raw['email_gate'] )             ? $raw['email_gate']             : '0';
        $atts['email_gate_label']       = isset( $raw['email_gate_label'] )       ? $raw['email_gate_label']       : __( 'Enter your email to download', 'quick-download-button' );
        $atts['email_gate_btn_txt']     = isset( $raw['email_gate_btn_txt'] )     ? $raw['email_gate_btn_txt']     : __( 'Get Download Link', 'quick-download-button' );
        $atts['email_gate_always_show'] = isset( $raw['email_gate_always_show'] ) ? $raw['email_gate_always_show'] : '0';
        $atts['email_gate_ty_msg']      = isset( $raw['email_gate_ty_msg'] )      ? $raw['email_gate_ty_msg']      : __( 'Welcome back! Click below to continue your download.', 'quick-download-button' );
        $atts['email_gate_ty_btn_txt']  = isset( $raw['email_gate_ty_btn_txt'] )  ? $raw['email_gate_ty_btn_txt']  : __( 'Continue Download', 'quick-download-button' );
        return $atts;
    }

    /** Inject data-qdb-email-gate="1" (and data-qdb-email-returning="1" when applicable). */
    public static function add_data_attr( $extra, $atts ) {
        if ( empty( $atts['email_gate'] ) || '1' !== $atts['email_gate'] ) return $extra;

        $always_show = ! empty( $atts['email_gate_always_show'] ) && '1' === (string) $atts['email_gate_always_show'];
        $unlocked    = self::visitor_unlocked();

        if ( $unlocked && ! $always_show ) {
            // Standard mode — gate already cleared, bypass entirely.
            return $extra;
        }

        $extra['data-qdb-email-gate'] = '1';

        if ( $unlocked && $always_show ) {
            // Always-show mode — visitor already submitted: flag as returning
            // so JS shows the thank-you state instead of the email form.
            $extra['data-qdb-email-returning'] = '1';
        }

        return $extra;
    }

    /**
     * Output the gate HTML (hidden by default, shown by Pro JS).
     *
     * In standard mode only the email form is rendered.
     * In always-show mode BOTH the email form AND the thank-you state are
     * embedded so that cached pages display correctly — the Pro JS checks
     * the cookie on load and reveals whichever state applies.
     */
    public static function output_gate_html( $atts ) {
        if ( empty( $atts['email_gate'] ) || '1' !== $atts['email_gate'] ) return;

        $always_show = ! empty( $atts['email_gate_always_show'] ) && '1' === (string) $atts['email_gate_always_show'];
        $unlocked    = self::visitor_unlocked();

        // Standard mode: skip entirely once the visitor has already submitted.
        if ( ! $always_show && $unlocked ) return;

        $label      = $atts['email_gate_label']      ?? __( 'Enter your email to download', 'quick-download-button' );
        $btn_txt    = $atts['email_gate_btn_txt']    ?? __( 'Get Download Link', 'quick-download-button' );
        $ty_msg     = $atts['email_gate_ty_msg']     ?? __( 'Welcome back! Click below to continue your download.', 'quick-download-button' );
        $ty_btn_txt = $atts['email_gate_ty_btn_txt'] ?? __( 'Continue Download', 'quick-download-button' );
        ?>
        <div class="qdbp-gate qdbp-email-gate"<?php echo $always_show ? ' data-qdb-always-show="1"' : ''; ?> hidden>

            <?php if ( $always_show ) : ?>
            <div class="qdbp-email-new-state"<?php echo $unlocked ? ' hidden' : ''; ?>>
            <?php endif; ?>

                <form class="qdbp-email-form">
                    <label class="qdbp-gate-label"><?php echo esc_html( $label ); ?></label>
                    <div class="qdbp-email-field-row">
                        <input type="email" class="qdbp-email-input"
                               placeholder="<?php esc_attr_e( 'you@example.com', 'quick-download-button' ); ?>"
                               required />
                        <button type="submit" class="qdbp-gate-submit"><?php echo esc_html( $btn_txt ); ?></button>
                    </div>
                    <div class="qdbp-gate-error" hidden></div>
                </form>

            <?php if ( $always_show ) : ?>
            </div><!-- /.qdbp-email-new-state -->

            <div class="qdbp-email-returning-state"<?php echo $unlocked ? '' : ' hidden'; ?>>
                <p class="qdbp-gate-label"><?php echo esc_html( $ty_msg ); ?></p>
                <button type="button" class="qdbp-gate-submit qdbp-email-ty-proceed">
                    <?php echo esc_html( $ty_btn_txt ); ?>
                </button>
            </div><!-- /.qdbp-email-returning-state -->
            <?php endif; ?>

        </div><!-- /.qdbp-email-gate -->
        <?php
    }

    /** Handle email submission via AJAX. */
    public static function handle_ajax() {
        check_ajax_referer( 'qdbp_nonce', 'security' );

        $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'quick-download-button' ) ) );
        }

        // Store lead
        self::store_lead( $email );

        // Notify admin if enabled
        if ( QDBP_Settings::get( 'admin_email_notify' ) ) {
            $to      = QDBP_Settings::get( 'notify_email' ) ?: get_option( 'admin_email' );
            $subject = sprintf( __( '[%s] New download lead: %s', 'quick-download-button' ), get_bloginfo( 'name' ), $email );
            $body    = sprintf(
                /* translators: 1: email, 2: source URL */
                __( "A new email lead was captured:\n\nEmail: %1\$s\nSource: %2\$s\n\nView leads: %3\$s", 'quick-download-button' ),
                $email,
                isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '—',
                admin_url( 'admin.php?page=qdbp-leads' )
            );
            wp_mail( sanitize_email( $to ), $subject, $body );
        }

        // Set unlock cookie
        self::set_unlock_cookie();

        wp_send_json_success( array( 'message' => __( 'Thank you! Your download is starting.', 'quick-download-button' ) ) );
    }

    /** Check if the visitor has already passed the gate. */
    public static function visitor_unlocked() {
        return ! empty( $_COOKIE[ self::COOKIE_KEY ] );
    }

    /** Set the unlock cookie. */
    private static function set_unlock_cookie() {
        $expiry = time() + ( self::COOKIE_DAYS * DAY_IN_SECONDS );
        setcookie( self::COOKIE_KEY, '1', $expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
    }

    /** Store a captured email lead, skipping if email already exists. */
    private static function store_lead( $email ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        // Deduplicate — one row per unique email address.
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s LIMIT 1", $email ) ); // phpcs:ignore
        if ( $exists ) return;

        $wpdb->insert(
            $table,
            array(
                'email'      => $email,
                'user_id'    => get_current_user_id(),
                'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
                'source_url' => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%d', '%s', '%s', '%s' )
        );
    }

    /** Pass gate active flag to Pro JS. */
    public static function localize_data( $data ) {
        $data['email_gate_ajax'] = 'qdbp_email_gate';
        return $data;
    }

    /** Create leads table on activation. */
    public static function create_table() {
        global $wpdb;
        $table           = $wpdb->prefix . self::TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email      VARCHAR(200)        NOT NULL,
            user_id    BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            ip         VARCHAR(45)         NOT NULL DEFAULT '',
            source_url TEXT                NOT NULL,
            created_at DATETIME            NOT NULL,
            PRIMARY KEY (id),
            KEY email (email),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
