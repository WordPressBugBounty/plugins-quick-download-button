<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly


$nonce = $_REQUEST['_wpnonce'];
if ( ! wp_verify_nonce( $nonce, 'qdbutton_nonce_action' ) ) {

	print 'Sorry, your nonce did not verify.';

	exit;

} else {
	// Get the attachment ID. This is internal download
	if ( isset( $_GET['aid'] ) ) {
		$attachment_id = esc_attr( $_GET['aid'] );

		//Validate number
		if ( intval( $attachment_id ) ) {

			// Build context array passed to all download hooks.
			$context = array(
				'attachment_id' => (int) $attachment_id,
				'external_url'  => '',
				'user_id'       => get_current_user_id(),
				'ip'            => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				'user_agent'    => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'timestamp'     => current_time( 'mysql' ),
			);

			/**
			 * Filter: qdb_can_download
			 *
			 * Gate filter — return false to block the download entirely.
			 * Used by Pro: download limit, WooCommerce purchase gate, email gate.
			 *
			 * @param bool  $can_download Whether the download is allowed.
			 * @param array $context      Download context (attachment_id, user_id, ip, …).
			 */
			$can_download = apply_filters( 'qdb_can_download', true, $context );

			if ( ! $can_download ) {
				wp_die( esc_html__( 'Access denied.', 'quick-download-button' ), '', array( 'response' => 403 ) );
			}

			/**
			 * Action: qdb_before_download
			 *
			 * Fires when access is approved, before the file is served.
			 * Used by Pro: download tracking, download logs, admin email notification,
			 * notify user by email.
			 *
			 * @param array $context Download context (attachment_id, user_id, ip, …).
			 */
			do_action( 'qdb_before_download', $context );

			require_once dirname( __DIR__ ) . '/class/download.class.php';

			$download = new QDBU_DownloadFile( $attachment_id );

			//Download the file
			$download->file_from_url();
		}
	}

	// This is external download.
	if ( isset( $_GET['external_link'] ) ) {
		$url = esc_url_raw( wp_unslash( $_GET['external_link'] ) );

		// Build context array for external downloads.
		$context = array(
			'attachment_id' => 0,
			'external_url'  => $url,
			'user_id'       => get_current_user_id(),
			'ip'            => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'user_agent'    => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'timestamp'     => current_time( 'mysql' ),
		);

		/** @see qdb_can_download */
		$can_download = apply_filters( 'qdb_can_download', true, $context );

		if ( ! $can_download ) {
			wp_die( esc_html__( 'Access denied.', 'quick-download-button' ), '', array( 'response' => 403 ) );
		}

		/** @see qdb_before_download */
		do_action( 'qdb_before_download', $context );

		header( 'Location: ' . $url ); // Direct to location
		exit;
	}
}


