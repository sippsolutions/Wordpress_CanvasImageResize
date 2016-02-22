<?php

/*
Plugin Name: Canvas Image Resize
Description: Resizes images in browser before upload (max. 1600px x 1600px)
Version: 0.1.0
Author: Simon Sippert
Author URI: http://www.sippsolutions.de/
*/

class Canvas_Image_Resize {
    function __construct() {
        add_filter('plupload_default_settings', array($this, 'set_settings'));
    }

    function set_settings($defaults) {
        $defaults['resize'] = array(
            'width' => 1600,
            'height' => 1600,
            'quality' => 100,
        );
        return $defaults;
    }
}

// init
new Canvas_Image_Resize();
