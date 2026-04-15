<?php
defined( 'ABSPATH' ) || exit;

/**
 * Centralised settings helper for Quick Download Button.
 *
 * All options are stored as a single serialised array under the
 * option key 'qdbp_settings'. Call QDBP_Settings::get() anywhere.
 */
class QDBP_Settings {

    const OPTION_KEY = 'qdbp_settings';

    /** Default values for every setting. */
    private static $defaults = array(
        'show_download_count'  => 0,
        'download_count_label' => '{count} downloads',
        'admin_email_notify'   => 0,
        'notify_email'         => '',          // blank = use admin_email
        'log_retention_days'   => 0,           // 0 = keep forever
    );

    /**
     * Return the full settings array merged with defaults.
     *
     * @return array
     */
    public static function all() {
        $saved = get_option( self::OPTION_KEY, array() );
        return wp_parse_args( is_array( $saved ) ? $saved : array(), self::$defaults );
    }

    /**
     * Return a single setting value.
     *
     * @param string $key
     * @param mixed  $fallback  Optional override for the built-in default.
     * @return mixed
     */
    public static function get( $key, $fallback = null ) {
        $all = self::all();
        if ( array_key_exists( $key, $all ) ) {
            return $all[ $key ];
        }
        return $fallback;
    }

    /**
     * Save settings (sanitised).
     *
     * @param array $raw  Raw POST values.
     */
    public static function save( $raw ) {
        $clean = array(
            'show_download_count'  => ! empty( $raw['show_download_count'] ) ? 1 : 0,
            'download_count_label' => sanitize_text_field( $raw['download_count_label'] ?? '{count} downloads' ),
            'admin_email_notify'   => ! empty( $raw['admin_email_notify'] ) ? 1 : 0,
            'notify_email'         => sanitize_email( $raw['notify_email'] ?? '' ),
            'log_retention_days'   => max( 0, (int) ( $raw['log_retention_days'] ?? 0 ) ),
        );
        update_option( self::OPTION_KEY, $clean );
    }
}
