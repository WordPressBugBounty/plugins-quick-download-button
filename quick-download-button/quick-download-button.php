<?php

/**
 * Plugin Name: Quick Download Button
 * Plugin URI: https://github.com/kusimo/quick-download-button
 * Description: Use to add download button link to post or page.
 * Version: 1.2.6
 * Author: Abidemi Kusimo
 *
 * @package quick-download-button
 */

defined( 'ABSPATH' ) || exit;

// Define globals properties
define( 'QDBN__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'QDBN__PLUGIN_URI', plugin_dir_url( __FILE__ ) );

/**
 * Load translations for the plugin from the /languages/ folder.
 *
 * @link https://developer.wordpress.org/reference/functions/load_plugin_textdomain/
 */

add_action( 'init', 'qdbu_quick_download_button_load_textdomain' );

function qdbu_quick_download_button_load_textdomain() {
	 load_plugin_textdomain( 'quick-download-button', false, basename( __DIR__ ) . '/languages' );
}

/**
 * Settings Page @todo
 * We need to add a settings page to provide UI for user to generate shortcode.
*/
/*
require_once plugin_dir_path( __FILE__ ) . '/class/settings.class.php';

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quick_download_button_page_settings_link' );
function quick_download_button_page_settings_link( $links ) {
	$links[] = '<a href="' .
	admin_url( 'options-general.php?page=quick-download-button-dashboard' ) .
	'">' . __( 'Settings' ) . '</a>';
	return $links;
}
*/

/**
 * Registers all block assets so that they can be enqueued through the Block Editor in
 * the corresponding context.
 *
 * @link https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-registration/
 */
add_action( 'init', 'qdbu_quick_download_button_register_blocks' );

function qdbu_quick_download_button_register_blocks() {
	//If Block Editor is not active, bail.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	/**
	 * Use asset file to automatically set the dependency list for enqueuing the script
	 */
	$quick_download_button_asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

	//Register the block editor script
	wp_register_script(
		'quick-download-button-editor-script',                       // label
		plugins_url( 'build/index.js', __FILE__ ),                     // script file
		$quick_download_button_asset_file['dependencies'],           // dependencies
		filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' )      // set version as file last modified time
	);

	//Localise script - download page ID
	wp_localize_script(
		'quick-download-button-editor-script',
		'qdbu_data',
		array(
			'download_page_id' => (int) get_option( 'qdbu_quick_download_button_page_id' ),
			'qdbn_user_roles'  => qdbn_get_user_rolls(),  
		)
	);

	//Register blocks script and styles
	register_block_type(
		'quick-download-button/download-button',
		array(
			'editor_script' => 'quick-download-button-editor-script',                    // Calls registered script above
			'editor_style'  => 'quick-download-button-editor-styles',                    // Calls registered stylesheet above
			'style'         => 'quick-download-button-front-end-styles',
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		/**
		 * Adds internationalization support.
		 *
		 * @link https://wordpress.org/gutenberg/handbook/designers-developers/developers/internationalization/
		 * @link https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
		 */
		wp_set_script_translations( 'quick-download-button-editor-script', 'quick-download-button', plugin_dir_path( __FILE__ ) . '/languages' );
	}
}

function qdbu_enqueue_block_editor_assets() {
    // Enqueue editor-specific styles
    wp_enqueue_style(
        'quick-download-button-editor-styles',                      // Handle for editor styles
        plugins_url('css/editor.css', __FILE__),                    // Editor CSS file
        array('wp-edit-blocks'),                                    // Dependencies
        filemtime(plugin_dir_path(__FILE__) . 'css/editor.css')     // Version: file last modified time
    );

    // Enqueue frontend styles as well
    wp_enqueue_style(
        'quick-download-button-front-end-styles',                   
        plugins_url('css/minified/style.min.css', __FILE__),        
        array(),                                                    
        filemtime(plugin_dir_path(__FILE__) . 'css/style.css')      
    );
}

add_action('enqueue_block_editor_assets', 'qdbu_enqueue_block_editor_assets');


function has_quick_download_button($post_id) {
    $post = get_post($post_id);
    $content = $post ? $post->post_content : '';

    // Check for shortcode
    if (has_shortcode($content, 'quick_download_button')) {
        return true;
    }

    // Check for Gutenberg block
    if (has_block('quick-download-button/download-button', $post_id)) {
        return true;
    }

    return false;
}

/**
 * Front end CSS
 */
function qdbu_enqueue_download_button_styles() {
    global $post;

    if (is_a($post, 'WP_Post') && has_quick_download_button($post->ID)) {
        wp_enqueue_style(
            'quick-download-button-front-end-styles',                     // label
            plugins_url('css/minified/style.min.css', __FILE__),         // CSS file
            array(),                                                      // dependencies
            filemtime(plugin_dir_path(__FILE__) . 'css/style.css')       // set version as file last modified time
        );
    }
}
add_action('wp_enqueue_scripts', 'qdbu_enqueue_download_button_styles');

/**
 * Front end Script
 */
function qdbu_button_front_end_script() {
		global $post;
		if (is_a($post, 'WP_Post') && has_quick_download_button($post->ID)) {
		wp_enqueue_script(
			'quick-download-button-frontend-script',                   
			plugins_url( 'frontend/minified/frontend.js', __FILE__ ),   
			array(),       
			filemtime( plugin_dir_path( __FILE__ ) . 'frontend/frontend.js' ),    
			true
		);

		wp_localize_script(
			'quick-download-button-frontend-script',
			'quick_download_object',
			array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'security'    => wp_create_nonce( 'qdbutton_nonce_action' ),
				'redirecturl' => qdbu_default_url(),
				'qdbn_user_roles'  => qdbn_get_current_user_roles(),  
			)
		);
	}

}
add_action( 'wp_enqueue_scripts', 'qdbu_button_front_end_script' );

add_action( 'wp_ajax_nopriv_quick-download-button-frontend-script', 'qdbu_download_ajax_referer' );
add_action( 'wp_ajax_quick-download-button-frontend-script', 'qdbu_download_ajax_referer' );


/**
 * Nonce to be used for download page
 */

function qdbu_download_ajax_referer() {
	 //nonce-field is created on page
	check_ajax_referer( 'qdbutton_nonce_action', 'security' );

	$return = array();
	$return . array_push( $script_params );

	echo $return;

	wp_die();
}

/**
 * Shortcode
 */

require_once 'class/shortcode.class.php';
$downloadShortcode = new QDBU_QuickDownloadShortCode( __FILE__ );
$downloadShortcode = new QDBU_QuickDownloadShortCode();


/**
 * Set default download page to 'quick-download-button/'
 * Incase user change the page slug, we use download page by ID
 * Download page name can be renamed before using the plugin. Rename just once and before using
 * */
if ( ! function_exists( 'qdbu_default_url' ) ) {
	function qdbu_default_url() {

		$quick_download_button_default_page = site_url() . '/quick-download-button/';

		if ( false !== get_option( 'qdbu_quick_download_button_page_id' ) ) {
			$quick_download_button_default_page = get_page_link( get_option( 'qdbu_quick_download_button_page_id' ) );
		}
		return $quick_download_button_default_page;
	}
}



add_filter( 'template_include', 'qdbu_download_button_plugin_templates' );

/**
 * Get Custom Template for the download page
 *
 * @param  mixed $template
 * @return void
 */
function qdbu_download_button_plugin_templates( $template ) {

	$quick_download_button_pid = get_option( 'qdbu_quick_download_button_page_id' );

	//Let's check we are on the right page and template file exists
	if ( is_page( 'quick-download-button' ) || is_page( $quick_download_button_pid ) && file_exists( plugin_dir_path( __DIR__ ) . 'quick-download-button/templates/qdb-page-template.php' ) ) {

		$template = dirname( __FILE__ ) . '/templates/qdb-page-template.php';
	}
	return $template;
}


// register custom meta tag field
/**
 * Feature development - to store the download url in the cutom field. Do not uncomment qdbu_register_post_meta.
 *
 * @return void
 */
function qdbu_register_post_meta() {
	register_post_meta(
		'post',
		'_qdbu_download_url',
		array(
			'show_in_rest'  => false,
			'single'        => true,
			'type'          => 'string',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}

//add_action( 'init', 'qdbu_register_post_meta' );

/**
 * Create download page after plugin activation
 */

require_once 'class/create.downloadpage.class.php';

$create_download_page = new QDBU_CreateDownloadPage();

register_activation_hook( __FILE__, array( $create_download_page, 'activation_logic' ) );


