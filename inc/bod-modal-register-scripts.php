<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

// resources needed for modal popup

    wp_register_style( 'bod-modal-style',
        plugin_dir_url (__DIR__) .  'css/bod-modal.css',
		array(),
		'1.1'
    );
	
    wp_register_script( 'bod-core-script',
        plugin_dir_url (__DIR__) .  'js/bod-core.js',
		array('jquery'),
		'1.1',
		true
    );		
				
    wp_register_script( 'bod-modal-script',
        plugin_dir_url (__DIR__) .  'js/bod-modal.js',
		array('jquery'),
		'1.1',
		true
	);

?>