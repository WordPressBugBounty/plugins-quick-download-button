<?php
/*
*	Plugin Settings Page
*/
// If this file is called directly, abort. //
if ( ! defined( 'WPINC' ) ) {
	die;} // end if

class QDBNsettings {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			'Quick Download',
			'manage_options',
			'quick-download-button-dashboard',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'qdbn_option_name' );
		?>
		<div class="areoi-container">
			<div class="areoi-container__content">
                <div class="areoi-sidebar">
                    <div class="areoi-sidebar__content">
                    <h1><img style="display: block;" src="<?php echo esc_url( QDBN__PLUGIN_URI . 'assets/img/qdbn-logo.svg' ) ?>" width="150"></h1>
                    <ul class="areoi-sidebar__list">
                        <li class="areoi-sidebar__link active"><a href="">Button Style</a></li>
                        <li class="areoi-sidebar__link"><a href="">URL Settings</a></li>
						<li class="areoi-sidebar__link"><a href="">Countdown Settings</a></li>
						<li class="areoi-sidebar__link"><a href="">Button Background Color</a></li>
						<li class="areoi-sidebar__link"><a href="">Button Text Color</a></li>
						<li class="areoi-sidebar__link"><a href="">Button Icon Color</a></li>
						<li class="areoi-sidebar__link"><a href="">Button Border</a></li>
                    </ul>
					<ul class="areoi-sidebar__list">
						<li class="areoi-sidebar__link"><a href="">Options</a></li>
					</ul>
                    </div>
                </div><!--sidebar-->
                <div style="max-width: 1550px; height: 100%;">
                    <div class="areoi-container__body">
						
						<div class="areoi-header">
							<h2>Page Title</h2>
						</div><!-- .areoi-header -->

						<form method="post" action="options.php" class="areoi-form"> 
							<div class="areoi-body">

								<p>Body</p>

							</div><!-- .areoi-body -->

						</form>

						<?php include( QDBN__PLUGIN_DIR . 'views/more-info.php' ); ?>

						<div style="clear: both; display: block;"></div>

						

                        <div class="areoi-form-button">
                            <div class="areoi-form-button__body">

                                <div class="areoi-form-button__group">
                                    <button class="button" data-type="1">Save Settings</button>
                                    <button class="button button-primary" data-type="2">Save & Recompile CSS</button>
                                </div>

                                <span class="spinner" style="float: none;"></span>

                                <p class="areoi-form-button__alert">
                                    The CSS failed to compile. This is usually because of an invalid variable. Check your variable values and try again. 
                                </p>
                            </div><!-- .areoi-body -->
					    </div><!-- .areoi-form-button -->

					</div><!-- .areoi-container__body -->
                    <div class="areoi-settings-container areoi-settings-message-footer">
                        <div class="areoi-settings-row">
                        <div class="areoi-settings-col-12">
                            <p>If you like this plugin please leave us a <a href="https://wordpress.org/support/plugin/quick-download-button/reviews/#new-post" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> review on WordPress.org!</p>
                        </div>
                        </div>
                    </div>
                </div>
		    </div>
        </div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'qdbn_option_group', // Option group
			'qdbn_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'Enter Size Guide Image URL', // Title
			array( $this, 'print_section_info' ), // Callback
			'quick-download-button-dashboard' // Page
		);

		add_settings_field(
			'image_url',
			'Image URL',
			array( $this, 'imageurl_callback' ),
			'quick-download-button-dashboard',
			'setting_section_id'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();

		if ( isset( $input['image_url'] ) ) {
			$new_input['image_url'] = sanitize_text_field( $input['image_url'] );
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
		print 'Enter image url below:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function imageurl_callback() {
		printf(
			'<input type="text" class="w-100" id="image_url" name="qdbn_option_name[image_url]" value="%s" />',
			isset( $this->options['image_url'] ) ? esc_attr( $this->options['image_url'] ) : ''
		);
	}
}

if ( is_admin() ) {
	$canvas_size_settings_page = new QDBNsettings();
}