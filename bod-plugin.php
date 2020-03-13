<?php defined('ABSPATH') or die('This script cannot be accessed directly.');
/*
Plugin Name: Bod Widgets
Description: A number of useful widgets includes posts carousel and flipbox.
Version: 1.1.1
Author: Mark Bird
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
// 1.1 introduce modal widget and shortcode
// 1.1.1 2/1/2020 allow rgba color picker in flipbox

include(plugin_dir_path(__FILE__) . 'inc/bod-carousel-shortcode.php');
include(plugin_dir_path(__FILE__) . 'inc/bod-carousel-widget.php');
include(plugin_dir_path(__FILE__) . 'inc/bod-flipbox-widget.php');
include(plugin_dir_path(__FILE__) . 'inc/bod-modal-shortcode.php');
include(plugin_dir_path(__FILE__) . 'inc/bod-modal-widget.php');
