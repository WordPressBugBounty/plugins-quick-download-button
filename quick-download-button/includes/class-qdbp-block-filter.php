<?php
defined( 'ABSPATH' ) || exit;

/**
 * Gutenberg block filter.
 *
 * Post-processes the static-save block HTML on the frontend to inject Pro features.
 *
 * HTML placement rules:
 *   <quick-download-button-info>  ← informational content only (count badge etc.)
 *   </quick-download-button-info>
 *   <!-- gate forms go HERE, after the info tag -->
 *
 * Block attributes (emailGate, passcode, dlLimit, showCount …) are
 * registered client-side via blocks.registerBlockType filter in pro-editor.js
 * and stored in the block comment so $block['attrs'] contains them here.
 */
class QDBP_Block_Filter {

    const BLOCK = 'quick-download-button/download-button';

    public static function init() {
        add_filter( 'render_block',         array( __CLASS__, 'inject_gates'     ), 10, 2 );
        add_filter( 'qdb_shortcode_output', array( __CLASS__, 'add_pro_class'    ), 10, 1 );
    }

    /**
     * Add qdbp-pro class to the button wrapper for shortcode output.
     * Enables Pro-specific CSS targeting without JS.
     */
    public static function add_pro_class( $output ) {
        return preg_replace( '/class="qdbn"/', 'class="qdbn qdbp-pro"', $output, 1 );
    }

    public static function inject_gates( $content, $block ) {
        if ( $block['blockName'] !== self::BLOCK ) return $content;

        // Always add qdbp-pro class so Pro CSS can target the wrapper.
        $content = preg_replace( '/class="qdbn"/', 'class="qdbn qdbp-pro"', $content, 1 );

        $attrs  = $block['attrs'] ?? array();
        $btn_id = $attrs['btnId'] ?? '';

        // Fallback for blocks saved before btnId was introduced.
        if ( ! $btn_id ) {
            $btn_id = substr( md5( serialize( $attrs ) ), 0, 12 );
        }

        $data_attrs = ''; // added to <button>
        $gate_html  = ''; // injected AFTER <quick-download-button-info>
        $info_html  = ''; // injected INSIDE <quick-download-button-info>

        // ── Email Gate ─────────────────────────────────────────────────────
        if ( ! empty( $attrs['emailGate'] ) && ! QDBP_Email_Gate::visitor_unlocked() ) {
            $data_attrs .= ' data-qdb-email-gate="1"';
            $label       = esc_html( $attrs['emailGateLabel']  ?? __( 'Enter your email to download', 'quick-download-button' ) );
            $btn_txt     = esc_html( $attrs['emailGateBtnTxt'] ?? __( 'Get Download Link', 'quick-download-button' ) );
            $gate_html  .= self::email_gate_html( $label, $btn_txt );
        }

        // ── Passcode Gate ──────────────────────────────────────────────────
        if ( ! empty( $attrs['passcode'] ) && ! QDBP_Passcode::is_unlocked( $btn_id ) ) {
            $data_attrs .= ' data-qdb-passcode="1"';
            $label       = esc_html( $attrs['passcodeLabel'] ?? __( 'Enter passcode to download', 'quick-download-button' ) );
            $gate_html  .= self::passcode_gate_html( $label );

            // Store passcode server-side for AJAX verification.
            if ( $btn_id ) {
                set_transient( 'qdbp_pc_' . $btn_id, array(
                    'code'  => $attrs['passcode'],
                    'error' => '',   // blocks have no per-button error attr; use default
                ), DAY_IN_SECONDS );
            }
        }

        // ── Download Limit ─────────────────────────────────────────────────
        if ( ! empty( $attrs['dlLimit'] ) && (int) $attrs['dlLimit'] > 0 ) {
            $ip      = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
            $user_id = get_current_user_id();
            $limit   = (int) $attrs['dlLimit'];
            $window  = (int) ( $attrs['dlLimitWindow'] ?? 24 );
            $msg     = $attrs['dlLimitMsg'] ?? '';

            // Store settings so check_limit() can enforce server-side at download time.
            if ( $btn_id ) {
                QDBP_Download_Limit::store_settings( $btn_id, $limit, $window, $msg );
            }

            $count   = QDBP_Download_Limit::get_count_for_ip( $ip, $user_id, $window, $btn_id );

            if ( $count >= $limit ) {
                $data_attrs .= ' data-qdb-limit-reached="1"';
                $data_attrs .= ' data-qdb-limit-msg="' . esc_attr( $msg ?: __( 'You have reached the download limit. Please try again later.', 'quick-download-button' ) ) . '"';
            }
        }

        // ── Expiring Links ─────────────────────────────────────────────────
        $exp_hours  = (int) ( $attrs['expiryHours']  ?? 0 );
        $exp_clicks = (int) ( $attrs['expiryClicks'] ?? 0 );

        if ( ( $exp_hours > 0 || $exp_clicks > 0 ) && $btn_id ) {
            $data_attrs .= ' data-qdb-expiry="1"';
            QDBP_Expiring_Links::store_settings(
                $btn_id,
                $exp_hours,
                $exp_clicks,
                $attrs['expiryMsg'] ?? __( 'This download link has expired.', 'quick-download-button' )
            );
        }

        // ── Download Counter ───────────────────────────────────────────────
        // showCount attr overrides global setting; if absent fall back to global.
        $show_count = isset( $attrs['showCount'] )
            ? (bool) $attrs['showCount']
            : (bool) QDBP_Settings::get( 'show_download_count' );

        if ( $show_count && $btn_id ) {
            $dl_count  = QDBP_Analytics::get_count( $btn_id );
            $info_html .= QDBP_Download_Counter::build_html( $dl_count );
        }

        // ── Ensure data-qdb-btn-id is on the button (fixes old static-save blocks) ──
        if ( $btn_id && strpos( $content, 'data-qdb-btn-id' ) === false ) {
            $safe_btn_id = esc_attr( $btn_id );
            $content     = preg_replace_callback(
                '/(<button\b[^>]+\bclass="[^"]*\bg-btn\b[^"]*\bf-l\b[^"]*"[^>]*)>/i',
                function ( $m ) use ( $safe_btn_id ) {
                    return $m[1] . ' data-qdb-btn-id="' . $safe_btn_id . '">';
                },
                $content,
                1
            );
        }

        // ── Inject Pro data attrs into button ──────────────────────────────
        // Uses preg_replace_callback so user-provided text (limit msg, expiry msg)
        // containing '$1' or '\1' is never misinterpreted as a back-reference.
        if ( $data_attrs ) {
            $content = preg_replace_callback(
                '/(<button\b[^>]+\bclass="[^"]*\bg-btn\b[^"]*\bf-l\b[^"]*"[^>]*)>/i',
                function ( $m ) use ( $data_attrs ) {
                    return $m[1] . $data_attrs . '>';
                },
                $content,
                1
            );
        }

        // ── Inject info INSIDE <quick-download-button-info> ────────────────
        if ( $info_html ) {
            // Add has-info to the class attribute.
            $content = preg_replace(
                '/(<quick-download-button-info[^>]*\bclass=")([^"]*)"/',
                '$1$2 has-info"',
                $content,
                1
            );
            // Inject badge content inside the element.
            $content = preg_replace_callback(
                '/(<quick-download-button-info[^>]*>)(.*?)(<\/quick-download-button-info>)/s',
                function ( $m ) use ( $info_html ) {
                    return $m[1] . $m[2] . $info_html . $m[3];
                },
                $content,
                1
            );
        }

        // ── Inject gate forms AFTER <quick-download-button-info> ──────────
        if ( $gate_html ) {
            $content = preg_replace_callback(
                '/(<\/quick-download-button-info>)/s',
                function ( $m ) use ( $gate_html ) {
                    return $m[1] . $gate_html;
                },
                $content,
                1
            );
        }

        return $content;
    }

    // ── Form HTML builders ─────────────────────────────────────────────────

    private static function email_gate_html( $label, $btn_txt ) {
        return '<div class="qdbp-gate qdbp-email-gate" hidden>'
            . '<form class="qdbp-email-form">'
            . '<label class="qdbp-gate-label">' . $label . '</label>'
            . '<div class="qdbp-email-field-row">'
            . '<input type="email" class="qdbp-email-input" placeholder="' . esc_attr__( 'you@example.com', 'quick-download-button' ) . '" required />'
            . '<button type="submit" class="qdbp-gate-submit">' . $btn_txt . '</button>'
            . '</div>'
            . '<div class="qdbp-gate-error" hidden></div>'
            . '</form>'
            . '</div>';
    }

    private static function passcode_gate_html( $label ) {
        return '<div class="qdbp-gate qdbp-passcode-gate" hidden>'
            . '<form class="qdbp-passcode-form">'
            . '<label class="qdbp-gate-label">' . $label . '</label>'
            . '<div class="qdbp-passcode-field-row">'
            . '<input type="password" class="qdbp-passcode-input" placeholder="••••••" required />'
            . '<button type="submit" class="qdbp-gate-submit">' . esc_html__( 'Unlock', 'quick-download-button' ) . '</button>'
            . '</div>'
            . '<div class="qdbp-gate-error" hidden></div>'
            . '</form>'
            . '</div>';
    }
}
