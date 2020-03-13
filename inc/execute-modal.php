<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// Main code for modal popup
//
// Params :
//
// $align - string - trigger button / image / text alignment 
// $el_class - string - user defined class name to assign to modal 
// $show_on - string - show on btn, image, text, selector (class), load (windows load) 
// $image - string - image on which to trigger modal
// $image_size - string - image size to use (loaded from wordpress umage sizes) 
// $btn_label - string - text to use on button
// $text_size - string - text size for btn text in px, em, % or rem units
// $text_color - string - link text color for where $show_on is text
// $show_delay - int - delay in seconds for $show_on load trigger			
// $trigger_selector - string - class on whoch to place trigger
// $btn_bgcolor - string - background color for trigger button
// $btn_color - string - color of button text 
// $size - string - modal content text size in px, em, % or rem units
// $paddings - string - remove white space around modal content 	
// $animation - string - type of animation to use for modal popup transition 
// $overlay_bgcolor - string - background color for modal popup, i.e. color overlaying original window content 
// $border_radius - int - modal border radius in px	
// $title - string - modal title appears above modal content 	
// $title_bgcolor - string - background color for title bar 
// $title_textcolor - string
// $content_bgcolor string
// $content_textcolor - string
// $content - string - content to display in modal popup
	
	
	// Main element classes
	
	$classes = ' align_' . $align;
	if ( ! empty( $el_class ) ) {
		$classes .= ' ' . esc_attr($el_class);
	}
	
	$output = '<div class="bod-popup' . $classes . '">';


	// Trigger


	if ( $show_on == 'image' AND ! empty( $image ) AND ( $image_html = wp_get_attachment_image( $image, $image_size ) ) ) {

		$output .= '<a href="javascript:void(0)" class="bod-popup-trigger type_image">' . $image_html . '</a>';

	} elseif ( $show_on == 'text' ) {

		$output .= '<a href="javascript:void(0)" class="bod-popup-trigger type_text"';
		$output .= bod_prepare_inline_css( array(
			'font-size' => $text_size,
			'color' => $text_color,
		) );
		$output .= '>' . $btn_label . '</a>';
		
	} elseif ( $show_on == 'load' ) {

		$output .= '<span class="bod-popup-trigger type_load" data-delay="'.intval($show_delay).'"></span>';
	
	} elseif ( $show_on == 'selector' ) {
		
		$output .= '<span class="bod-popup-trigger type_selector" data-selector="'.esc_attr($trigger_selector).'"></span>';
		
	} else/*if ( $show_on == 'btn' )*/ {
		

		$output .= '<a href="javascript:void(0)" class="bod-popup-trigger type_btn bod-btn"';
		$output .= bod_prepare_inline_css( array(
			'color' => $btn_color,
			'background-color' => $btn_bgcolor,
		) );
		$output .= '><span>' . $btn_label . '</span></a>';
		
	}		
	

	// Overlay

	$output .= '<div class="bod-popup-overlay"';
	$output .= bod_prepare_inline_css( array(
		'background-color' => $overlay_bgcolor,
	) );
	$output .= '></div>';
	
	
	// The part that will be shown
	$output .= '<div class="bod-popup-wrap';
	if ( ! empty( $el_class ) ) {
		$output .= ' ' .  esc_attr($el_class);
	}
	$output .= '">';
	
	$box_classes = ' size_' . esc_attr($size) . ' animation_' . esc_attr($animation);
	if ( $paddings == 'none' ) {
		$box_classes .= ' paddings_none';
	}
	
	$output .= '<div class="bod-popup-box' . $box_classes . '"';
	
	$output .= bod_prepare_inline_css( array(
		'border-radius' => $border_radius,
	) );
	
	$output .= '><div class="bod-popup-box-h">';


	// Modal box title

	if ( ! empty( $title ) ) {
		$output .= '<div class="bod-popup-box-title"';
		$output .= bod_prepare_inline_css( array(
			'color' => $title_textcolor,
			'background-color' => $title_bgcolor,
		) );
		$output .= '>' . esc_html($title) . '</div>';
	}

	// Modal box content

	$output .= '<div class="bod-popup-box-content"';
	$output .= bod_prepare_inline_css( array(
		'color' => $content_textcolor,
		'background-color' => $content_bgcolor,
	) );
	
	// do_shortcode extracts and executes any embeded shortcodes in the content 
	$output .= '>' . do_shortcode( $content ) . '</div>'; // .bod-popup-box-content
	
	$output .= '<div class="bod-popup-box-closer"';
	if ( ! empty( $title ) ) {
		$output .= bod_prepare_inline_css( array(
			'color' => $title_textcolor,
		) );
	}
	$output .= '></div>'; // .bod-popup-box-closer
	
	$output .= '</div></div>'; // .bod-popup-box-h .bod-popup-box
	
	$output .= '<div class="bod-popup-closer"></div>';
	$output .= '</div>'; // .bod-popup-wrap

	$output .= '</div>'; // end bod-popup

	
	echo $output;
					

?>