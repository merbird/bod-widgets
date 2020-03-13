<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// include helper functions

require_once (plugin_dir_path (__FILE__) . 'helpers.php');

// ************************************************************* 
// ****
// Create a shortcode for modal popup
// ****
// *************************************************************

class bod_modal_shortcode{
	
	//on initialize
	public function __construct(){
		add_action('init', array($this,'register_bod_modal_shortcode')); //shortcode
		add_action( 'wp_enqueue_scripts', array($this,'bod_modal_register_shortcode_scripts') );
	}


	//create shortcode
	public function register_bod_modal_shortcode(){
		add_shortcode('bod-modal', array($this,'bod_modal_output'));
	}
	
	// register all scripts needed for shortcode
	public function bod_modal_register_shortcode_scripts() {	
		include (plugin_dir_path (__FILE__) . 'bod-modal-register-scripts.php');
	}

	  
	// *********************  
	// execute shortcode
	// *********************
	  
	public function bod_modal_output( $atts, $content = '', $tag ) {
		
		// enqueue required assets
		
		wp_enqueue_style('bod-modal-style');
		wp_enqueue_script('bod-core-script');
		wp_enqueue_script('bod-modal-script');
					
				
		// shortcode_atts acts like a merge takes values from $atts and merges with first array but only for keys in 1st array. extract then takes the array reszult and creates individual variables  
		
		extract ( shortcode_atts(array(
			'popup_title' => '',
			'show_on' => 'btn',
			'btn_label' => 'READ MORE',
			'btn_bgcolor' => '',
			'btn_color' => '',
			'image' => '',
			'image_size' => 'large',
			'text_size' => '',
			'text_color' => '',
			'align' => 'left',
			'trigger_selector' => '.my-element',
			'show_delay' => 2,
			'size' => '',
			'paddings' => 'default',
			'animation' => 'fadeIn',
			'border_radius' => 0,
			'title_bgcolor' => '#f2f2f2',
			'overlay_bgcolor' => 'rgba(0,0,0,0.75)',
			'title_textcolor' => '#666666',
			'content_bgcolor' => '#ffffff',
			'content_textcolor' => '#333333',
			'el_class' => '',)
		,$atts,$tag));	
		
		// Sanitize the data
		
		$title = sanitize_text_field( $popup_title );
		$content = wp_kses_post( $content);
		$show_on = sanitize_text_field( $show_on );
		$btn_label = sanitize_text_field( $btn_label );
		$btn_bgcolor =  bod_sanitize_color($btn_bgcolor);
		$btn_color =  bod_sanitize_color($btn_color);
		$image = !empty($image) ? absint( $image ) : '';
		$image_size = sanitize_text_field( $image_size );
		$text_size = sanitize_text_field( $text_size );
		$text_color =  bod_sanitize_color($text_color);
		$align = sanitize_text_field( $align );
		$trigger_selector = sanitize_text_field( $trigger_selector );
		$show_delay = sanitize_text_field( $show_delay );
		$size = sanitize_text_field( $size );
		$paddings = sanitize_text_field( $paddings );
		$animation = sanitize_text_field( $animation );
		$border_radius = sanitize_text_field( $border_radius );
		$title_bgcolor =  bod_sanitize_color($title_bgcolor);
		$overlay_bgcolor =  bod_sanitize_color($overlay_bgcolor);
		$title_textcolor =  bod_sanitize_color($title_textcolor);
		$content_bgcolor =  bod_sanitize_color($content_bgcolor);
		$content_textcolor =  bod_sanitize_color($content_textcolor);
		$el_class = sanitize_html_class( $el_class );
	

		// get main code to display modal 
		// output buffer stops echo's and problem with content always appearing at stop of page
		 
		ob_start(); // use output buffer to stop echo to screen
		include (plugin_dir_path (__FILE__) . 'execute-modal.php');
		return ob_get_clean(); // return the buffer contents

	} // end bod_modal_output
	
} // end class

$bod_modal_shortcode = new bod_modal_shortcode;
?>