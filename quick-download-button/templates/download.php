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

			require_once dirname( __DIR__ ) . '/class/download.class.php';

			$download = new QDBU_DownloadFile( $attachment_id );

			//Download the file
			$download->file_from_url();
		}
	}

	// This is external download.
	if ( isset( $_GET['external_link'] ) ) {
		$url      = esc_attr( $_GET['external_link'] );
		header( 'Location: ' . $url ); // Direct to location
		exit;
	}
}


