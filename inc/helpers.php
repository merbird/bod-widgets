<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );


// Debug function 

function bod_debug_to_console ( $data , $text = "") {
	$output = $data;
	if ( is_array($output) ) {
		$output = implode(',' , $output);
	}

	echo "<script>console.log( 'Debug: " . $text . " " . $output . "' );</script>";
}

/**
 * Parsing vc_link field type properly
 *
 * @param string $value
 * @param bool $as_string Return prepared anchor attributes string instead of array
 *
 * @return mixed
 */
function bod_parse_link_value( $value, $as_string = FALSE ) {
	$result = array( 'url' => '', 'title' => '', 'target' => '' );
	$params_pairs = explode( '|', $value );
	if ( ! empty( $params_pairs ) ) {
		foreach ( $params_pairs as $pair ) {
			$param = explode( ':', $pair, 2 );
			if ( ! empty( $param[0] ) && isset( $param[1] ) ) {
				$result[ $param[0] ] = trim( rawurldecode( $param[1] ) );
			}
		}
	}

	if ( $as_string ) {
		$string = '';
		foreach ( $result as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$string .= ' ' . ( ( $attr == 'url' ) ? 'href' : $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		return $string;
	}

	return $result;
}


/**
 * Get image size information as an array
 *
 * @param string $size_name
 *
 * @return array
 */
function bod_get_intermediate_image_size( $size_name ) {
	global $_wp_additional_image_sizes;
	if ( isset( $_wp_additional_image_sizes[ $size_name ] ) ) {
		// Getting custom image size
		return $_wp_additional_image_sizes[ $size_name ];
	} else {
		// Getting standard image size
		return array(
			'width' => get_option( "{$size_name}_size_w" ),
			'height' => get_option( "{$size_name}_size_h" ),
			'crop' => get_option( "{$size_name}_crop" ),
		);
	}
}


/**
 * Get image size values for selector
 *
 * @param array $size_names List of size names
 *
 * @return array
 */
 
function bod_image_sizes_select_values( $size_names = array( 'large', 'medium', 'thumbnail', 'full' ) ) {
	$image_sizes = array();
	// For translation purposes
	$size_titles = array(
		'large' => __( 'Large', 'bod-flipbox' ),
		'medium' => __( 'Medium', 'bod-flipbox' ),
		'thumbnail' => __( 'Thumbnail', 'bod-flipbox' ),
		'full' => __( 'Full Size', 'bod-flipbox' ),
	);
	
	// loop round the sizes passed to us building the title which is name + width + height + cropped
	foreach ( $size_names as $size_name ) {
		$size_title = isset( $size_titles[ $size_name ] ) ? $size_titles[ $size_name ] : ucwords( $size_name );
		if ( $size_name != 'full' ) {
			// Detecting size
			$size = bod_get_intermediate_image_size( $size_name );
			$size_title .= ' - ' . ( ( $size['width'] == 0 ) ? __( 'Any', 'bod-flipbox' ) : $size['width'] );
			$size_title .= 'x';
			$size_title .= ( $size['height'] == 0 ) ? __( 'Any', 'bod-flipbox' ) : $size['height'];
			$size_title .= ' (' . ( $size['crop'] ? __( 'cropped', 'bod-flipbox' ) : __( 'not cropped', 'bod-flipbox' ) ) . ')';
		}
		$image_sizes[ $size_name ] = $size_title;
	}

	return apply_filters( 'bod_image_sizes_select_values', $image_sizes );
}

/**
 * Sanitize color field for RGBA or Hex colors
 *
 * @param string $color
 *
 * @return string sanitized color
 */
 
 function bod_sanitize_color($color) {
	 if ( empty($color) ) {
		 return '';
	 }
	 
	 // check for rgba color

	 if ( strpos ($color,'rgba') !== false) {
		 $color = str_replace(' ', '', $color);
		 sscanf ( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha);
		 return 'rgba('.$red.','.$green.','.$blue.','.$alpha.')';
	 }
	 
	 // else must be hex color so use wordpress sanatize module
	 
	 return sanitize_hex_color($color);
 }
 
 /**
 * Prepare a proper icon classname from user's custom input
 *
 * @param string $icon_class
 *
 * @return string
 */
function bod_prepare_icon_class( $icon_class ) {
	if ( substr( $icon_class, 0, 3 ) != 'fa-' ) {
		$icon_class = 'fa-' . $icon_class;
	}

	return 'fa ' . $icon_class;
}
 
 
/**
 * Prepare a proper inline-css string from given css proper
 *
 * @param array $props
 * @param bool $style_attr
 *
 * @return string
 */
function bod_prepare_inline_css( $props, $style_attr = TRUE ) {
	$result = '';
	foreach ( $props as $prop => $value ) {
		if ( empty( $value ) ) {
			continue;
		}
		switch ( $prop ) {
			// Properties that can be set either in percents or in pixels
			case 'width':
			case 'padding':
			case 'font-size':
				if ( is_string( $value ) AND strpos( $value, '%' ) !== FALSE ) {
					$result .= $prop . ':' . floatval( $value ) . '%;';
				} else if ( is_string( $value ) AND strpos( $value, 'em' ) !== FALSE ) {
					$result .= $prop . ':' . floatval( $value ) . 'em;';
				} else if ( is_string( $value ) AND strpos( $value, 'rem' ) !== FALSE ) {
					$result .= $prop . ':' . floatval( $value ) . 'rem;';
				} else {
					$result .= $prop . ':' . intval( $value ) . 'px;';
				}
				break;
			// Properties that can be set only in pixels
			case 'height':
			case 'line-height':
			case 'border-width':
			case 'border-radius':
				$result .= $prop . ':' . intval( $value ) . 'px;';
				break;
			// Properties that need vendor prefixes
			case 'transition-duration':
				if ( ! preg_match( '~^(\d+ms)|(\d{0,2}(\.\d+)?s)$~', $value ) ) {
					$value = ( ( strpos( $value, '.' ) !== FALSE ) ? intval( ( floatval( $value ) * 1000 ) ) : intval( $value ) ) . 'ms';
				}
				$result .= '-webkit-' . $prop . ':' . $value . ';' . $prop . ':' . $value . ';';
				break;
			// Properties with image values
			case 'background-image':
				if ( is_numeric( $value ) ) {
					$image = wp_get_attachment_image_src( $value, 'full' );
					if ( $image ) {
						$result .= $prop . ':url("' . $image[0] . '");';
					}
				} else {
					$result .= $prop . ':url("' . $value . '");';
				}
				break;
			// All other properties
			default:
				$result .= $prop . ':' . $value . ';';
				break;
		}
	}
	if ( $style_attr AND ! empty( $result ) ) {
		$result = ' style="' . esc_attr( $result ) . '"';
	}

	return $result;
}

/**
 * Parse hex color value and return red, green and blue integer values in a single array
 *
 * @param string $hex
 *
 * @return array
 */
function bod_hex_to_rgb( $hex ) {
	$hex = preg_replace( '~[^0-9a-f]+~', '', $hex );
	if ( strlen( $hex ) == 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( strlen( $hex ) != 6 ) {
		return array( 255, 255, 255 );
	}

	return array( hexdec( $hex[0] . $hex[1] ), hexdec( $hex[2] . $hex[3] ), hexdec( $hex[4] . $hex[5] ) );
}

/**
 * Get hex form of rgb color values
 *
 * @param array $rgb Red, green and blue integer values within a single array
 *
 * @return string
 */
function bod_rgb_to_hex( $rgb ) {
	return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
}


/**
 * Load WordPress TinyMCE wysiwyg editor configuration
 * The configration will be available in JavaScript: tinyMCEPreInit.mceInit['codelights']
 */
function bod_maybe_load_wysiwyg() {
	global $bod_html_editor_loaded;
	if ( ! isset( $bod_html_editor_loaded ) OR ! $bod_html_editor_loaded ) {
		$screen = get_current_screen();
		if ( $screen !== NULL AND $screen->base == 'customize' ) {
			bod_load_wysiwyg();
		} else {
			// Support for 3-rd party plugins that customize mce_buttons during the admin_head action
			add_action( 'admin_head', 'bod_load_wysiwyg', 50 );
		}
		$bod_html_editor_loaded = TRUE;
	}
}

function bod_load_wysiwyg() {
	if ( ! class_exists( '_WP_Editors' ) ) {
		require( ABSPATH . WPINC . '/class-wp-editor.php' );
	}
	_WP_Editors::editor_settings( 'bodWidgets', _WP_Editors::parse_settings( 'content', array(
		'dfw' => TRUE,
		'tabfocus_elements' => 'insert-media-button',
		'editor_height' => 360,
	) ) );
}

/**
 * Transform some variable to elm's onclick attribute, so it could be obtained from JavaScript as:
 * var data = elm.onclick()
 *
 * @param mixed $data Data to pass
 *
 * @return string Element attribute ' onclick="..."'
 */
function bod_pass_data_to_js( $data ) {
	return ' onclick=\'return ' . str_replace( "'", '&#39;', json_encode( $data ) ) . '\'';
}
