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
	public $color_font;
	public $color_icon_dark;
	public $msg;
	public $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22"><path d="M18 11.3l-1-1.1-4 4V3h-1.5v11.3L7 10.2l-1 1.1 6.2 5.8 5.8-5.8zm.5 3.7v3.5h-13V15H4v5h16v-5h-1.5z" /></svg>';
	public $button_type;
	private $user_role;
	public $validate_msg;



	public function __construct() {
		add_shortcode( 'quick_download_button', array( $this, 'quick_download_button_shortcode' ) );
	}

	/**
	 * @usage Add shortcode
	 *
	 * @param  mixed $attr
	 * @return void
	 */
	public function quick_download_button_shortcode( $attr ) {

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
				'validate_msg'			=> ''
			),
			$attr
		);

		global $post;
		$this->pid          = $post->ID;
		$this->download_pid = (int) get_option( 'qdbu_quick_download_button_page_id' );

		$this->attachment_id = attachment_url_to_postid( $this->a['url'] );  //get attachment id from URL

		$quick_download_button_url = qdbu_default_url() . '?aid=' . $this->attachment_id; //pass attachment id to plugin download file


		$this->url = ! empty( $this->a['url'] && strpos( $this->a['url'], site_url()) !== false ) ? $quick_download_button_url : $this->a['url_external']; //if url value is empty set url to external url (url_external)

		$this->open_new_window = 'true' === $this->a['open_new_window'] ? 'true' : 'false';

		$this->wait = $this->a['wait'] > 0 ? $this->a['wait'] : 0;

		$this->color_gb = null !==  $this->a['color_bg'] ? $this->a['color_bg'] : null;

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

		$hide_size = '' === $this->a['file_size'] ? ' hide-size' : '' ;
		$hide_file = '' === $this->a['extension'] ? ' hide-file' : '' ;

		// Check priviledge - member
		if (in_array($this->user_role, qdbn_get_current_user_roles())) {
			$this->a['validate'] = true;
		} else {
			$this->validate_msg = $this->user_role .' account required.';
		}
		// Check priviledge - login
		if ( 'loggedin' === $this->user_role ) {
			if ( is_user_logged_in() ) {
				$this->a['validate'] = true;
			} else {
				$this->validate_msg = 'You must be logged in to download.';
			}
		}

		ob_start();
		?>
	<div className="qdbn-wrapper">
	<div class="qdbn" 
	data-plugin-name="qdbn"
	data-style="<?php echo esc_attr( $this->button_type ); ?>" 
	data-file="<?php echo esc_attr( $hide_file ) ;?>"
	data-size="<?php echo esc_attr( $hide_size ) ; ?>"
	<?php 
	echo wp_kses( $l1_style, array( 
		'style' => array(
			'padding' => array(),
			'text-align' => array(),
			'margin' => array()
		)	
	) ); ?>
		>
		<div class="qdbn-download-button-inner">
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
						$button_styles = 'style="';
						if ( 'small' === $this->button_type && null !== $this->color_gb 
							|| 'mid' === $this->button_type && null !== $this->color_gb 
							|| 'basic' === $this->button_type && null !== $this->color_gb
							) {
							$button_styles .= 'background: ' .esc_attr( $this->color_gb ). ';';
						}
						if (null !== $this->color_font) {
							$button_styles .= 'color: ' .esc_attr( $this->color_font ). ';';
						}
						if(null !== $this->border_radius) {
							$button_styles .= 'border-radius: ' .esc_attr( $this->border_radius ). 'px;';
						}

						if(null !== $this->border_width || null !== $this->border_style || null !== $this->border_color) {
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
					<span class="download-btn-icon"><?php qdb_sanitize_svg( $this->icon ) ;?></span>
					<span><?php echo esc_attr( $this->a['title'] ); ?></span>
			</button>

			<?php 
				$p_styles = 'style="';
				if ( 'large' === $this->button_type && null !== $this->color_gb || 'mid' === $this->button_type && null !== $this->color_gb ) {
					$p_styles .= 'background: ' .esc_attr( $this->color_gb ). ';';
				} 
				$p_styles .= '"';
			
			?>
			<?php if ( '0' !== $this->a['extension'] ) : ?>
				<p class="up" <?php echo $p_styles; ?>>
					<i class="<?php echo esc_attr( $this->qdbu_extension( 'icon' ) ); ?>"></i>
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
				
				printf( __( '<p class="down" %3$s><i class="fi-folder-o"></i><span class="file-size">%1$s %2$s</span></p>' ), esc_attr( $blob_number ), esc_attr( $blob_measure ), 
				$p_styles );

			elseif ( '' !== $this->a['file_size'] ) :
				/* translators: %1$s is a filesize */
				printf( __( '<p class="down" %2$s><i class="fi-folder-o"></i><span class="file-size"> %1$s </span></p>' ), esc_attr( $this->a['file_size'] ), 
				$p_styles 
				);

				//else :
				?>

			<?php endif; ?>

		</div>
	</div>
	<quick-download-button-info class="qdb-btn-info"></quick-download-button-info>
	</div>
		<?php
		return ob_get_clean();
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








