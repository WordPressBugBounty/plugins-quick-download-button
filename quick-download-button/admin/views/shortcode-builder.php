<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap qdbp-wrap">
    <div class="qdbp-page-header">
        <h1><?php esc_html_e( 'Shortcode Builder', 'quick-download-button' ); ?></h1>
        <p class="qdbp-page-subtitle"><?php esc_html_e( 'Configure your download button and copy the shortcode.', 'quick-download-button' ); ?></p>
    </div>

    <div class="qdbp-builder-layout">

        <!-- ── Left: form ───────────────────────────────────────── -->
        <div class="qdbp-builder-form">

            <!-- File -->
            <div class="qdbp-builder-section">
                <h2 class="qdbp-builder-section__title"><?php esc_html_e( 'File', 'quick-download-button' ); ?></h2>

                <div class="qdbp-builder-field">
                    <label for="qdbp-b-url"><?php esc_html_e( 'File URL', 'quick-download-button' ); ?></label>
                    <div class="qdbp-builder-field-row">
                        <input type="url" id="qdbp-b-url" class="qdbp-b-input regular-text"
                               data-sc-attr="url" data-sc-default=""
                               placeholder="https://example.com/file.zip" />
                        <button type="button" class="button qdbp-media-pick" data-target="qdbp-b-url">
                            <?php esc_html_e( 'Choose file', 'quick-download-button' ); ?>
                        </button>
                    </div>
                </div>

                <div class="qdbp-builder-field">
                    <label for="qdbp-b-title"><?php esc_html_e( 'Button label', 'quick-download-button' ); ?></label>
                    <input type="text" id="qdbp-b-title" class="qdbp-b-input regular-text"
                           data-sc-attr="title" data-sc-default="Download"
                           value="Download" />
                </div>
            </div>

            <!-- Button Style -->
            <div class="qdbp-builder-section">
                <h2 class="qdbp-builder-section__title"><?php esc_html_e( 'Button Style', 'quick-download-button' ); ?></h2>

                <div class="qdbp-builder-field">
                    <label><?php esc_html_e( 'Style', 'quick-download-button' ); ?></label>
                    <div class="qdbp-style-picker">
                        <?php
                        $styles = array(
                            'large' => __( 'Large', 'quick-download-button' ),
                            'mid'   => __( 'Mid', 'quick-download-button' ),
                            'small' => __( 'Small', 'quick-download-button' ),
                            'basic' => __( 'Basic', 'quick-download-button' ),
                            'pill'  => __( 'Pill', 'quick-download-button' ),
                            'card'  => __( 'Card', 'quick-download-button' ),
                            'ghost' => __( 'Ghost', 'quick-download-button' ),
                        );
                        foreach ( $styles as $val => $label ) :
                            $checked = 'large' === $val ? ' checked' : '';
                        ?>
                        <label class="qdbp-style-option">
                            <input type="radio" name="qdbp-b-button_type" value="<?php echo esc_attr( $val ); ?>"
                                   class="qdbp-b-radio" data-sc-attr="button_type" data-sc-default="large"<?php echo $checked; ?> />
                            <span><?php echo esc_html( $label ); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="qdbp-builder-field">
                    <label><?php esc_html_e( 'Alignment', 'quick-download-button' ); ?></label>
                    <select id="qdbp-b-align" class="qdbp-b-input" data-sc-attr="align" data-sc-default="">
                        <option value=""><?php esc_html_e( '— Default —', 'quick-download-button' ); ?></option>
                        <option value="left"><?php esc_html_e( 'Left', 'quick-download-button' ); ?></option>
                        <option value="center"><?php esc_html_e( 'Center', 'quick-download-button' ); ?></option>
                        <option value="right"><?php esc_html_e( 'Right', 'quick-download-button' ); ?></option>
                    </select>
                </div>

                <div class="qdbp-builder-field">
                    <label for="qdbp-b-wait"><?php esc_html_e( 'Countdown (seconds)', 'quick-download-button' ); ?></label>
                    <input type="number" id="qdbp-b-wait" class="qdbp-b-input small-text"
                           data-sc-attr="wait" data-sc-default="0"
                           value="0" min="0" max="60" />
                    <p class="description"><?php esc_html_e( '0 = no countdown', 'quick-download-button' ); ?></p>
                </div>

                <div class="qdbp-builder-field">
                    <label>
                        <input type="checkbox" id="qdbp-b-open_new_window" class="qdbp-b-checkbox"
                               data-sc-attr="open_new_window" data-sc-default="false"
                               data-sc-on="true" data-sc-off="false" />
                        <?php esc_html_e( 'Open in new window', 'quick-download-button' ); ?>
                    </label>
                </div>
            </div>

            <!-- Display / Counter -->
            <div class="qdbp-builder-section">
                <h2 class="qdbp-builder-section__title"><?php esc_html_e( 'Display', 'quick-download-button' ); ?></h2>

                <div class="qdbp-builder-field">
                    <label>
                        <input type="checkbox" id="qdbp-b-show_count" class="qdbp-b-checkbox"
                               data-sc-attr="show_count" data-sc-default="0"
                               data-sc-on="1" data-sc-off="0" />
                        <?php esc_html_e( 'Show download count', 'quick-download-button' ); ?>
                    </label>
                </div>
            </div>

            <!-- Gates -->
            <div class="qdbp-builder-section">
                <h2 class="qdbp-builder-section__title">&#x26A1; <?php esc_html_e( 'Gates', 'quick-download-button' ); ?></h2>

                <!-- Email gate -->
                <div class="qdbp-builder-field">
                    <label>
                        <input type="checkbox" id="qdbp-b-email_gate" class="qdbp-b-checkbox qdbp-b-toggle"
                               data-sc-attr="email_gate" data-sc-default="0"
                               data-sc-on="1" data-sc-off="0"
                               data-toggle-target=".qdbp-email-gate-fields" />
                        <?php esc_html_e( 'Require email before download', 'quick-download-button' ); ?>
                    </label>
                </div>

                <div class="qdbp-email-gate-fields qdbp-conditional-fields" hidden>
                    <div class="qdbp-builder-field qdbp-builder-field--sub">
                        <label for="qdbp-b-email_gate_label"><?php esc_html_e( 'Gate label', 'quick-download-button' ); ?></label>
                        <input type="text" id="qdbp-b-email_gate_label" class="qdbp-b-input regular-text"
                               data-sc-attr="email_gate_label" data-sc-default="Enter your email to download"
                               value="Enter your email to download" />
                    </div>
                    <div class="qdbp-builder-field qdbp-builder-field--sub">
                        <label for="qdbp-b-email_gate_btn_txt"><?php esc_html_e( 'Submit button text', 'quick-download-button' ); ?></label>
                        <input type="text" id="qdbp-b-email_gate_btn_txt" class="qdbp-b-input regular-text"
                               data-sc-attr="email_gate_btn_txt" data-sc-default="Get Download Link"
                               value="Get Download Link" />
                    </div>
                    <div class="qdbp-builder-field qdbp-builder-field--sub" style="margin-top:10px">
                        <label>
                            <input type="checkbox" id="qdbp-b-email_gate_always_show" class="qdbp-b-checkbox qdbp-b-toggle"
                                   data-sc-attr="email_gate_always_show" data-sc-default="0"
                                   data-sc-on="1" data-sc-off="0"
                                   data-toggle-target=".qdbp-email-gate-ty-fields" />
                            <?php esc_html_e( 'Always show gate (even for returning visitors)', 'quick-download-button' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'When enabled, the gate appears on every click. Returning visitors see a thank-you message instead of the email form.', 'quick-download-button' ); ?></p>
                    </div>
                    <div class="qdbp-email-gate-ty-fields qdbp-conditional-fields" hidden>
                        <div class="qdbp-builder-field qdbp-builder-field--sub">
                            <label for="qdbp-b-email_gate_ty_msg"><?php esc_html_e( 'Returning visitor message', 'quick-download-button' ); ?></label>
                            <input type="text" id="qdbp-b-email_gate_ty_msg" class="qdbp-b-input regular-text"
                                   data-sc-attr="email_gate_ty_msg" data-sc-default="Welcome back! Click below to continue your download."
                                   value="Welcome back! Click below to continue your download." />
                        </div>
                        <div class="qdbp-builder-field qdbp-builder-field--sub">
                            <label for="qdbp-b-email_gate_ty_btn_txt"><?php esc_html_e( 'Continue button text', 'quick-download-button' ); ?></label>
                            <input type="text" id="qdbp-b-email_gate_ty_btn_txt" class="qdbp-b-input regular-text"
                                   data-sc-attr="email_gate_ty_btn_txt" data-sc-default="Continue Download"
                                   value="Continue Download" />
                        </div>
                    </div>
                </div>

                <div class="qdbp-builder-field" style="margin-top:12px">
                    <label for="qdbp-b-passcode"><?php esc_html_e( 'Passcode', 'quick-download-button' ); ?></label>
                    <div class="qdbp-builder-field-row">
                        <input type="text" id="qdbp-b-passcode" class="qdbp-b-input regular-text qdbp-b-toggle"
                               data-sc-attr="passcode" data-sc-default=""
                               data-toggle-target=".qdbp-passcode-fields"
                               placeholder="<?php esc_attr_e( 'Leave blank to disable', 'quick-download-button' ); ?>" />
                    </div>
                </div>

                <div class="qdbp-passcode-fields qdbp-conditional-fields" hidden>
                    <div class="qdbp-builder-field qdbp-builder-field--sub">
                        <label for="qdbp-b-passcode_label"><?php esc_html_e( 'Passcode label', 'quick-download-button' ); ?></label>
                        <input type="text" id="qdbp-b-passcode_label" class="qdbp-b-input regular-text"
                               data-sc-attr="passcode_label" data-sc-default="Enter passcode to download"
                               value="Enter passcode to download" />
                    </div>
                </div>
            </div>

            <!-- Download Limit -->
            <div class="qdbp-builder-section">
                <h2 class="qdbp-builder-section__title">&#x26A1; <?php esc_html_e( 'Download Limit', 'quick-download-button' ); ?></h2>

                <div class="qdbp-builder-field">
                    <label for="qdbp-b-dl_limit"><?php esc_html_e( 'Max downloads per visitor', 'quick-download-button' ); ?></label>
                    <input type="number" id="qdbp-b-dl_limit" class="qdbp-b-input small-text qdbp-b-toggle"
                           data-sc-attr="dl_limit" data-sc-default="0"
                           data-toggle-target=".qdbp-limit-fields"
                           value="0" min="0" />
                    <p class="description"><?php esc_html_e( '0 = unlimited', 'quick-download-button' ); ?></p>
                </div>

                <div class="qdbp-limit-fields qdbp-conditional-fields" hidden>
                    <div class="qdbp-builder-field qdbp-builder-field--sub">
                        <label for="qdbp-b-dl_limit_window"><?php esc_html_e( 'Time window (hours)', 'quick-download-button' ); ?></label>
                        <input type="number" id="qdbp-b-dl_limit_window" class="qdbp-b-input small-text"
                               data-sc-attr="dl_limit_window" data-sc-default="24"
                               value="24" min="1" />
                    </div>
                    <div class="qdbp-builder-field qdbp-builder-field--sub">
                        <label for="qdbp-b-dl_limit_msg"><?php esc_html_e( 'Limit message', 'quick-download-button' ); ?></label>
                        <input type="text" id="qdbp-b-dl_limit_msg" class="qdbp-b-input regular-text"
                               data-sc-attr="dl_limit_msg" data-sc-default="You have reached the download limit. Please try again later."
                               value="You have reached the download limit. Please try again later." />
                    </div>
                </div>
            </div>

            <!-- Expiring Links -->
            <div class="qdbp-builder-section">
                <h2 class="qdbp-builder-section__title">&#x26A1; <?php esc_html_e( 'Expiring Links', 'quick-download-button' ); ?></h2>

                <div class="qdbp-builder-field">
                    <label for="qdbp-b-expiry_hours"><?php esc_html_e( 'Link expires after (hours)', 'quick-download-button' ); ?></label>
                    <input type="number" id="qdbp-b-expiry_hours" class="qdbp-b-input small-text qdbp-b-toggle"
                           data-sc-attr="expiry_hours" data-sc-default="0"
                           data-toggle-target=".qdbp-expiry-msg-field"
                           value="0" min="0" />
                    <p class="description"><?php esc_html_e( '0 = no time limit', 'quick-download-button' ); ?></p>
                </div>

                <div class="qdbp-builder-field">
                    <label for="qdbp-b-expiry_clicks"><?php esc_html_e( 'Max clicks per link', 'quick-download-button' ); ?></label>
                    <input type="number" id="qdbp-b-expiry_clicks" class="qdbp-b-input small-text qdbp-b-toggle"
                           data-sc-attr="expiry_clicks" data-sc-default="0"
                           data-toggle-target=".qdbp-expiry-msg-field"
                           value="0" min="0" />
                    <p class="description"><?php esc_html_e( '0 = unlimited', 'quick-download-button' ); ?></p>
                </div>

                <div class="qdbp-expiry-msg-field qdbp-conditional-fields" hidden>
                    <div class="qdbp-builder-field qdbp-builder-field--sub">
                        <label for="qdbp-b-expiry_msg"><?php esc_html_e( 'Expiry message', 'quick-download-button' ); ?></label>
                        <input type="text" id="qdbp-b-expiry_msg" class="qdbp-b-input regular-text"
                               data-sc-attr="expiry_msg" data-sc-default="This download link has expired."
                               value="This download link has expired." />
                    </div>
                </div>
            </div>

        </div><!-- /.qdbp-builder-form -->

        <!-- ── Right: output panel ──────────────────────────────── -->
        <div class="qdbp-builder-output-wrap">
            <div class="qdbp-builder-output">
                <h2 class="qdbp-builder-output__title">
                    <?php esc_html_e( 'Your Shortcode', 'quick-download-button' ); ?>
                </h2>
                <textarea id="qdbp-builder-result" class="qdbp-builder-result" rows="5" readonly></textarea>
                <button type="button" id="qdbp-copy-shortcode" class="button button-primary qdbp-copy-btn">
                    <?php esc_html_e( 'Copy shortcode', 'quick-download-button' ); ?>
                </button>
                <p class="qdbp-builder-output__hint">
                    <?php esc_html_e( 'Paste into any post, page, or widget.', 'quick-download-button' ); ?>
                </p>
            </div>
        </div>

    </div><!-- /.qdbp-builder-layout -->
</div>
