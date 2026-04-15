<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shortcode class
 */
class QDBU_QuickDownloadShortCode {


	public $a;
	public $pid;
	public $attachment_id;
	public $url;
	public $open_new_window;
	public $wait;
	public $download_pid;
	public $color_gb;
	public $panel_color;
	public $icon_id;
	public $custom_file_type_icon;
	public $file_size_icon_id;
	public $custom_file_size_icon;
	public $icon_position;
	public $color_font;
	public $color_icon_dark;
	public $msg;
	public $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22"><path d="M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z" /></svg>';
	public $button_type;
	private $user_role;
	public $validate_msg;
	public $popup_content;
	public $popup_closable;



	public function __construct() {
		add_shortcode( 'quick_download_button', array( $this, 'quick_download_button_shortcode' ) );
	}

	/**
	 * @usage Add shortcode
	 *
	 * @param  mixed $attr
	 * @return void
	 */
	public function quick_download_button_shortcode( $attr, $content = null ) {

		$this->a = shortcode_atts(
			array(
				'title'          		=> 'Download',
				'file_size'      		=> '',
				'url'            		=> '',
				'extension'      		=> '',
				'extension_text' 		=> '0',
				'url_external'   		=> '',
				'open_new_window'		=> 'false',
				'wait'			 		=> 0,
				'color_bg'				=> null,
				'panel_color'			=> null,
				'icon_id'                => 'default',
				'custom_file_type_icon'  => '',
				'file_size_icon_id'      => 'folder',
				'custom_file_size_icon'  => '',
				'icon_position'          => 'left',
				'color_font'			=> null,
				'color_icon_dark'		=> 'true',
				'msg'					=> 'Please wait...',
				'button_type'			=> 'large',
				'border_width'			=> null,
				'border_style'			=> null,
				'border_color'			=> null,
				'border_radius'			=> null,
				'align'					=> null,
				'padding'				=> null,
				'user_must_be'			=> '',
				'validate'		        => false,
				'validate_msg'			=> '',
				'popup_closable'		=> '1'
			),
			$attr
		);

		/**
		 * Filter: qdb_shortcode_atts
		 *
		 * Modify or extend parsed shortcode attributes before the button is built.
		 * Used by Pro: add 'required_product_id', 'download_limit', 'email_gate', etc.
		 *
		 * @param array $atts Parsed shortcode attributes.
		 * @param array $attr Raw attributes passed to the shortcode.
		 */
		$this->a = apply_filters( 'qdb_shortcode_atts', $this->a, $attr );

		global $post;
		$this->pid          = $post->ID;
		$this->download_pid = (int) get_option( 'qdbu_quick_download_button_page_id' );

		$this->attachment_id = attachment_url_to_postid( $this->a['url'] );  //get attachment id from URL

		$quick_download_button_url = qdbu_default_url() . '?aid=' . $this->attachment_id; //pass attachment id to plugin download file


		$this->url = ! empty( $this->a['url'] && strpos( $this->a['url'], site_url()) !== false ) ? $quick_download_button_url : $this->a['url_external']; //if url value is empty set url to external url (url_external)

		$this->open_new_window = 'true' === $this->a['open_new_window'] ? 'true' : 'false';

		$this->wait = $this->a['wait'] > 0 ? $this->a['wait'] : 0;

		$this->color_gb = null !==  $this->a['color_bg'] ? $this->a['color_bg'] : null;

		$this->panel_color = null !== $this->a['panel_color'] ? $this->a['panel_color'] : null;

		$this->icon_id               = sanitize_key( $this->a['icon_id'] );
		$this->custom_file_type_icon = wp_kses( $this->a['custom_file_type_icon'], array( 'svg' => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true ), 'path' => array( 'd' => true, 'fill' => true ), 'g' => array( 'fill' => true ) ) );
		$this->file_size_icon_id     = sanitize_key( $this->a['file_size_icon_id'] );
		$this->custom_file_size_icon = wp_kses( $this->a['custom_file_size_icon'], array( 'svg' => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true ), 'path' => array( 'd' => true, 'fill' => true ), 'g' => array( 'fill' => true ) ) );
		$this->icon_position         = in_array( $this->a['icon_position'], array( 'left', 'right' ), true ) ? $this->a['icon_position'] : 'left';
		$this->popup_content         = $content ? do_shortcode( $content ) : null;
		$this->popup_closable        = '0' === $this->a['popup_closable'] ? '0' : '1';

		$this->color_font = null !==  $this->a['color_font'] ? $this->a['color_font'] : null;

		$this->color_icon_dark = 'true' ===  $this->a['color_icon_dark'] ? 'true' : 'false';

		$this->msg = !empty($this->a['msg']) && '' !== $this->a['msg'] ? $this->a['msg'] : 'Please wait...';

		$this->button_type = !empty($this->a['button_type']) ? $this->a['button_type'] : 'large';

		$this->border_width = !null !==  $this->a['border_width'] ? $this->a['border_width'] : null;

		$this->border_style = !null !==  $this->a['border_style'] ? $this->a['border_style'] : null;

		$this->border_color = !null !==  $this->a['border_color'] ? $this->a['border_color'] : null;

		$this->border_radius = !null !==  $this->a['border_radius'] ? $this->a['border_radius'] : null;

		$this->padding = !null !==  $this->a['padding'] ? $this->a['padding'] : null;

		$this->align = !null !==  $this->a['align'] ? $this->a['align'] : null;

		$this->user_role = '' !== $this->a['user_must_be'] ? trim(strtolower( $this->a['user_must_be'])) : '';

		$this->validate_msg = '' !== $this->a['validate_msg'] ? esc_attr($this->a['validate_msg']) : 'Please contact the admin.';


		return $this->generate_button();
	}


	/**
	 * @Output download button html
	 *
	 * @return void
	 */
	public function generate_button() {

		$l1_style = 'style="';
		if(null !== $this->padding) {
			$l1_style .= 'padding-top:' .esc_attr( $this->padding ). 'px;';
			$l1_style .= 'padding-bottom:' .esc_attr( $this->padding ). 'px;';
		}
		if(null !== $this->align) {
			$l1_style .= 'padding-top: ' .esc_attr( $this->padding ). 'px;';
			$l1_style .= 'text-align: ' .esc_attr( $this->align ).';';
		}
		$l1_style .= '"';

		// Compute btn_id once so it is available throughout generate_button() and to all Pro filters/actions.
		$this->a['btn_id'] = substr( md5( $this->pid . '_' . $this->attachment_id . '_' . $this->a['url_external'] ), 0, 12 );

		$hide_size = '' === $this->a['file_size'] ? ' hide-size' : '' ;
		$hide_file = '' === $this->a['extension'] ? ' hide-file' : '' ;

		// Built-in role/login check.
		if ( in_array( $this->user_role, qdbn_get_current_user_roles() ) ) {
			$this->a['validate'] = true;
		} else {
			$this->validate_msg = $this->user_role . ' account required.';
		}
		if ( 'loggedin' === $this->user_role ) {
			if ( is_user_logged_in() ) {
				$this->a['validate'] = true;
			} else {
				$this->validate_msg = 'You must be logged in to download.';
			}
		}

		/**
		 * Filter: qdb_user_can_access
		 *
		 * Override or extend the access check for the download button.
		 * Return array with 'allowed' (bool) and 'message' (string).
		 * Used by Pro: WooCommerce purchase gate, email gate, download limit UI.
		 *
		 * @param array $access  { 'allowed' => bool, 'message' => string }
		 * @param array $atts    Shortcode attributes.
		 * @param int   $user_id Current user ID (0 for guests).
		 */
		$access = apply_filters(
			'qdb_user_can_access',
			array(
				'allowed' => $this->a['validate'],
				'message' => $this->validate_msg,
			),
			$this->a,
			get_current_user_id()
		);
		$this->a['validate'] = $access['allowed'];
		$this->validate_msg  = $access['message'];

		ob_start();
		?>
	<div className="qdbn-wrapper">
	<div class="qdbn" 
	data-plugin-name="qdbn"
	data-style="<?php echo esc_attr( $this->button_type ); ?>"
	data-file="<?php echo esc_attr( $hide_file ) ;?>"
	data-size="<?php echo esc_attr( $hide_size ) ; ?>"
	data-icon-position="<?php echo esc_attr( $this->icon_position ); ?>"
	<?php 
	echo wp_kses( $l1_style, array( 
		'style' => array(
			'padding' => array(),
			'text-align' => array(),
			'margin' => array()
		)	
	) ); ?>
		>
		<div class="qdbn-download-button-inner"<?php
		$new_styles = array( 'pill', 'card', 'ghost' );
		if ( in_array( $this->button_type, $new_styles, true ) && ( null !== $this->border_width || null !== $this->border_style || null !== $this->border_color ) ) {
			$c_width = null !== $this->border_width ? esc_attr( $this->border_width ) : '0';
			$c_style = null !== $this->border_style ? esc_attr( $this->border_style ) : 'solid';
			$c_color = null !== $this->border_color ? esc_attr( $this->border_color ) : 'transparent';
			echo ' style="border:' . $c_width . 'px ' . $c_style . ' ' . $c_color . ';"';
		}
	?>>
			<button class="g-btn f-l" type="button" title="<?php echo esc_attr( $this->a['title'] ); ?>" 
				<?php if( $this->a['wait'] > 0 ) : ?> 
					data-spinner="<?php echo absint( $this->a['wait'] ); ?>"
				<?php endif; ?>
				<?php if ( '' !== $this->msg ) : ?>
					data-msg="<?php echo esc_attr( $this->msg ); ?>";
				<?php endif; ?>
				<?php if ('' !== $this->a['user_must_be'] && $this->a['validate']) : ?>
					data-validate="1"
				<?php endif; ?>
				<?php if ('' !== $this->a['user_must_be'] && !$this->a['validate']) : ?>
					data-validate="0"
					data-validate-msg="<?php echo esc_attr($this->validate_msg ); ?>"
				<?php endif; ?>
					data-has-icon-dark="<?php echo esc_attr( $this->color_icon_dark ); ?>"
			<?php if ( $this->popup_content ) : ?>data-qdb-popup="1"<?php endif; ?>
			<?php if ( $this->popup_content && '0' === $this->popup_closable ) : ?>data-qdb-popup-closable="0"<?php endif; ?>
			data-qdb-btn-id="<?php echo esc_attr( $this->a['btn_id'] ); ?>"
				<?php
				if ( empty( $this->a['url_external'] ) && strpos( $this->a['url'], site_url()) !== false)  :
					?>
					data-attachment-id="<?php echo esc_attr( $this->add_ids( $this->attachment_id, $this->download_pid ) ); ?>" data-page-id="<?php echo intval( $this->download_pid ); ?>" data-post-id="<?php echo intval( $this->pid ); ?>" 
					<?php
				else :
					?>
					data-external-url="<?php echo esc_url( $this->a['url_external'] ); ?>" 
				<?php endif; ?>
					data-target-blank="<?php echo esc_attr($this->a['open_new_window']);?>"
					<?php
					/**
					 * Filter: qdb_button_data_atts
					 *
					 * Add extra data-* attributes to the download button element.
					 * Used by Pro: email gate injects data-email-gate="1",
					 * WooCommerce gate injects data-product-id="X", etc.
					 * Return an associative array of attribute name => value.
					 *
					 * @param array $extra_atts Key/value pairs for data attributes.
					 * @param array $atts       Shortcode attributes.
					 */
					$extra_data_atts = apply_filters( 'qdb_button_data_atts', array(), $this->a );
					foreach ( $extra_data_atts as $attr_name => $attr_value ) {
						echo ' ' . esc_attr( $attr_name ) . '="' . esc_attr( $attr_value ) . '"';
					}
					?>
					<?php
						$button_styles = 'style="';
						if ( null !== $this->color_gb && in_array( $this->button_type, array( 'small', 'mid', 'basic', 'pill', 'card', 'ghost' ), true ) ) {
							$button_styles .= 'background: ' .esc_attr( $this->color_gb ). ';';
						}
						if (null !== $this->color_font) {
							$button_styles .= 'color: ' .esc_attr( $this->color_font ). ';';
						}
						if(null !== $this->border_radius) {
							$button_styles .= 'border-radius: ' .esc_attr( $this->border_radius ). 'px;';
						}

						// Border on the button only for legacy styles; new styles (pill/card/ghost) get it on the container.
						if ( ! in_array( $this->button_type, array( 'pill', 'card', 'ghost' ), true ) && ( null !== $this->border_width || null !== $this->border_style || null !== $this->border_color ) ) {
							$width = null !== $this->border_width ? esc_attr( $this->border_width ) : '0';
							$b_style = null !== $this->border_style ? esc_attr( $this->border_style) : 'solid';
							$color = null !== $this->border_color ? esc_attr( $this->border_color) : 'transparent';
							$button_styles .= 'border: '.$width.'px '.$b_style. ' '.$color;
						}
						
						$button_styles .= '"';
						echo wp_kses( $button_styles, array( 
							'background' => array(
								'color' => array()
							),
							'color' => array(),
							'border' => array()
						) );
					?>
			      >
					<span class="download-btn-icon"><?php
	if ( 'default' === $this->icon_id ) {
		qdb_sanitize_svg( $this->icon );
	} else {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo qdb_get_builtin_icon( $this->icon_id );
	}
?></span>
					<span><?php echo esc_attr( $this->a['title'] ); ?></span>
			</button>

			<?php 
				$p_styles = 'style="';
				if ( 'large' === $this->button_type && null !== $this->color_gb || 'mid' === $this->button_type && null !== $this->color_gb ) {
					$p_styles .= 'background: ' .esc_attr( $this->color_gb ). ';';
				} elseif ( in_array( $this->button_type, array( 'pill', 'card', 'ghost' ), true ) && null !== $this->panel_color ) {
					$p_styles .= 'background: ' . esc_attr( $this->panel_color ) . ';';
				}
				$p_styles .= '"';
			
			?>
			<?php if ( '0' !== $this->a['extension'] ) : ?>
				<p class="up" <?php echo $p_styles; ?>>
					<?php if ( $this->custom_file_type_icon ) : ?>
	<?php echo wp_kses( $this->custom_file_type_icon, array( 'svg' => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true ), 'path' => array( 'd' => true, 'fill' => true ), 'g' => array( 'fill' => true ) ) ); ?>
<?php else : ?>
	<i class="<?php echo esc_attr( $this->qdbu_extension( 'icon' ) ); ?>"></i>
<?php endif; ?>
					<?php
					if ( '1' === $this->a['extension_text'] ) {
						echo '<span>' . esc_html( $this->qdbu_extension( 'ext' ) ) . '</span>';
					}
					?>
				</p>
			<?php endif; ?>

			<?php
			$have_site_url = strpos( $this->a['url'], site_url() ) === false ? false: true;

			if ( '1' === $this->a['file_size'] && $have_site_url ) :
				$file_url  = filesize( $this->qdb_convert_url_to_path( $this->a['url'] ) );
				$file_size = $this->qdb_format_size_units( $file_url );
				if ( '0 bytes' !== $file_size ) {
					$file_blob_size = $this->qdb_format_size_units( $file_url );
					$blob_number    = explode( ' ', $file_blob_size );
					$blob_number    = $blob_number[0];

					$blob_measure = explode( ' ', $file_blob_size );
					$blob_measure = $blob_measure[1];
				}
				/* translators: %1$s is a filesize %2$s is the measurement */
				$size_icon_html = $this->custom_file_size_icon ? wp_kses( $this->custom_file_size_icon, array( 'svg' => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true ), 'path' => array( 'd' => true, 'fill' => true ), 'g' => array( 'fill' => true ) ) ) : qdb_get_builtin_icon( $this->file_size_icon_id );
				printf( '<p class="down" %3$s>%4$s<span class="file-size">%1$s %2$s</span></p>', esc_attr( $blob_number ), esc_attr( $blob_measure ), $p_styles, $size_icon_html );

			elseif ( '' !== $this->a['file_size'] ) :
				/* translators: %1$s is a filesize */
				$size_icon_html = $this->custom_file_size_icon ? wp_kses( $this->custom_file_size_icon, array( 'svg' => array( 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'aria-hidden' => true ), 'path' => array( 'd' => true, 'fill' => true ), 'g' => array( 'fill' => true ) ) ) : qdb_get_builtin_icon( $this->file_size_icon_id );
				printf( '<p class="down" %2$s>%3$s<span class="file-size"> %1$s </span></p>', esc_attr( $this->a['file_size'] ), $p_styles, $size_icon_html );

				//else :
				?>

			<?php endif; ?>

		</div>
	</div>
	<quick-download-button-info class="qdb-btn-info"></quick-download-button-info>
	<?php if ( $this->popup_content ) : ?>
	<div class="qdb-popup-src" hidden><?php echo $this->popup_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted: processed by do_shortcode, output by editor ?></div>
	<?php endif; ?>
	<?php
	/**
	 * Action: qdb_gate_html
	 *
	 * Output additional HTML inside the button wrapper (after the button, before closing div).
	 * Used by Pro: inject email gate form, passcode input, etc.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	do_action( 'qdb_gate_html', $this->a );
	?>
	</div>
		<?php
		/**
		 * Filter: qdb_shortcode_output
		 *
		 * Modify the final button HTML before it is returned to the page.
		 * Used by Pro: wrap with email capture form, modal overlay, etc.
		 *
		 * @param string $output The complete button HTML.
		 * @param array  $atts   Shortcode attributes.
		 */
		return apply_filters( 'qdb_shortcode_output', ob_get_clean(), $this->a );
	}

	/**
	 * @usage Add file extension text, add icon
	 * @param  mixed $value
	 * @return void
	 */
	public function qdbu_extension( $value = '' ) {
		$ex = array(
			'ext'  => '',
			'icon' => '',
		);

		$extension_array = array( 'pdf', 'mp3', 'mov', 'zip', 'txt', 'doc', 'xml', 'mp4', 'ppt' );

		$extension_path = explode( '.', $this->a['url'] );

		$ex['ext'] = end( $extension_path );
		$ex['ext'] = $ex['ext'];

		if ( in_array( strtolower( $ex['ext'] ), $extension_array ) ) {
			$ex['icon'] = 'fi fi-' . strtolower( $ex['ext'] );
		} else {

			$image_array = array( 'jpg', 'jpeg', 'tiff', 'png', 'bmp', 'gif' );
			if ( in_array( strtolower( $ex['ext'] ), $image_array ) ) {
				$ex['icon'] = 'fi fi-image';
			} else {
				$ex['icon'] = 'fi fi-file';
			}
		}

		if ( 'icon' === $value ) {
			$value = $ex['icon'];
		}
		if ( 'ext' === $value ) {
			$value = $ex['ext'];
		}

		return $value;
	}



	/**
	 * @usage Convert URL to path
	 * @param  mixed $url
	 * @return string
	 */
	public function qdb_convert_url_to_path( $url ) {
		return str_replace(
			wp_get_upload_dir()['baseurl'],
			wp_get_upload_dir()['basedir'],
			$url
		);
	}



	/**
	 * @usage Get file size
	 *
	 * @param  mixed $bytes
	 * @return string
	 */
	public function qdb_format_size_units( $bytes ) {
		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
			$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
		} elseif ( $bytes > 1 ) {
			$bytes = $bytes . ' bytes';
		} elseif ( 1 === $bytes ) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}


	/**
	 * @usage add attachment ID + download page id, use this to disguise real attachment ID when view from browser inspect
	 * @param  mixed $num1
	 * @param  mixed $num2
	 * @return int
	 */
	public static function add_ids( $num1, $num2 ) {
		return intval( $num1 + $num2 );
	}
}

/**
 * Return an inline SVG string for a built-in icon ID.
 * Used by the shortcode to match the Gutenberg block's icon set.
 *
 * @param string $id   Icon ID (matches QDB_DOWNLOAD_ICONS / QDB_SIZE_ICONS in JS).
 * @param int    $size Icon size in px.
 * @return string  SVG HTML string, already escaped for output.
 */
function qdb_get_builtin_icon( $id, $size = 20 ) {
	$paths = array(
		'default' => 'M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z',
		'cloud'   => 'M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z',
		'circle'  => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 5h2v6h3l-4 4-4-4h3V7z',
		'file-dl' => 'M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z',
		'inbox'   => 'M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5v-3h3.56c.69 1.19 1.97 2 3.45 2s2.75-.81 3.45-2H19v3zm0-5h-4.99c0 1.1-.9 1.99-2 1.99S10 15.1 10 14H5V5h14v9z',
		'save'    => 'M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z',
		'bolt'    => 'M7 2v11h3v9l7-12h-4l4-8z',
		'folder'  => 'M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z',
		'archive' => 'M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z',
		'info'    => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z',
		'chip'    => 'M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z',
	);
	$id = isset( $paths[ $id ] ) ? $id : 'default';
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="' . absint( $size ) . '" height="' . absint( $size ) . '" aria-hidden="true"><path fill="currentColor" d="' . esc_attr( $paths[ $id ] ) . '"/></svg>';
}

/**
 * Sanitize SVG
 */

 function qdb_sanitize_svg( $svg ) {
	$kses_defaults = wp_kses_allowed_html( 'post' );

	$svg_args = array(
		'svg'   => array(
			'class'           => true,
			'aria-hidden'     => true,
			'aria-labelledby' => true,
			'role'            => true,
			'xmlns'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true // <= Must be lower case!
		),
		'g'     => array( 'fill' => true ),
		'title' => array( 'title' => true ),
		'path'  => array( 
			'd'               => true, 
			'fill'            => true  
		)
	);

	$allowed_tags = array_merge( $kses_defaults, $svg_args );

	echo wp_kses( $svg, $allowed_tags );
 }



/**
 * Get the current user's role.
 * @return array
 */

function qdbn_get_current_user_roles() {
 
	if( is_user_logged_in() ) {
   
	  $user = wp_get_current_user();
   
	  $roles = ( array ) $user->roles;
   
	  return $roles; // This will returns an array
   
	} else {
   
	  return array();
   
	}
   
}


function qdbn_get_user_rolls() {
	$data = ['Nothing','Logged in'];
	$roles = wp_roles()->get_names();

	foreach( $roles as $role ) {
		array_push($data, strtolower( translate_user_role( $role ) ));
	}
	return $data;
}


/**
 * Shortcode: [quick_download_button_group]
 *
 * Wraps multiple [quick_download_button] shortcodes in a flex row.
 *
 * Attributes:
 *   layout          - 'horizontal' (default) or 'stack'
 *   stack_on_mobile - 'true' (default) or 'false'
 *   alignment       - 'left' (default), 'center', or 'right'
 *   gap             - gap between buttons in px (default: 12)
 *
 * Example:
 *   [quick_download_button_group alignment="center" gap="16"]
 *     [quick_download_button url="..." title="Download v1"]
 *     [quick_download_button url="..." title="Download v2"]
 *   [/quick_download_button_group]
 */
function qdbu_button_group_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts(
		array(
			'layout'          => 'horizontal',
			'stack_on_mobile' => 'true',
			'alignment'       => 'left',
			'gap'             => '12',
		),
		$atts
	);

	$allowed_layouts   = array( 'horizontal', 'stack' );
	$allowed_alignment = array( 'left', 'center', 'right' );
	$align_map         = array( 'left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end' );

	$layout    = in_array( $atts['layout'], $allowed_layouts, true ) ? $atts['layout'] : 'horizontal';
	$alignment = in_array( $atts['alignment'], $allowed_alignment, true ) ? $atts['alignment'] : 'left';
	$gap       = absint( $atts['gap'] );

	$classes = 'qdb-btn-row qdb-btn-row--' . $layout;
	if ( 'true' === $atts['stack_on_mobile'] ) {
		$classes .= ' qdb-btn-row--mobile-stack';
	}
	$classes .= ' qdb-btn-row--align-' . $alignment;

	if ( 'stack' === $layout ) {
		$style = 'gap:' . $gap . 'px;align-items:' . $align_map[ $alignment ] . ';';
	} else {
		$style = 'gap:' . $gap . 'px;justify-content:' . $align_map[ $alignment ] . ';align-items:flex-start;';
	}

	return '<div class="' . esc_attr( $classes ) . '" style="' . esc_attr( $style ) . '">'
		. do_shortcode( $content )
		. '</div>';
}
add_shortcode( 'quick_download_button_group', 'qdbu_button_group_shortcode' );








