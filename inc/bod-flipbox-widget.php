<?php defined('ABSPATH') or die('This script cannot be accessed directly.');
// 2/1/2020 - include wp-color-picker-alpha so we can select rgba colors 
// include helper functions

require_once(plugin_dir_path(__FILE__) . 'helpers.php');

// ************************************************************* 
// ****
// Create a widget which displays a flipbox
// ****
// *************************************************************


function bod_flipbox_register_scripts()
{


	wp_register_style(
		'bod-flipbox-style',
		plugin_dir_url(__DIR__) .  'css/bod-flipbox.css',
		array(),
		'1.0.5'
	);

	wp_register_style(
		'bod-fontawesome',
		plugin_dir_url(__DIR__) .  'lib/fontawesome/css/all.css',
		array(),
		'1.0'
	);

	wp_register_script(
		'bod-core-script',
		plugin_dir_url(__DIR__) .  'js/bod-core.js',
		array('jquery'),
		'1.0.0',
		true
	);

	wp_register_script(
		'bod-flipbox-script',
		plugin_dir_url(__DIR__) .  'js/bod-flipbox.js',
		array('jquery'),
		'1.0.1',
		true
	);
}

add_action('wp_enqueue_scripts', 'bod_flipbox_register_scripts');

function bod_flipbox_register_admin_scripts()
{

	wp_register_style(
		'bod-admin-style',
		plugin_dir_url(__DIR__) . 'css/bod-admin.css',
		array('wp-color-picker'),
		'1.0.1'
	);

	wp_register_script(
		'bod-admin-script',
		plugin_dir_url(__DIR__) .  'js/bod-admin.js',
		array('jquery', 'wp-color-picker'),
		'1.0.8'
	);

	// 2/1/2020 register script to enable rgba color picker

	wp_register_script(
		'wp-color-picker-alpha',
		plugin_dir_url(__DIR__) .  'js/wp-color-picker-alpha.min.js',
		array('wp-color-picker'),
		'1.0.0'
	);
}

add_action('admin_enqueue_scripts', 'bod_flipbox_register_admin_scripts');


// Widget Class  

class bod_flipbox_widget extends WP_Widget
{

	public function __construct()
	{
		$widget_options = array(
			'classname' => 'bod_flipbox_widget',
			'description' => 'This is a Flipbox Widget',
		);
		parent::__construct('bod_flipbox_widget', 'Bod Flipbox Widget', $widget_options);
	}


	// *********************  
	// execute widget code
	// *********************

	public function widget($args, $instance)
	{

		// enqueue required assets

		wp_enqueue_style('bod-flipbox-style');
		wp_enqueue_script('bod-core-script');
		wp_enqueue_script('bod-flipbox-script');


		echo $args['before_widget'];

		// Widget Title

		if (!empty($instance['widget_title'])) {
			$title = apply_filters('widget_title', $instance['widget_title']);
			echo $args['before_title'] . esc_html($title) . $args['after_title'];
		}

		$direction = $instance['direction']; // $direction string Animation direction: 'n' / 'ne' / 'e' / 'se' / 's' / 'sw' / 'w' / 'nw'
		$animation = $instance['animation']; // $animation string Animation type: 'cardflip' / 'cubetilt' / 'cubeflip' / 'coveropen'

		if (in_array($direction, array('ne', 'se', 'sw', 'nw'))) {
			// When rotating cubetilt in diaginal direction, we're actually doing a cube flip animation instead
			if ($animation == 'cubetilt') {
				$animation = 'cubeflip';
			}
		}

		// Main element classes
		$valign = $instance['valign']; // $valign string Vertical align: 'top' / 'center'
		$classes = ' animation_' . $animation . ' direction_' . $direction;
		if (!empty($valign)) {
			$classes .= ' valign_' . $valign;
		}

		$full_height = !empty($instance['full_height']) ? $instance['full_height'] : 'no';; // $valign string full height yes / no
		if ($full_height == 'yes') {
			$classes .= ' bod-full-h';
		}

		// deal with the link if we have one (container or button)

		$tag = 'div';
		$atts = '';
		$link_type = $instance['link_type']; // $link_type string Link type: 'none' / 'container' / 'btn'
		$link = $instance['link']; // $link string URL of the overall flipbox anchor in a encoded link format
		if ($link_type != 'none' and !empty($link)) {
			$link_atts = bod_parse_link_value($link, TRUE);
			// Altering the whole element's div with anchor when it has a link
			if ($link_type == 'container') {
				$tag = 'a';
				$atts .= $link_atts;
			}
		}

		// if we have a user defined extra class then add to the classes string
		$el_class = $instance['el_class']; // $el_class string Extra class name
		if (!empty($el_class)) {
			$classes .= ' ' . esc_attr($el_class);
		}

		$width = $instance['width']; // $width string In pixels or percents: '100' / '100%'
		$inline_css = bod_prepare_inline_css(array(
			'width' => $width,
		));

		$output = '<' . $tag . $atts . ' class="bod-flipbox' . $classes . '"' . $inline_css . '>';

		$easing = $instance['easing']; // $easing string Easing CSS class name
		$helper_classes = ' easing_' . esc_attr($easing);

		$duration = $instance['duration']; // $duration string Animation duration in milliseconds
		$helper_inline_css = bod_prepare_inline_css(array(
			'transition-duration' => $duration,
		));

		$output .= '<div class="bod-flipbox-h' . $helper_classes . '"' . $helper_inline_css . '><div class="bod-flipbox-hh">';

		if ($animation == 'cubeflip' and in_array($direction, array('ne', 'se', 'sw', 'nw'))) {
			$output .= '<div class="bod-flipbox-hhh">';
		}

		// ****************
		// Front of flipbox
		// ****************

		$height = $instance['height']; // $height string in px
		$front_bgcolor =  $instance['front_bgcolor']; // $front_bgcolor string hex or rgba
		$border_color =  $instance['border_color']; // $border_color string hex or rgba
		$border_radius =  $instance['border_radius']; // $border_radius string in px
		$border_size =  $instance['border_size']; // $border_size string in px

		$front_inline_css = array(
			'height' => $height,
			'background-color' => $front_bgcolor,
			'border-color' => $border_color,
			'border-radius' => $border_radius,
			'border-width' => $border_size,
		);

		// Output front of flipbox colors, size, radius, height -------------------------------------
		$output .= '<div class="bod-flipbox-front"' . bod_prepare_inline_css($front_inline_css) . '>';


		// check for front image which is now a foreground image rather than background 
		$front_image =  $instance['front_image']; // $front_bgimage int ID of the WP attachment image
		$front_image_size =  $instance['front_image_size']; // $front_bgimage_size string WordPress image thumbnail name
		if (!empty($front_image) and ($front_image_src = wp_get_attachment_image_src($front_image, $front_image_size))) {
			$image_alt = get_post_meta($front_image, '_wp_attachment_image_alt', true);
			$output .= '<img class="bod-flipbox-fill-image" alt="' . $image_alt . '" src="' . $front_image_src[0] . '">';
		}

		// check for text background  color and padding
		$front_text_bgcolor = !empty($instance['front_text_bgcolor']) ? $instance['front_text_bgcolor'] : '';
		$front_text_padding = !empty($instance['front_text_padding']) ? $instance['front_text_padding'] : '10';
		if (!empty($front_text_bgcolor)) {
			$front_inline_css = array(
				'padding' => $front_text_padding,
				'background-color' => $front_text_bgcolor,
			);
			$output .= '<div class="bod-flipbox-front-h"' . bod_prepare_inline_css($front_inline_css) . '>';
		} else {
			$output .= '<div class="bod-flipbox-front-h">';
		}


		$output_front_icon = '';
		$front_icon_type =  $instance['front_icon_type']; // $front_icon_type string Front icon type: 'none' / 'font' / 'image'
		$front_icon_name =  $instance['front_icon_name']; // $front_icon_name string The name of the front icon if present (ex: 'star' / 'fa-star')
		$front_icon_size =  $instance['front_icon_size']; // $front_icon_size int Front icon font size
		$front_icon_color =  $instance['front_icon_color']; // $front_icon_color string
		$front_icon_bgcolor =  $instance['front_icon_bgcolor']; // $front_icon_bgcolor string
		$front_icon_style =  $instance['front_icon_style']; // $front_icon_style string Front icon style type: 'default' / 'circle' / 'square'
		$front_icon_image =  $instance['front_icon_image']; // $front_icon_image int ID of the WP attachment image
		$front_icon_image_width =  $instance['front_icon_image_width']; // @$front_icon_image_width string Image icon width in pixels or percent


		// front icon font

		if ($front_icon_type == 'font' and !empty($front_icon_name)) {

			wp_enqueue_style('bod-fontawesome');
			$front_icon_size = intval($front_icon_size);
			$front_icon_css_props = array(
				'background-color' => $front_icon_bgcolor,
				'color' => $front_icon_color,
			);

			if ($front_icon_style != 'default') {
				$front_icon_css_props['border-color'] = $front_icon_color;
			}

			if (!empty($front_icon_size)) {
				$front_icon_size = intval($front_icon_size);
				$front_icon_boxsize = $front_icon_size * (($front_icon_style == 'default') ? 1 : 2.3);
				$front_icon_css_props += array(
					'width' => $front_icon_boxsize,
					'height' => $front_icon_boxsize,
					'font-size' => $front_icon_size,
					'line-height' => $front_icon_boxsize,
				);
			}

			$output_front_icon .= '<div class="bod-flipbox-front-icon style_' . $front_icon_style . '"' . bod_prepare_inline_css($front_icon_css_props) . '>';
			$output_front_icon .= '<i class="' . esc_html($front_icon_name) . '"></i>';
			$output_front_icon .= '</div>';
		} elseif ($front_icon_type == 'image' and !empty($front_icon_image) and ($front_icon_image_html = wp_get_attachment_image($front_icon_image, 'medium'))) {

			// front icon image 

			$output_front_icon .= '<div class="bod-flipbox-front-image"';
			$output_front_icon .= bod_prepare_inline_css(array(
				'width' => $front_icon_image_width,
			));

			$output_front_icon .= '>' . $front_icon_image_html . '</div>';
		}

		// front title, desc

		$front_title =  $instance['front_title']; // $front_title string
		$front_title_head = !empty($instance['front_title_head']) ? (in_array($instance['front_title_head'], array('H1', 'H2', 'H3', 'H4')) ? $instance['front_title_head'] : 'H3') : 'H3'; // $front_title_head string
		$front_title_size =  $instance['front_title_size']; // $front_title_size string px
		$front_textcolor =  $instance['front_textcolor']; // $front_textcolor string
		$front_desc =  $instance['front_desc']; // $front_desc string
		$front_elmorder =  $instance['front_elmorder']; // $front_elmorder string Elements order: 'itd' / 'tid' / 'tdi' (first letters of: Icon, Title, Description)


		$output_front_title = '';
		if (!empty($front_title)) {
			$output_front_title .= '<' . esc_html($front_title_head) . ' class="bod-flipbox-front-title"';
			$output_front_title .= bod_prepare_inline_css(array(
				'font-size' => $front_title_size,
				'color' => $front_textcolor,
			));
			$output_front_title .= '>' . esc_html($front_title) . '</' . esc_html($front_title_head) . '>';
		}

		$output_front_desc = '';
		if (!empty($front_desc)) {
			$output_front_desc .= '<p class="bod-flipbox-front-desc"';
			$output_front_desc .= bod_prepare_inline_css(array(
				'color' => $front_textcolor,
			));
			$output_front_desc .= '>' . esc_textarea($front_desc) . '</p>';
		}

		// output front icon, title, desc in order 

		if ($front_elmorder == 'tid') {
			$output .= $output_front_title . $output_front_icon . $output_front_desc;
		} elseif ($front_elmorder == 'tdi') {
			$output .= $output_front_title . $output_front_desc . $output_front_icon;
		} else/*if ( $front_elmorder == 'itd' )*/ {
			$output .= $output_front_icon . $output_front_title . $output_front_desc;
		}
		$output .= '</div></div>';


		// *******************
		// Back of flipbox
		// *******************

		$padding = $instance['padding']; // $padding % or px
		$back_bgcolor =  $instance['back_bgcolor']; // $back_bgcolor string hex or rgba
		$back_bgimage =  $instance['back_bgimage']; // $back_bgimage int ID of the WP attachment image
		$back_bgimage_size =  $instance['back_bgimage_size']; // $back_bgimage_size string WordPress image thumbnail name

		$back_inline_css = array(
			'padding' => $padding,
			'background-color' => $back_bgcolor,
			'border-color' => $border_color,
			'border-radius' => $border_radius,
			'border-width' => $border_size,
			'display' => 'none',
		);

		if (!empty($back_bgimage) and ($back_bgimage_src = wp_get_attachment_image_src($back_bgimage, $back_bgimage_size))) {
			$back_inline_css['background-image'] = $back_bgimage_src[0];
		}
		$output .= '<div class="bod-flipbox-back"' . bod_prepare_inline_css($back_inline_css) . '><div class="bod-flipbox-back-h">';

		$back_title =  $instance['back_title']; // $back_bgimage int ID of the WP attachment image
		$back_title_size =  $instance['back_title_size']; // $back_title_size string
		$back_textcolor =  $instance['back_textcolor']; // $back_textcolor string
		$back_desc =  $instance['back_desc']; // $back_desc string Back-side text
		$back_btn_label =  $instance['back_btn_label']; // $back_btn_label string Back button label
		$back_btn_color =  $instance['back_btn_color']; // $back_btn_color string
		$back_btn_bgcolor =  $instance['back_btn_bgcolor']; // $back_btn_bgcolor string Back button background color
		$back_elmorder =  $instance['back_elmorder']; // $back_elmorder string Elements order: 'tdb' / 'tbd' / 'btd' (first letters of: Title, Description, Button)


		$output_back_title = ''; // $back_title string
		if (!empty($back_title)) {
			$output_back_title .= '<h4 class="bod-flipbox-back-title"';
			$output_back_title .= bod_prepare_inline_css(array(
				'font-size' => $back_title_size,
				'color' => $back_textcolor,
			));
			$output_back_title .= '>' . esc_html($back_title) . '</h4>';
		}

		$output_back_desc = '';
		if (!empty($back_desc)) {
			$output_back_desc .= '<p class="bod-flipbox-back-desc"';
			$output_back_desc .= bod_prepare_inline_css(array(
				'color' => $back_textcolor,
			));
			$output_back_desc .= '>' . esc_textarea($back_desc) . '</p>';
		}

		$output_back_btn = '';
		if ($link_type == 'btn' and isset($link_atts) and !empty($back_btn_label)) {
			$back_btn_inline_css = bod_prepare_inline_css(array(
				'color' => $back_btn_color,
				'background-color' => $back_btn_bgcolor,
			));
			$output_back_btn .= '<a class="bod-btn"' . $back_btn_inline_css . $link_atts . '><span>' . $back_btn_label . '</span></a>';
		}

		if ($back_elmorder == 'tbd') {
			$output .= $output_back_title . $output_back_btn . $output_back_desc;
		} elseif ($back_elmorder == 'btd') {
			$output .= $output_back_btn . $output_back_title . $output_back_desc;
		} else/*if ( $back_elmorder == 'tdb' )*/ {
			$output .= $output_back_title . $output_back_desc . $output_back_btn;
		}
		$output .= '</div></div>';

		// cubeflip

		if ($animation == 'cubeflip') {
			// Counting flanks color
			$flank_inline_css_props = array(
				'border-color' => $border_color,
				'border-radius' => $border_radius,
				'border-width' => $border_size,
			);
			// We need some additional dom-elements for some of the animations (:before / :after won't suit)
			if (in_array($direction, array('ne', 'e', 'se', 'sw', 'w', 'nw'))) {
				// Top / bottom side flank
				$front_bgcolor = empty($front_bgcolor) ? '#eeeeee' : $front_bgcolor;
				$front_rgb = bod_hex_to_rgb($front_bgcolor);
				for ($i = 0; $i < 3; $i++) {
					$front_rgb[$i] = min(250, $front_rgb[$i] + 20);
				}
				$flank_inline_css_props['background-color'] = bod_rgb_to_hex($front_rgb);
				$output .= '<div class="bod-flipbox-yflank"' . bod_prepare_inline_css($flank_inline_css_props) . '></div>';
			}
			if (in_array($direction, array('n', 'ne', 'se', 's', 'sw', 'nw'))) {
				// Left / right side flank
				$front_bgcolor = empty($front_bgcolor) ? '#eeeeee' : $front_bgcolor;
				$front_rgb = bod_hex_to_rgb($front_bgcolor);
				for ($i = 0; $i < 3; $i++) {
					$front_rgb[$i] = max(5, $front_rgb[$i] - 20);
				}
				$flank_inline_css_props['background-color'] = bod_rgb_to_hex($front_rgb);
				$output .= '<div class="bod-flipbox-xflank"' . bod_prepare_inline_css($flank_inline_css_props) . '></div>';
			}
		}

		if ($animation == 'cubeflip' and in_array($direction, array('ne', 'se', 'sw', 'nw'))) {
			$output .= '</div>';
		}
		$output .= '</div></div>';
		$output .= '</' . $tag . '>';

		echo $output;

		echo $args['after_widget'];
	}


	// *************************************
	// form to allow user defined parameters
	// ************************************* 

	public function form($instance)
	{

		wp_enqueue_style('bod-admin-style');
		wp_enqueue_script('wplink');
		wp_enqueue_script('bod-admin-script');
		wp_enqueue_media();

		// 2/1/2020 include rgba color picker
		wp_enqueue_script('wp-color-picker-alpha');


		// get the fields if they exist

		// General

		$widget_title = !empty($instance['widget_title']) ? $instance['widget_title'] : '';
		$link_type = !empty($instance['link_type']) ? $instance['link_type'] : 'None';
		$link = !empty($instance['link']) ? $instance['link'] : '';
		$back_btn_label = !empty($instance['back_btn_label']) ? $instance['back_btn_label'] : __('READ MORE', 'bod_flipbox');
		$back_btn_bgcolor = !empty($instance['back_btn_bgcolor']) ? $instance['back_btn_bgcolor'] : '';
		$back_btn_color = !empty($instance['back_btn_color']) ? $instance['back_btn_color'] : '';
		$animation = !empty($instance['animation']) ? $instance['animation'] : '';
		$direction = !empty($instance['direction']) ? $instance['direction'] : 'w';
		$duration = !empty($instance['duration']) ? $instance['duration'] : '500';
		$easing = !empty($instance['easing']) ? $instance['easing'] : 'ease';

		// Front

		$front_icon_type = !empty($instance['front_icon_type']) ? $instance['front_icon_type'] : 'none';
		$front_icon_name = !empty($instance['front_icon_name']) ? $instance['front_icon_name'] : '';
		$front_icon_size = !empty($instance['front_icon_size']) ? $instance['front_icon_size'] : '35';
		$front_icon_style = !empty($instance['front_icon_style']) ? $instance['front_icon_style'] : 'default';
		$front_icon_color = !empty($instance['front_icon_color']) ? $instance['front_icon_color'] : '';
		$front_icon_bgcolor = !empty($instance['front_icon_bgcolor']) ? $instance['front_icon_bgcolor'] : '';
		$front_icon_image = !empty($instance['front_icon_image']) ? $instance['front_icon_image'] : '';
		$front_icon_image_width = !empty($instance['front_icon_image_width']) ? $instance['front_icon_image_width'] : '32px';
		$front_title = !empty($instance['front_title']) ? $instance['front_title'] :  __('Flipbox Title', 'bod_flipbox');
		$front_title_head = !empty($instance['front_title_head']) ? $instance['front_title_head'] : 'H3';
		$front_title_size = !empty($instance['front_title_size']) ? $instance['front_title_size'] : '';
		$front_desc = !empty($instance['front_desc']) ? $instance['front_desc'] : '';
		$front_elmorder = !empty($instance['front_elmorder']) ? $instance['front_elmorder'] : 'itd';
		$front_bgcolor = !empty($instance['front_bgcolor']) ? $instance['front_bgcolor'] : '';
		$front_textcolor = !empty($instance['front_textcolor']) ? $instance['front_textcolor'] : '';
		$front_text_bgcolor = !empty($instance['front_text_bgcolor']) ? $instance['front_text_bgcolor'] : '';
		$front_text_padding = !empty($instance['front_text_padding']) ? $instance['front_text_padding'] : '10';
		$front_image = !empty($instance['front_image']) ? $instance['front_image'] : '';
		$front_image_size = !empty($instance['front_image_size']) ? $instance['front_image_size'] : 'full';

		// Back

		$back_title = !empty($instance['back_title']) ? $instance['back_title'] :  __('Flipbox Title', 'bod_flipbox');
		$back_title_size = !empty($instance['back_title_size']) ? $instance['back_title_size'] : '';
		$back_desc = !empty($instance['back_desc']) ? $instance['back_desc'] : '';
		$back_elmorder = !empty($instance['back_elmorder']) ? $instance['back_elmorder'] : 'tdb';
		$back_bgcolor = !empty($instance['back_bgcolor']) ? $instance['back_bgcolor'] : '';
		$back_textcolor = !empty($instance['back_textcolor']) ? $instance['back_textcolor'] : '';
		$back_bgimage = !empty($instance['back_bgimage']) ? $instance['back_bgimage'] : '';
		$back_bgimage_size = !empty($instance['back_bgimage_size']) ? $instance['back_bgimage_size'] : 'full';


		// Style

		$width = !empty($instance['width']) ? $instance['width'] : '100%';
		$height = !empty($instance['height']) ? $instance['height'] : '';
		$valign = !empty($instance['valign']) ? $instance['valign'] : 'top';
		$padding = !empty($instance['padding']) ? $instance['padding'] : '15%';
		$full_height = !empty($instance['full_height']) ? $instance['full_height'] : 'no';
		$border_radius = !empty($instance['border_radius']) ? $instance['border_radius'] : '0';
		$border_size = !empty($instance['border_size']) ? $instance['border_size'] : '0';
		$border_color = !empty($instance['border_color']) ? $instance['border_color'] : '';
		$el_class = !empty($instance['el_class']) ? $instance['el_class'] : '';

		// start formatting the output		

		$output = '<div class="bod-eform for_bodflipbox"><div class="bod-eform-h">';

		// output group tabs

		$output .= '<div class="bod-tabs">';
		$output .= '<div class="bod-tabs-list">';
		$output .= '<div id="1" class="bod-tabs-item active">' . __('General', 'bod-flipbox') . '</div>';
		$output .= '<div id="2" class="bod-tabs-item">' . __('Front', 'bod-flipbox') . '</div>';
		$output .= '<div id="3" class="bod-tabs-item">' . __('Back', 'bod-flipbox') . '</div>';
		$output .= '<div id="4" class="bod-tabs-item">' . __('Style', 'bod-flipbox') . '</div>';
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
		$output .= '<label for="' . $this->get_field_id('widget-title') . '">' . __('Widget Title', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('widget_title') . '" id="' . $this->get_field_id('widget_title') . '" value="' . esc_attr($widget_title) . '">';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Link Type

		$output = '<div class="bod-eform-row type_select for_link_type">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('link_type') . '">' . __('Link', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('link_type') . '" id="' . $this->get_field_id('link_type') . ' ">';
		$output .= '<option value="none"' . selected($link_type, 'none', FALSE) . '>' . __('None', 'bod-flipbox') . '</option>';
		$output .= '<option value="container"' . selected($link_type, 'container', FALSE) . '>' . __('Add Link to whole Flipbox', 'bod-flipbox') . '</option>';
		$output .= '<option value="btn"' . selected($link_type, 'btn', FALSE) . '>' . __('Add link as a button on the back side', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Link URL

		$output = '<div class="bod-eform-row type_link for_link">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('link') . '">' . __('Link URL', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<div class="bod-linkdialog">';

		// we have 3 fields 1) a button to trigger link dialog 2) a span which displays url 3) full url include text and new window selector
		// we take the full url and extract the url / link text and open in new window selector

		$extractedLink = bod_parse_link_value($link);

		$output .= '<a class="bod-linkdialog-btn button button-default button-large" href="javascript:void(0)">' . __('Insert link', 'bod-flipbox') . '</a>';
		//$output .= '<input class="url-button" type="button" value="Choose URL" />';
		$output .= '<span class="bod-linkdialog-url">' . $extractedLink['url'] . '</span>';
		$output .= '<textarea name="' . $this->get_field_name('link') . '" id="' . $this->get_field_id('link') . '">' . esc_textarea($link) . '</textarea>';

		$output .= '</div>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Back Btn Label

		$output = '<div class="bod-eform-row type_textfield for_back_btn_label">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_btn_label') . '">' . __('Button Label', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('back_btn_label') . '" id="' . $this->get_field_id('back_btn_label') . '"';
		$output .= ' value="' . esc_attr($back_btn_label) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Background Btn Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_back_btn_bgcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_btn_bgcolor') . '">' . __('Button Background Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('back_btn_bgcolor') . '" type="text" name="' . $this->get_field_name('back_btn_bgcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($back_btn_bgcolor) . '" value="' . esc_attr($back_btn_bgcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Btn Text Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_back_btn_color">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_btn_color') . '">' . __('Button Text Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('back_btn_color') . '" type="text" name="' . $this->get_field_name('back_btn_color') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($back_btn_color) . '" value="' . esc_attr($back_btn_color) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Animation Type

		$output = '<div class="bod-eform-row type_select bod_col-sm-6 bod_column for_animation">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('animation') . '">' . __('Animation Type', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('animation') . '" id="' . $this->get_field_id('animation') . ' ">';
		$output .= '<option value="cardflip"' . selected($animation, 'cardflip', FALSE) . '>' . __('Card Flip', 'bod-flipbox') . '</option>';
		$output .= '<option value="cubetilt"' . selected($animation, 'cubetilt', FALSE) . '>' . __('Cube Tilt', 'bod-flipbox') . '</option>';
		$output .= '<option value="cubeflip"' . selected($animation, 'cubeflip', FALSE) . '>' . __('Cube Flip', 'bod-flipbox') . '</option>';
		$output .= '<option value="coveropen"' . selected($animation, 'coveropen', FALSE) . '>' . __('Cover Open', 'bod-flipbox') . '</option>';

		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Animation Direction

		$output = '<div class="bod-eform-row type_select bod_col-sm-6 bod_column for_direction">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('direction') . '">' . __('Animation Direction', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('direction') . '" id="' . $this->get_field_id('direction') . ' ">';
		$output .= '<option value="n"' . selected($direction, 'n', FALSE) . '>' . __('Up', 'bod-flipbox') . '</option>';
		$output .= '<option value="ne"' . selected($direction, 'ne', FALSE) . '>' . __('Up-Right', 'bod-flipbox') . '</option>';
		$output .= '<option value="e"' . selected($direction, 'e', FALSE) . '>' . __('Right', 'bod-flipbox') . '</option>';
		$output .= '<option value="se"' . selected($direction, 'se', FALSE) . '>' . __('Down-Right', 'bod-flipbox') . '</option>';
		$output .= '<option value="s"' . selected($direction, 's', FALSE) . '>' . __('Down', 'bod-flipbox') . '</option>';
		$output .= '<option value="sw"' . selected($direction, 'sw', FALSE) . '>' . __('Down-Left', 'bod-flipbox') . '</option>';
		$output .= '<option value="w"' . selected($direction, 'w', FALSE) . '>' . __('Left', 'bod-flipbox') . '</option>';
		$output .= '<option value="nw"' . selected($direction, 'nw', FALSE) . '>' . __('Up-Left', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Animation Duration

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_duration">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('duration') . '">' . __('Animation Duration', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="number" name="' . $this->get_field_name('duration') . '" id="' . $this->get_field_id('duration') . '"';
		$output .= ' value="' . esc_attr($duration) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Animation Easing

		$output = '<div class="bod-eform-row type_select bod_col-sm-6 bod_column for_easing">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('easing') . '">' . __('Animation Easing', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('easing') . '" id="' . $this->get_field_id('easing') . ' ">';
		$output .= '<option value="ease"' . selected($easing, 'ease', FALSE) . '>' . __('ease', 'bod-flipbox') . '</option>';
		$output .= '<option value="easeInOutExpo"' . selected($easing, 'easeInOutExpo', FALSE) . '>' . __('easeInOutExpo', 'bod-flipbox') . '</option>';
		$output .= '<option value="easeInOutCirc"' . selected($easing, 'easeInOutCirc', FALSE) . '>' . __('easeInOutCirc', 'bod-flipbox') . '</option>';
		$output .= '<option value="easeInOutBack"' . selected($easing, 'easeInOutBack', FALSE) . '>' . __('easeInOutBack', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// end tab section

		$output = '</div></div>'; // bod-tabs-section and 	bod-tabs-section-h


		// *******************************
		// Front
		// tab section 

		$output .= '<div class="bod-tabs-section" style="display: none">';
		$output .= '<div class="bod-tabs-section-h">';
		echo $output;


		// Icon

		$output = '<div class="bod-eform-row type_select for_front_icon_type">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_type') . '">' . __('Icon', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('front_icon_type') . '" id="' . $this->get_field_id('front_icon_type') . ' ">';
		$output .= '<option value="none"' . selected($front_icon_type, 'none', FALSE) . '>' . __('None', 'bod-flipbox') . '</option>';
		$output .= '<option value="font"' . selected($front_icon_type, 'font', FALSE) . '>' . __('Font Awesome', 'bod-flipbox') . '</option>';
		$output .= '<option value="image"' . selected($front_icon_type, 'image', FALSE) . '>' . __('Image', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Icon Name

		$output = '<div class="bod-eform-row type_textfield for_front_icon_name">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_name') . '">' . __('Icon Name', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('front_icon_name') . '" id="' . $this->get_field_id('front_icon_name') . '"';
		$output .= ' value="' . esc_attr($front_icon_name) . '" />';


		$output .= '</div>';	// row field
		$output .= '<div class="bod-eform-row-description"><a href="https://fontawesome.com/icons" target="_blank">Font Awesome</a> icon</div>';
		$output .= '</div>';	// eform row
		echo $output;



		// Icon Size

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_front_icon_size">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_size') . '">' . __('Icon Size', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="number" name="' . $this->get_field_name('front_icon_size') . '" id="' . $this->get_field_id('front_icon_size') . '"';
		$output .= ' value="' . esc_attr($front_icon_size) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Icon Style

		$output = '<div class="bod-eform-row type_select bod_col-sm-6 bod_column for_front_icon_style">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_style') . '">' . __('Icon Style', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('front_icon_style') . '" id="' . $this->get_field_id('front_icon_style') . ' ">';
		$output .= '<option value="default"' . selected($front_icon_style, 'default', FALSE) . '>' . __('Simple', 'bod-flipbox') . '</option>';
		$output .= '<option value="circle"' . selected($front_icon_style, 'circle', FALSE) . '>' . __('Circle Background', 'bod-flipbox') . '</option>';
		$output .= '<option value="square"' . selected($front_icon_style, 'square', FALSE) . '>' . __('Square Background', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Icon Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_front_icon_color">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_color') . '">' . __('Icon Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('front_icon_color') . '" type="text" name="' . $this->get_field_name('front_icon_color') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($front_icon_color) . '" value="' . esc_attr($front_icon_color) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Icon Background Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_front_icon_bgcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_bgcolor') . '">' . __('Icon Background Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('front_icon_bgcolor') . '" type="text" name="' . $this->get_field_name('front_icon_bgcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($front_icon_bgcolor) . '" value="' . esc_attr($front_icon_bgcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Front Icon Image

		$output = '<div class="bod-eform-row type_images bod_col-sm-6 bod_column for_front_icon_image">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_image') . '">' . __('Icon Image', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';


		$output .= '<div class="bod-imgattach" data-multiple="FALSE">';
		$output .= '<ul class="bod-imgattach-list">';

		$front_icon_image == '' ? $style = ' style="display:none" ' : $style = ' style="" ';
		$output .= '<li data-id="' . $front_icon_image . '"' . $style . '><a href="javascript:void(0)" class="bod-imgattach-delete">&times;</a>' . wp_get_attachment_image($front_icon_image, 'thumbnail', TRUE) . '</li>';
		$output .= '</ul>';

		$add_btn_title = __('Add image', 'bod-flipbox');
		$output .= '<a href="javascript:void(0)" class="bod-imgattach-add" title="' . $add_btn_title . '">+</a>';
		$output .= '<input class="bod-image-details" type="hidden" name="' . $this->get_field_name('front_icon_image') . '" value="' . esc_attr($front_icon_image) . '" />';
		$output .= '</div>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Icon Width

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_front_icon_image_width">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_icon_image_width') . '">' . __('Icon Width (px or %)', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('front_icon_image_width') . '" id="' . $this->get_field_id('front_icon_image_width') . '"';
		$output .= ' value="' . esc_attr($front_icon_image_width) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Flipbox Title

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_front_title">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_title') . '">' . __('Flipbox Title', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('front_title') . '" id="' . $this->get_field_id('front_title') . '"';
		$output .= ' value="' . esc_attr($front_title) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Title Heading Tag

		$output = '<div class="bod-eform-row type_select for_front_title_head">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_title_head') . '">' . __('Title Heading Tag', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('front_title_head') . '" id="' . $this->get_field_id('front_title_head') . ' ">';
		$output .= '<option value="H2"' . selected($front_title_head, 'H2', FALSE) . '>' . __('H2', 'bod-flipbox') . '</option>';
		$output .= '<option value="H3"' . selected($front_title_head, 'H3', FALSE) . '>' . __('H3', 'bod-flipbox') . '</option>';
		$output .= '<option value="H4"' . selected($front_title_head, 'H4', FALSE) . '>' . __('H4', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Flipbox Title Size

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_front_title_size">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_title_size') . '">' . __('Title Size', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('front_title_size') . '" id="' . $this->get_field_id('front_title_size') . '"';
		$output .= ' value="' . esc_attr($front_title_size) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Front Desc

		$output = '<div class="bod-eform-row type_textarea for_front_desc">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_desc') . '">' . __('Description', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<textarea name="' . $this->get_field_name('front_desc') . '" id="' . $this->get_field_id('front_desc') . '">' . esc_textarea($front_desc) . '</textarea>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Elements Order

		$output = '<div class="bod-eform-row type_select for_front_elmorder">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_elmorder') . '">' . __('Elements Order', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('front_elmorder') . '" id="' . $this->get_field_id('front_elmorder') . ' ">';
		$output .= '<option value="itd"' . selected($front_elmorder, 'itd', FALSE) . '>' . __('Icon, Title, Desc', 'bod-flipbox') . '</option>';
		$output .= '<option value="tid"' . selected($front_elmorder, 'tid', FALSE) . '>' . __('Title, Icon, Desc', 'bod-flipbox') . '</option>';
		$output .= '<option value="tdi"' . selected($front_elmorder, 'tdi', FALSE) . '>' . __('Title, Desc, Icon', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Front Background Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_front_bgcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_bgcolor') . '">' . __('Background Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('front_bgcolor') . '" type="text" name="' . $this->get_field_name('front_bgcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($front_bgcolor) . '" value="' . esc_attr($front_bgcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Front Text Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_front_textcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_textcolor') . '">' . __('Text Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('front_textcolor') . '" type="text" name="' . $this->get_field_name('front_textcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($front_textcolor) . '" value="' . esc_attr($front_textcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Front Text Backgound Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_front_text_bgcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_text_bgcolor') . '">' . __('Text Background Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('front_text_bgcolor') . '" type="text" name="' . $this->get_field_name('front_text_bgcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($front_text_bgcolor) . '" value="' . esc_attr($front_text_bgcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Front Text Padding

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_front_text_padding">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_text_padding') . '">' . __('Padding (px, em, rem or %)', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('front_text_padding') . '" id="' . $this->get_field_id('front_text_padding') . '"';
		$output .= ' value="' . esc_attr($front_text_padding) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Front Image

		$output = '<div class="bod-eform-row type_images bod_col-sm-6 bod_column for_front_image">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_image') . '">' . __('Foreground Image', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';


		$output .= '<div class="bod-imgattach" data-multiple="FALSE">';
		$output .= '<ul class="bod-imgattach-list">';

		$front_image == '' ? $style = ' style="display:none" ' : $style = ' style="" ';
		$output .= '<li data-id="' . $front_image . '"' . $style . '><a href="javascript:void(0)" class="bod-imgattach-delete">&times;</a>' . wp_get_attachment_image($front_image, 'thumbnail', TRUE) . '</li>';
		$output .= '</ul>';

		$add_btn_title = __('Add image', 'bod-flipbox');
		$output .= '<a href="javascript:void(0)" class="bod-imgattach-add" title="' . $add_btn_title . '">+</a>';
		$output .= '<input class="bod-image-details" type="hidden" name="' . $this->get_field_name('front_image') . '" value="' . esc_attr($front_image) . '" />';
		$output .= '</div>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Front Foreground Image Size

		$output = '<div class="bod-eform-row type_select  bod_col-sm-6 bod_column for_front_image_size">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('front_image_size') . '">' . __('Image Size', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('front_image_size') . '" id="' . $this->get_field_id('front_image_size') . ' ">';
		$image_sizes = bod_image_sizes_select_values(); // get the image sizes to display
		foreach ($image_sizes as $image_name => $image_desc) {
			$output .= '<option value="' . $image_name . '"' . selected($front_image_size, $image_name, FALSE) . '>' . __($image_desc, 'bod-flipbox') . '</option>';
		}
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// end tab section

		$output = '</div></div>'; // bod-tabs-section and 	bod-tabs-section-h


		// *******************************
		// Back
		// tab section 

		$output .= '<div class="bod-tabs-section" style="display: none">';
		$output .= '<div class="bod-tabs-section-h">';
		echo $output;



		// Back Flipbox Title

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_back_title">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_title') . '">' . __('Flipbox Title', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('back_title') . '" id="' . $this->get_field_id('back_title') . '"';
		$output .= ' value="' . esc_attr($back_title) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Flipbox Title Size

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_back_title_size">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_title_size') . '">' . __('Title Size', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('back_title_size') . '" id="' . $this->get_field_id('back_title_size') . '"';
		$output .= ' value="' . esc_attr($back_title_size) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Back Desc

		$output = '<div class="bod-eform-row type_textarea for_back_desc">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_desc') . '">' . __('Description', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<textarea name="' . $this->get_field_name('back_desc') . '" id="' . $this->get_field_id('back_desc') . '">' . esc_textarea($back_desc) . '</textarea>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Elements Order

		$output = '<div class="bod-eform-row type_select for_back_elmorder">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_elmorder') . '">' . __('Elements Order', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('back_elmorder') . '" id="' . $this->get_field_id('back_elmorder') . ' ">';
		$output .= '<option value="tdb"' . selected($back_elmorder, 'tdb', FALSE) . '>' . __('Title, Description, Button', 'bod-flipbox') . '</option>';
		$output .= '<option value="tbd"' . selected($back_elmorder, 'tbd', FALSE) . '>' . __('Title, Button, Desc', 'bod-flipbox') . '</option>';
		$output .= '<option value="btd"' . selected($back_elmorder, 'btd', FALSE) . '>' . __('Button, Title, Desc', 'bod-flipbox') . '</option>';
		$output .= '</select>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Back Background Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_back_bgcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_bgcolor') . '">' . __('Background Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('back_bgcolor') . '" type="text" name="' . $this->get_field_name('back_bgcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($back_bgcolor) . '" value="' . esc_attr($back_bgcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Back Text Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_back_textcolor">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_textcolor') . '">' . __('Text Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('back_textcolor') . '" type="text" name="' . $this->get_field_name('back_textcolor') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($back_textcolor) . '" value="' . esc_attr($back_textcolor) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Back Background Image

		$output = '<div class="bod-eform-row type_images bod_col-sm-6 bod_column for_back_bgimage">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_bgimage') . '">' . __('Background Image', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';


		$output .= '<div class="bod-imgattach" data-multiple="FALSE">';
		$output .= '<ul class="bod-imgattach-list">';

		$back_bgimage == '' ? $style = ' style="display:none" ' : $style = ' style="" ';
		$output .= '<li data-id="' . $back_bgimage . '"' . $style . '><a href="javascript:void(0)" class="bod-imgattach-delete">&times;</a>' . wp_get_attachment_image($back_bgimage, 'thumbnail', TRUE) . '</li>';
		$output .= '</ul>';

		$add_btn_title = __('Add image', 'bod-flipbox');
		$output .= '<a href="javascript:void(0)" class="bod-imgattach-add" title="' . $add_btn_title . '">+</a>';
		$output .= '<input class="bod-image-details" type="hidden" name="' . $this->get_field_name('back_bgimage') . '" value="' . esc_attr($back_bgimage) . '" />';
		$output .= '</div>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Back Background Image Size

		$output = '<div class="bod-eform-row type_select  bod_col-sm-6 bod_column for_back_bgimage_size">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('back_bgimage_size') . '">' . __('Image Size', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<select name="' . $this->get_field_name('back_bgimage_size') . '" id="' . $this->get_field_id('back_bgimage_size') . ' ">';
		$image_sizes = bod_image_sizes_select_values(); // get the image sizes to display
		foreach ($image_sizes as $image_name => $image_desc) {
			$output .= '<option value="' . $image_name . '"' . selected($back_bgimage_size, $image_name, FALSE) . '>' . __($image_desc, 'bod-flipbox') . '</option>';
		}
		$output .= '</select>';

		$output .= '</div>';	// row field
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


		// Width

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_width">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('width') . '">' . __('Width (px or %)', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('width') . '" id="' . $this->get_field_id('width') . '"';
		$output .= ' value="' . esc_attr($width) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Height

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_height">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('height') . '">' . __('Height (blank = front height)', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('height') . '" id="' . $this->get_field_id('height') . '"';
		$output .= ' value="' . esc_attr($height) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Valign

		$output = '<div class="bod-eform-row type_checkboxes  bod_col-sm-6 bod_column for_valign">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('valign') . '">' . __('Align Content', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<label class="bod-checkbox">';
		$output .= '<input type="checkbox" value="center"';
		if (esc_attr($valign) == 'center') {
			$output .= ' checked="checked"';
		}
		$output .= ' /> ' . __('Center the content vertically', 'bod-flipbox') . '</label>';

		$output .= '<input type="hidden" name="' . $this->get_field_name('valign') . '" value="' . esc_attr($valign) . '" />';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	
		echo $output;


		// Padding

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_padding">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('padding') . '">' . __('Padding (px or %)', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('padding') . '" id="' . $this->get_field_id('padding') . '"';
		$output .= ' value="' . esc_attr($padding) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Full Height

		$output = '<div class="bod-eform-row type_checkboxes for_full_height">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('full_height') . '">' . __('Force Full Height', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<label class="bod-checkbox">';
		$output .= '<input type="checkbox" value="yes"';
		if (esc_attr($full_height) == 'yes') {
			$output .= ' checked="checked"';
		}
		$output .= ' /> ' . __('Force 100% Height', 'bod-flipbox') . '</label>';

		$output .= '<input type="hidden" name="' . $this->get_field_name('full_height') . '" value="' . esc_attr($full_height) . '" />';

		$output .= '</div>';	// row field
		$output .= '<div class="bod-eform-row-description">' . __('Used with flexbox for equal height fields', 'bod-flipbox') . '</div>';

		$output .= '</div>';	// eform row	
		echo $output;


		// Border Radius

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_border_radius">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('border_radius') . '">' . __('Border Radius', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="number" name="' . $this->get_field_name('border_radius') . '" id="' . $this->get_field_id('border_radius') . '"';
		$output .= ' value="' . esc_attr($border_radius) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Border Size

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_border_size">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('border_size') . '">' . __('Border Width', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="number" name="' . $this->get_field_name('border_size') . '" id="' . $this->get_field_id('border_size') . '"';
		$output .= ' value="' . esc_attr($border_size) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Border Color

		$output = '<div class="bod-eform-row type_color bod_col-sm-6 bod_column for_border_color">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('border_color') . '">' . __('Border Color', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input id="' . $this->get_field_id('border_color') . '" type="text" name="' . $this->get_field_name('border_color') . '" class="bod-color-picker"  data-alpha="true" data-default-color="' . esc_attr($border_color) . '" value="' . esc_attr($border_color) . '"/>';

		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row
		echo $output;


		// Class

		$output = '<div class="bod-eform-row type_textfield bod_col-sm-6 bod_column for_el_class">';

		$output .= '<div class="bod-eform-row-title">';
		$output .= '<label for="' . $this->get_field_id('el_class') . '">' . __('Extra CSS Class', 'bod-flipbox') . '</label>';
		$output .= '</div>';

		$output .= '<div class="bod-eform-row-field">';

		$output .= '<input type="text" name="' . $this->get_field_name('el_class') . '" id="' . $this->get_field_id('el_class') . '"';
		$output .= ' value="' . esc_attr($el_class) . '" />';


		$output .= '</div>';	// row field
		$output .= '</div>';	// eform row	

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

	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Sanitize the data

		// General

		$instance['widget_title'] = sanitize_text_field($new_instance['widget_title']);
		$instance['link_type'] = sanitize_text_field($new_instance['link_type']);
		$instance['link'] = $new_instance['link'];
		$instance['back_btn_label'] = sanitize_text_field($new_instance['back_btn_label']);
		$instance['back_btn_bgcolor'] =  bod_sanitize_color($new_instance['back_btn_bgcolor']);
		$instance['back_btn_color'] =  bod_sanitize_color($new_instance['back_btn_color']);
		$instance['animation'] = sanitize_text_field($new_instance['animation']);
		$instance['direction'] = sanitize_text_field($new_instance['direction']);
		$instance['duration'] = absint($new_instance['duration']);
		$instance['easing'] = sanitize_text_field($new_instance['easing']);


		// Front

		$instance['front_icon_type'] = sanitize_text_field($new_instance['front_icon_type']);
		$instance['front_icon_name'] = sanitize_text_field($new_instance['front_icon_name']);
		$instance['front_icon_size'] = absint($new_instance['front_icon_size']);
		$instance['front_icon_style'] = sanitize_text_field($new_instance['front_icon_style']);
		$instance['front_icon_color'] =  bod_sanitize_color($new_instance['front_icon_color']);
		$instance['front_icon_bgcolor'] =  bod_sanitize_color($new_instance['front_icon_bgcolor']);
		$instance['front_icon_image'] = !empty($new_instance['front_icon_image']) ? absint($new_instance['front_icon_image'])  : '';
		$instance['front_icon_image_width'] = sanitize_text_field($new_instance['front_icon_image_width']);
		$instance['front_title'] = sanitize_text_field($new_instance['front_title']);
		$instance['front_title_head'] = sanitize_text_field($new_instance['front_title_head']);
		$instance['front_title_size'] = sanitize_text_field($new_instance['front_title_size']);
		$instance['front_desc'] = sanitize_textarea_field($new_instance['front_desc']);
		$instance['front_elmorder'] = sanitize_text_field($new_instance['front_elmorder']);
		$instance['front_bgcolor'] =  bod_sanitize_color($new_instance['front_bgcolor']);
		$instance['front_textcolor'] =  bod_sanitize_color($new_instance['front_textcolor']);
		$instance['front_text_bgcolor'] =  bod_sanitize_color($new_instance['front_text_bgcolor']);
		$instance['front_text_padding'] =  sanitize_text_field($new_instance['front_text_padding']);
		$instance['front_image'] = !empty($new_instance['front_image']) ? absint($new_instance['front_image'])  : '';
		$instance['front_image_size'] = sanitize_text_field($new_instance['front_image_size']);


		// Back

		$instance['back_title'] = sanitize_text_field($new_instance['back_title']);
		$instance['back_title_size'] = !empty($new_instance['back_title_size']) ? absint($new_instance['back_title_size'])  : '';
		$instance['back_desc'] = sanitize_textarea_field($new_instance['back_desc']);
		$instance['back_elmorder'] = sanitize_text_field($new_instance['back_elmorder']);
		$instance['back_bgcolor'] =  bod_sanitize_color($new_instance['back_bgcolor']);
		$instance['back_textcolor'] =  bod_sanitize_color($new_instance['back_textcolor']);
		$instance['back_bgimage'] = !empty($new_instance['back_bgimage']) ? absint($new_instance['back_bgimage'])  : '';
		$instance['back_bgimage_size'] = sanitize_text_field($new_instance['back_bgimage_size']);


		// Style

		$instance['width'] = sanitize_text_field($new_instance['width']);
		$instance['height'] = sanitize_text_field($new_instance['height']);
		$instance['valign'] = sanitize_text_field($new_instance['valign']);
		$instance['full_height'] = sanitize_text_field($new_instance['full_height']);
		$instance['border_radius'] = absint($new_instance['border_radius']);
		$instance['border_size'] = absint($new_instance['border_size']);
		$instance['border_color'] =  bod_sanitize_color($new_instance['border_color']);
		$instance['padding'] = sanitize_text_field($new_instance['padding']);
		$instance['el_class'] = sanitize_html_class($new_instance['el_class']);


		return $instance;
	}
}

function bod_register_flipbox_widget()
{
	register_widget('bod_flipbox_widget');
}
add_action('widgets_init', 'bod_register_flipbox_widget');
