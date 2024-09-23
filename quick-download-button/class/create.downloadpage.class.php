<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

	/**
	 * QDBU_CreateDownloadPage
	 * @usage For creating page
	 */
class QDBU_CreateDownloadPage {
	private $current_user;
	private $page;
	private $pid;
	private $title;

	public function quick_download_page() {

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		global $wpdb;

		$this->title = 'Quick Download Button';

		if ( null == $this->get_page_by_title() ) {

			$this->current_user = wp_get_current_user();

			// create post object
			$this->page = array(
				'post_title'  => __( 'Quick Download Button' ),
				'post_status' => 'publish',
				'post_author' => $this->current_user->ID,
				'post_type'   => 'page',
			);

			// Insert the post into the database
			if ( current_user_can( 'administrator' ) ) {
				$this->pid = wp_insert_post( $this->page );
			}

			// Store the page ID
			if ( false === get_option( 'qdbu_quick_download_button_page_id' ) && false === update_option( 'qdbu_quick_download_button_page_id', false ) ) {
				add_option( 'qdbu_quick_download_button_page_id', $this->pid );
			}

			//Update page ID after re-activation
			if ( $this->pid !== get_option( 'qdbu_quick_download_button_page_id' ) ) {
				update_option( 'qdbu_quick_download_button_page_id', $this->pid );
			}
		}

	}

	/**
	 * Used to check that the page exist before creating another page
	 * after the plugin is activated.
	 */
	public function get_page_by_title() {
		$page_got_by_title = null;
		$query = new WP_Query(
			array(
				'post_type'              => 'page',
				'title'                  => $this->title,
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);
		 
		if ( ! empty( $query->post ) ) {
			$page_got_by_title = $query->post;
		} 

		return $page_got_by_title;
	}


	/**
	 * @usage Check if page already exists using page title
	 *
	 * @param $title
	 * @return void
	 */
	public function post_exists( $title ) {
		global $wpdb;

		$post_title = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args  = array();

		if ( ! empty( $title ) ) {
			$query .= ' AND post_title = %s';
			$args[] = $post_title;
		}

		if ( ! empty( $args ) ) {
			return (int) $wpdb->get_var( $wpdb->prepare( $query, $args ) );
		}

		return 0;
	}

	/**
	 * @usage Check duplicate title
	 *
	 * @param $title
	 * @return void
	 */
	public function post_check_duplicates( $title ) {

		$post_id = post_exists( $title );
		if ( ! $post_id ) {
			return 0;
		} else {
			return 1;
		}

	}

	/**
	 * @usage return ID of duplicate post
	 *
	 * @param $title
	 * @return int
	 */
	public function getid_duplicates( $title ) {

		$post_id = post_exists( $title );
		if ( $post_id ) {
			return (int) $post_id;
		}
	}

	/** @usage check if we are on multi site or single site.
	 * $param $network_active
	 */

	public function activation_logic($network_active){
		// If multisite
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				// If network activate, iterate through each site
				if ( $network_active ) {
						if ( false == is_super_admin() ) {
								return;
						}
	
						$blogs = get_sites(); // default count is 100
						foreach ( $blogs as $blog ) {
							$blog_id = $blog->blog_id;
							switch_to_blog( $blog_id );
							$this->quick_download_page(); // do your activation hook
							restore_current_blog();
						}
				// single site activation in multisite
				} else {
						if ( false == current_user_can( 'activate_plugins' ) ) {
								return;
						}
	
						$this->quick_download_page(); // do your activation hook
				}
		// Normal WordPress plugin activation
		} else {
			$this->quick_download_page(); // do your activation hook
		}
	}

}


