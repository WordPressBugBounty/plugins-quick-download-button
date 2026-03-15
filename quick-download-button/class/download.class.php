<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly


/**
 * Download File Class
 */
class QDBU_DownloadFile {
	protected $attachment_id;
	protected $file_url;
	protected $attachment_title;

	public function __construct( $attachment_id ) {
		$this->attachment_id = $attachment_id;
	}

	/**
	 * @usage Get the attachment file URL
	 * @return string
	 */
	public function get_full_path() {
		$this->attachment_title = get_the_title( $this->attachment_id );
		$this->file_url         = get_attached_file( $this->attachment_id );

		if ( $this->file_url && file_exists( $this->file_url ) ) {
			return $this->file_url;
		}
		return '';

	}

	/**
	 * @usage Download the attachment file
	 * @return void
	 */
	public function file_from_url() {
			$file_path = $this->get_full_path();

			if ( empty( $file_path ) ) {
				wp_die( 'File not found.', 'Download Error', array( 'response' => 404 ) );
			}

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"', true, 200 );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate' );
			header( 'Pragma: public' );
			header( 'Content-Length: ' . filesize( $file_path ) );
			readfile( $file_path );
			exit;
	}

	public function download_from_external_url( $url ) {
		header( 'Location: ' . $url ); // Direct to location
		exit;
	}


}
