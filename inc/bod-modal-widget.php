<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// include helper functions

require_once (plugin_dir_path (__FILE__) . 'helpers.php');
 
// ************************************************************* 
// ****
// Create a widget which provides a modal popup
// ****
// *************************************************************


function bod_modal_register_scripts() {	
	include (plugin_dir_path (__FILE__) . 'bod-modal-register-scripts.php');
}

add_action( 'wp_enqueue_scripts', 'bod_modal_register_scripts' );

function bod_modal_register_admin_scripts() {	

    wp_register_style( 'bod-admin-style',
        plugin_dir_url (__DIR__) . 'css/bod-admin.css',
		array( 'wp-color-picker' ),
		'1.0'		
    );
	
    wp_register_script( 'bod-admin-script',
        plugin_dir_url (__DIR__) .  'js/bod-admin.js',
		array('jquery' , 'wp-color-picker'),
		'1.0.8'
    );
}

add_action( 'admin_enqueue_scripts', 'bod_modal_register_admin_scripts' );
 
 
// Widget Class  
 
class bod_modal_widget extends WP_Widget {
	
	public function __construct() {
		$widget_options = array( 
		  'classname' => 'bod_modal_widget',
		  'description' => 'This is a Modal Popup Widget',
		);
		parent::__construct( 'bod-modal-widget', 'Bod Modal Widget', $widget_options );
	}
	  
	  
	// *********************  
	// execute widget code
	// *********************
	  
	public function widget( $args, $instance ) {
		
		// enqueue required assets
		
		wp_enqueue_style('bod-modal-style');
		wp_enqueue_script('bod-core-script');
		wp_enqueue_script('bod-modal-script');
					
	  	
		// get all the variables we will need 
		
		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : '';
		$align = ! empty( $instance['align'] ) ? $instance['align'] : 'left';
		$el_class = ! empty( $instance['el_class'] ) ? $instance['el_class'] : ''; 
		$show_on = !empty( $instance['show_on'] ) ? $instance['show_on'] : 'btn'; 
		$image = !empty( $instance['image'] ) ? $instance['image'] : ''; 
		$image_size = !empty( $instance['image_size'] ) ? $instance['image_size'] : 'large'; 
		$btn_label = !empty( $instance['btn_label'] ) ? $instance['btn_label'] :  __('READ MORE', 'bod_modal'); 
		$text_size = !empty( $instance['text_size'] ) ? $instance['text_size'] : ''; 
		$text_color = !empty( $instance['text_color'] ) ? $instance['text_color'] : ''; 
		$show_delay = !empty( $instance['show_delay'] ) ? $instance['show_delay'] : '2'; 			
		$trigger_selector = ! empty( $instance['trigger_selector'] ) ? $instance['trigger_selector'] : '.my-element';
		$btn_bgcolor = !empty( $instance['btn_bgcolor'] ) ? $instance['btn_bgcolor'] : ''; 
		$btn_color = !empty( $instance['btn_color'] ) ? $instance['btn_color'] : ''; 
		$size = ! empty( $instance['size'] ) ? $instance['size'] : ''; 
        $paddings = ! empty( $instance['paddings'] ) ? $instance['paddings'] : 'default'; 		
		$animation = ! empty( $instance['animation'] ) ? $instance['animation'] : '';
		$overlay_bgcolor = ! empty( $instance['overlay_bgcolor'] ) ? $instance['overlay_bgcolor'] : 'rgba(0,0,0,0.75)';
		$border_radius = ! empty( $instance['border_radius'] ) ? $instance['border_radius'] : '0'; 		
		$title = ! empty( $instance['popup_title'] ) ? $instance['popup_title'] : ''; 		
		$title_bgcolor = ! empty( $instance['title_bgcolor'] ) ? $instance['title_bgcolor'] : '#f2f2f2';
		$title_textcolor = ! empty( $instance['title_textcolor'] ) ? $instance['title_textcolor'] : '#666666';
		$content_bgcolor = ! empty( $instance['content_bgcolor'] ) ? $instance['content_bgcolor'] : '#ffffff';
		$content_textcolor = ! empty( $instance['content_textcolor'] ) ? $instance['content_textcolor'] : '#333333';
		$content = ! empty( $instance['content'] ) ? $instance['content'] : ''; 		
	
		echo $args['before_widget']; 
	
		// Widget Title
				
		if (!empty($instance[ 'widget_title' ])) {
			$title = apply_filters( 'widget_title', $instance[ 'widget_title' ] );
			echo $args['before_title'] . esc_html($title) . $args['after_title']; 
		}

		// get main code to display modal 

		include (plugin_dir_path (__FILE__) . 'execute-modal.php');
	
		echo $args['after_widget'];

	}
	
	
	// *************************************
	// form to allow user defined parameters
	// ************************************* 
	
	public function form( $instance ) {
		
		wp_enqueue_style('bod-modal-admin-style');
		wp_enqueue_script( 'wplink' );	
		wp_enqueue_script('bod-modal-admin-script');
		wp_enqueue_media();

		
		// get the fields if they exist
		
		// General
		
		$widget_title = ! empty( $instance['widget_title'] ) ? $instance['widget_title'] : ''; 		
		$popup_title = ! empty( $instance['popup_title'] ) ? $instance['popup_title'] : ''; 		
		$content = ! empty( $instance['content'] ) ? $instance['content'] : ''; 		
		
		
		// Trigger
		
		$show_on = !empty( $instance['show_on'] ) ? $instance['show_on'] : 'btn'; 
		$btn_label = !empty( $instance['btn_label'] ) ? $instance['btn_label'] :  __('READ MORE', 'bod_modal'); 
		$btn_bgcolor = !empty( $instance['btn_bgcolor'] ) ? $instance['btn_bgcolor'] : ''; 
		$btn_color = !empty( $instance['btn_color'] ) ? $instance['btn_color'] : ''; 
		$image = !empty( $instance['image'] ) ? $instance['image'] : ''; 
		$image_size = !empty( $instance['image_size'] ) ? $instance['image_size'] : 'large'; 
		$text_size = !empty( $instance['text_size'] ) ? $instance['text_size'] : ''; 
		$text_color = !empty( $instance['text_color'] ) ? $instance['text_color'] : ''; 
		$align = ! empty( $instance['align'] ) ? $instance['align'] : 'left';
		$trigger_selector = ! empty( $instance['trigger_selector'] ) ? $instance['trigger_selector'] : '.my-element';
		$show_delay = !empty( $instance['show_delay'] ) ? $instance['show_delay'] : '2'; 

		
		// Style
								
		$size = ! empty( $instance['size'] ) ? $instance['size'] : ''; 
        $paddings = ! empty( $instance['paddings'] ) ? $instance['paddings'] : 'default'; 		
		$animation = ! empty( $instance['animation'] ) ? $instance['animation'] : '';
        $border_radius = ! empty( $instance['border_radius'] ) ? $instance['border_radius'] : '0'; 		
		$overlay_bgcolor = ! empty( $instance['overlay_bgcolor'] ) ? $instance['overlay_bgcolor'] : 'rgba(0,0,0,0.75)';
		$title_bgcolor = ! empty( $instance['title_bgcolor'] ) ? $instance['title_bgcolor'] : '#f2f2f2';
		$title_textcolor = ! empty( $instance['title_textcolor'] ) ? $instance['title_textcolor'] : '#666666';
		$content_bgcolor = ! empty( $instance['content_bgcolor'] ) ? $instance['content_bgcolor'] : '#ffffff';
		$content_textcolor = ! empty( $instance['content_textcolor'] ) ? $instance['content_textcolor'] : '#333333';
		$el_class = ! empty( $instance['el_class'] ) ? $instance['el_class'] : ''; 
		
// start formatting the output		
	
		$output = '<div class="bod-eform for_bodmodal"><div class="bod-eform-h">';
		
// output group tabs

		$output .= '<div class="bod-tabs">';		
		$output .= '<div class="bod-tabs-list">';		
		$output .= '<div id="1" class="bod-tabs-item active">' . __('General' , 'bod-modal') . '</div>';		
		$output .= '<div id="2" class="bod-tabs-item">' . __('Trigger' , 'bod-modal') . '</div>';		
		$output .= '<div id="3" class="bod-tabs-item">' . __('Style' , 'bod-modal') . '</div>';		
		$output .= '</div>';
		
// tabs sections

		$output .= '<div class="bod-tabs-sections">';	
		
		// tab section
		
		$output .= '<div class="bod-tabs-section" style="display: block">';		
		$output .= '<div class="bod-tabs-section-h">';	
		echo $output;	
		
		// Widget Title
		
		$output = '<div class="bod-eform-row type_textfield for_widget_title">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'widget_title' ) . '">' . __('Widget Title' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('widget_title') . '" id="' . $this->get_field_id( 'widget_title' ) . '" value="'. esc_attr( $widget_title ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;
		
		
		// Popup / Moddal Title
		
		$output = '<div class="bod-eform-row type_textfield for_popup_title">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'popup_title' ) . '">' . __('Popup Title' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('popup_title') . '" id="' . $this->get_field_id( 'popup_title' ) . '" value="'. esc_attr( $popup_title ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;		
		
		
		// Popup / Modal Content
				
		$output = '<div class="bod-eform-row type_textarea for_content">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'content' ) . '">' . __('Popup Content' , 'bod-flipbox') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<textarea name="' . $this->get_field_name( 'content' ) . '" id="' . $this->get_field_id( 'content' ) . '">' . esc_textarea( $content ) . '</textarea>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;

						
		// end tab section
		
		$output = '</div></div>'; // bod-tabs-section and 	bod-tabs-section-h



		// *******************************
		// Trigger
		// tab section 
		
		$output .= '<div class="bod-tabs-section" style="display: none">';		
		$output .= '<div class="bod-tabs-section-h">';	
		echo $output;	
		
		
		// Show On
		
		$output = '<div class="bod-eform-row type_select for_show_on">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'show_on' ) . '">' . __('Show Popup On' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<select name="' . $this->get_field_name('show_on') . '" id="' . $this->get_field_id( 'show_on' ) . ' ">';
		$output .= '<option value="btn"' . selected( $show_on, 'btn', FALSE ) . '>' . __('Button Click' , 'bod-modal') . '</option>';
		$output .= '<option value="text"' . selected( $show_on, 'text', FALSE ) . '>' . __('Text Click' , 'bod-modal') . '</option>';
		$output .= '<option value="image"' . selected( $show_on, 'image', FALSE ) . '>' . __('Image Click' , 'bod-modal') . '</option>';
		$output .= '<option value="selector"' . selected( $show_on, 'selector', FALSE ) . '>' . __('Custom Element Click' , 'bod-modal') . '</option>';
		$output .= '<option value="load"' . selected( $show_on, 'load', FALSE ) . '>' . __('Page Load' , 'bod-modal') . '</option>';

		$output .= '</select>';		
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;
				

		// Button Label
		
		$output = '<div class="bod-eform-row type_textfield for_btn_label">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'btn_label' ) . '">' . __('Button / Text Label' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('btn_label') . '" id="' . $this->get_field_id( 'btn_label' ) . '" value="'. esc_attr( $btn_label ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	

					
		// Button Background Color
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_btn_bgcolor">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'btn_bgcolor' ) . '">' . __('Button Background Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'btn_bgcolor' ) . '" type="text" name="' . $this->get_field_name( 'btn_bgcolor' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $btn_bgcolor ) . '" value="' . esc_attr( $btn_bgcolor ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
					
		
		// Button Color
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_btn_color">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'btn_color' ) . '">' . __('Button Text Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'btn_color' ) . '" type="text" name="' . $this->get_field_name( 'btn_color' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $btn_color ) . '" value="' . esc_attr( $btn_color ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
				
		
		// Image
		
		$output = '<div class="bod-eform-row type_images bod_col-sm-6 bod_column for_image">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'image' ) . '">' . __('Image' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';


		$output .= '<div class="bod-imgattach" data-multiple="FALSE">';
		$output .= '<ul class="bod-imgattach-list">';
		
		$image == '' ? $style = ' style="display:none" ' : $style = ' style="" ';
		$output .= '<li data-id="' . $image . '"' . $style . '><a href="javascript:void(0)" class="bod-imgattach-delete">&times;</a>' . wp_get_attachment_image( $image, 'thumbnail', TRUE ) . '</li>';
		$output .= '</ul>';
		
		$add_btn_title = __( 'Add image', 'bod-modal' );
		$output .= '<a href="javascript:void(0)" class="bod-imgattach-add" title="' . $add_btn_title . '">+</a>';
		$output .= '<input class="bod-image-details" type="hidden" name="' . $this->get_field_name( 'image' ) . '" value="' . esc_attr( $image ) . '" />';
		$output .= '</div>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;
		

		// Image Size
		
		$output = '<div class="bod-eform-row type_select  bod_col-sm-6 bod_column for_image_size">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'image_size' ) . '">' . __('Image Size' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<select name="' . $this->get_field_name('image_size') . '" id="' . $this->get_field_id( 'image_size' ) . ' ">';
		$image_sizes = bod_image_sizes_select_values(); // get the image sizes to display
		foreach ($image_sizes as $image_name => $image_desc) {
			$output .= '<option value="' . $image_name . '"' . selected( $image_size, $image_name, FALSE ) . '>' . __($image_desc , 'bod-modal') . '</option>';
		}		
		$output .= '</select>';		
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;
		

		// Text Size
		
		$output = '<div class="bod-eform-row type_textfield for_text_size">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'text_size' ) . '">' . __('Text Size' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('text_size') . '" id="' . $this->get_field_id( 'text_size' ) . '" value="'. esc_attr( $text_size ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '<div class="bod-eform-row-description">' . __('px, %, em or rem' , 'bod-modal') . '</div>';

		$output .= '</div>';	// eform row
		echo $output;	
		
						
		// Text Color
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_text_color">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'text_color' ) . '">' . __('Text Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'text_color' ) . '" type="text" name="' . $this->get_field_name( 'text_color' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $text_color ) . '" value="' . esc_attr( $text_color ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
		
				
		// Align
		
		$output = '<div class="bod-eform-row type_select for_align">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'align' ) . '">' . __('Button / Image / Text Alignment' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<select name="' . $this->get_field_name('align') . '" id="' . $this->get_field_id( 'align' ) . ' ">';
		$output .= '<option value="left"' . selected( $align, 'left', FALSE ) . '>' . __('Left' , 'bod-modal') . '</option>';
		$output .= '<option value="center"' . selected( $align, 'center', FALSE ) . '>' . __('Center' , 'bod-modal') . '</option>';
		$output .= '<option value="right"' . selected( $align, 'right', FALSE ) . '>' . __('Right' , 'bod-modal') . '</option>';

		$output .= '</select>';		
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;
		

		// Trigger Selector
		
		$output = '<div class="bod-eform-row type_textfield for_trigger_selector">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'trigger_selector' ) . '">' . __('Custom Element CSS Selector' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('trigger_selector') . '" id="' . $this->get_field_id( 'trigger_selector' ) . '" value="'. esc_attr( $trigger_selector ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;					
		

		// Show Delay
		
		$output = '<div class="bod-eform-row type_textfield for_show_delay">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'show_delay' ) . '">' . __('Popup Show Delay' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('show_delay') . '" id="' . $this->get_field_id( 'show_delay' ) . '" value="'. esc_attr( $show_delay ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '<div class="bod-eform-row-description">' . __('delay in seconds.' , 'bod-modal') . '</div>';
		$output .= '</div>';	// eform row
		echo $output;			
		
		
						
		// end tab section
		
		$output = '</div></div>'; // bod-tabs-section and 	bod-tabs-section-h



		// *******************************
		// Style
		// tab section 
		
		$output .= '<div class="bod-tabs-section" style="display: none">';		
		$output .= '<div class="bod-tabs-section-h">';	
		echo $output;	

		
		// Popup Size
		
		$output = '<div class="bod-eform-row type_select for_size">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'size' ) . '">' . __('Popup Size' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<select name="' . $this->get_field_name('size') . '" id="' . $this->get_field_id( 'size' ) . ' ">';
		$output .= '<option value="s"' . selected( $size, 's', FALSE ) . '>' . __('Small' , 'bod-modal') . '</option>';
		$output .= '<option value="m"' . selected( $size, 'm', FALSE ) . '>' . __('Medium' , 'bod-modal') . '</option>';
		$output .= '<option value="l"' . selected( $size, 'l', FALSE ) . '>' . __('Large' , 'bod-modal') . '</option>';
		$output .= '<option value="xl"' . selected( $size, 'xl', FALSE ) . '>' . __('Huge' , 'bod-modal') . '</option>';
		$output .= '<option value="f"' . selected( $size, 'f', FALSE ) . '>' . __('Fullscreen' , 'bod-modal') . '</option>';

		$output .= '</select>';		
		
		$output .= '</div>';	// row field
		$output .= '<div class="bod-eform-row-description">' . __('s:400px, m:600px, l:800px, h:1000px' , 'bod-modal') . '</div>';

		$output .= '</div>';	// eform row
		echo $output;

				
		// Paddings
		
		$output = '<div class="bod-eform-row type_checkboxes for_paddings">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'paddings' ) . '">' . __('Remove White Space' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';		

		$output .= '<label class="bod-checkbox">';
		$output .= '<input type="checkbox" value="none"';
		if ( esc_attr( $paddings ) == 'none' ) {
			$output .= ' checked="checked"';
		}
		$output .= ' /> ' . __('Remove White Space Around Popup Content' , 'bod-modal') . '</label>';

		$output .= '<input type="hidden" name="' . $this->get_field_name( 'paddings' ) . '" value="' . esc_attr( $paddings ) . '" />';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;

		
		// Animation
		
		$output = '<div class="bod-eform-row type_select for_animation">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'popup_style' ) . '">' . __('Appearance Animation' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<select name="' . $this->get_field_name('animation') . '" id="' . $this->get_field_id( 'animation' ) . ' ">';
		$output .= '<option value="fadeIn"' . selected( $animation, 'fadeIn', FALSE ) . '>' . __('Fade In' , 'bod-modal') . '</option>';
		$output .= '<option value="scaleUp"' . selected( $animation, 'scaleUp', FALSE ) . '>' . __('Scale Up' , 'bod-modal') . '</option>';
		$output .= '<option value="scaleDown"' . selected( $animation, 'scaleDown', FALSE ) . '>' . __('Scale Down' , 'bod-modal') . '</option>';
		$output .= '<option value="slideTop"' . selected( $animation, 'slideTop', FALSE ) . '>' . __('Slide from Top' , 'bod-modal') . '</option>';
		$output .= '<option value="slideBottom"' . selected( $animation, 'slideBottom', FALSE ) . '>' . __('Slide from Bottom' , 'bod-modal') . '</option>';
		$output .= '<option value="flipHor"' . selected( $animation, 'flipHor', FALSE ) . '>' . __('3D Flip (Horizontal)' , 'bod-modal') . '</option>';
		$output .= '<option value="flipVer"' . selected( $animation, 'flipVer', FALSE ) . '>' . __('3D Flip (Vertical)' , 'bod-modal') . '</option>';

		$output .= '</select>';		
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;
		

		// Radius
		
		$output = '<div class="bod-eform-row type_textfield for_border_radius">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'border_radius' ) . '">' . __('Corners Radius' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name('border_radius') . '" id="' . $this->get_field_id( 'border_radius' ) . '" value="'. esc_attr( $border_radius ) .'">';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
		
						
		// Overlay Bgcolor
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_overlay_bgcolor">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'overlay_bgcolor' ) . '">' . __('Overlay Backgroung Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'overlay_bgcolor' ) . '" type="text" name="' . $this->get_field_name( 'overlay_bgcolor' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $overlay_bgcolor ) . '" value="' . esc_attr( $overlay_bgcolor ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
		
						
		// Title Bgcolor
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_title_bgcolor">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'title_bgcolor' ) . '">' . __('Title Backgroung Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'title_bgcolor' ) . '" type="text" name="' . $this->get_field_name( 'title_bgcolor' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $title_bgcolor ) . '" value="' . esc_attr( $title_bgcolor ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
		
						
		// Title Text Color
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_title_textcolor">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'title_textcolor' ) . '">' . __('Title Text Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'title_textcolor' ) . '" type="text" name="' . $this->get_field_name( 'title_textcolor' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $title_textcolor ) . '" value="' . esc_attr( $title_textcolor ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;	
		
						
		// Content Bgcolor
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_content_bgcolor">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'content_bgcolor' ) . '">' . __('Content Backgroung Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'content_bgcolor' ) . '" type="text" name="' . $this->get_field_name( 'content_bgcolor' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $content_bgcolor ) . '" value="' . esc_attr( $content_bgcolor ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;			
		
						
		// Content Text Color
		
		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_content_textcolor">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'content_textcolor' ) . '">' . __('Content Text Color' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id( 'content_textcolor' ) . '" type="text" name="' . $this->get_field_name( 'content_textcolor' ) . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr( $content_textcolor ) . '" value="' . esc_attr( $content_textcolor ) . '"/>';
		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;			

		
		// Class
		
		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_el_class">';	
			
		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id( 'el_class' ) . '">' . __('Extra CSS Class' , 'bod-modal') . '</label>';	
		$output .= '</div>';	
		
		$output .= '<div class="bod-eform-row-field">';
		
		$output .= '<input type="text" name="' . $this->get_field_name( 'el_class' ) . '" id="' . $this->get_field_id( 'el_class' ) . '"';
		$output .= ' value="' . esc_attr( $el_class ) . '" />';		

		
		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
			
		echo $output;
		
		// end tab section
		
		$output = '</div></div>'; // bod-tabs-section and 	bod-tabs-section-h
								
		// end tab sections
		
		$output .= '</div></div>'; // bod-tabs-sections and bod-tabs
		
		// end eform
		
		$output .= '</div></div>'; // bod-eform and bod-eform-h
			
		echo $output;
		
	}
	
	
	// *************************
	// update widget paramneters
	// *************************
	
	public function update( $new_instance, $old_instance ) {
  		$instance = $old_instance;
		
		// Sanitize the data
		
		// General
		
		$instance[ 'widget_title' ] = sanitize_text_field( $new_instance[ 'widget_title' ] );
		$instance[ 'popup_title' ] = sanitize_text_field( $new_instance[ 'popup_title' ] );
		$instance[ 'content' ] = wp_kses_post( $new_instance[ 'content' ] );
		
		
		// Trigger
		
		$instance[ 'show_on' ] = sanitize_text_field( $new_instance[ 'show_on' ] );
		$instance[ 'btn_label' ] = sanitize_text_field( $new_instance[ 'btn_label' ] );
		$instance[ 'btn_bgcolor' ] =  bod_sanitize_color($new_instance['btn_bgcolor']);
		$instance[ 'btn_color' ] =  bod_sanitize_color($new_instance['btn_color']);
		$instance[ 'image' ] = !empty($new_instance[ 'image' ]) ? absint( $new_instance[ 'image' ] )  : '';
		$instance[ 'image_size' ] = sanitize_text_field( $new_instance[ 'image_size' ] );
		$instance[ 'text_size' ] = sanitize_text_field( $new_instance[ 'text_size' ] );
		$instance[ 'text_color' ] =  bod_sanitize_color($new_instance['text_color']);
		$instance[ 'align' ] = sanitize_text_field( $new_instance[ 'align' ] );
		$instance[ 'trigger_selector' ] = sanitize_text_field( $new_instance[ 'trigger_selector' ] );
		$instance[ 'show_delay' ] = sanitize_text_field( $new_instance[ 'show_delay' ] );

		
		// Style
		
		$instance[ 'size' ] = sanitize_text_field( $new_instance[ 'size' ] );
		$instance[ 'paddings' ] = sanitize_text_field( $new_instance[ 'paddings' ] );
		$instance[ 'animation' ] = sanitize_text_field( $new_instance[ 'animation' ] );
		$instance[ 'border_radius' ] = sanitize_text_field( $new_instance[ 'border_radius' ] );
		$instance[ 'title_bgcolor' ] =  bod_sanitize_color($new_instance['title_bgcolor']);
		$instance[ 'overlay_bgcolor' ] =  bod_sanitize_color($new_instance['overlay_bgcolor']);
		$instance[ 'title_textcolor' ] =  bod_sanitize_color($new_instance['title_textcolor']);
		$instance[ 'content_bgcolor' ] =  bod_sanitize_color($new_instance['content_bgcolor']);
		$instance[ 'content_textcolor' ] =  bod_sanitize_color($new_instance['content_textcolor']);
		$instance[ 'el_class' ] = sanitize_html_class( $new_instance[ 'el_class' ] );

				
  		return $instance;
	}
	
}
 
function bod_register_modal_widget() { 
  register_widget( 'bod_modal_widget' );
}
add_action( 'widgets_init', 'bod_register_modal_widget' );



?>