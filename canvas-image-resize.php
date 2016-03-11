<?php

/*
Plugin Name: Canvas Image Resize
Description: Re-sizes images right inside the browser BEFORE uploading them.
Version: 1.0.0
Author: Simon Sippert
Author URI: http://www.sippsolutions.de/
*/

/*
Canvas Image Resize, a plugin for WordPress
Copyright (C) 2016 Simon Sippert, sippsolutions (http://www.sippsolutions.de)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Canvas Image Resize Main Class
 *
 * @copyright 2016 Simon Sippert <s.sippert@sippsolutions.de>
 */
class Canvas_Image_Resize
{
    /**
     * Defines the plugin name
     *
     * @type string
     */
    const PLUGIN_NAME = 'Canvas Image Resize';

    /**
     * Defines the text domain
     *
     * @type string
     */
    const TEXT_DOMAIN = 'canvas-image-resize';

    /**
     * Defines the plugin's options page name
     *
     * @type string
     */
    const OPTIONS_PAGE_NAME = 'cir_options';

    /**
     * Field name for max width
     *
     * @type string
     */
    const FIELD_IMAGE_MAX_WIDTH = 'image_max_width';

    /**
     * Field name for max height
     *
     * @type string
     */
    const FIELD_IMAGE_MAX_HEIGHT = 'image_max_height';

    /**
     * Field name for max quality
     *
     * @type string
     */
    const FIELD_IMAGE_MAX_QUALITY = 'image_max_quality';

    /**
     * Stores default options
     *
     * @var array
     */
    protected $_defaultOptions = array(
        self::FIELD_IMAGE_MAX_WIDTH => 1600,
        self::FIELD_IMAGE_MAX_HEIGHT => 1600,
        self::FIELD_IMAGE_MAX_QUALITY => 100,
    );

    /**
     * Initializes the plugin
     */
    public function __construct() {
        $this->_initFilterSettings();
        $this->_initPluginPage();
    }

    /**
     * Initializes the filter settings
     */
    protected function _initFilterSettings() {
        add_filter('plupload_default_settings', array($this, 'setImageSettings'), 100);
        add_filter('plupload_default_params', array($this, 'setImageSettings'), 100);
        add_filter('plupload_init', array($this, 'setImageSettings'), 100);
    }

    /**
     * Initializes the plugin page
     */
    protected function _initPluginPage() {
        add_action('admin_init', array($this, 'initOptionsPage'));
        add_action('admin_menu', array($this, 'addOptionsPage'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'addPluginPage'));
    }

    /**
     * Adds the plugin page
     *
     * @param array $links
     * @return array
     */
    public function addPluginPage(array $links) {
        array_unshift($links, '<a href="options-general.php?page=' . self::TEXT_DOMAIN . '">' . __('Settings') . '</a>');
        return $links;
    }

    /**
     * Adds the options page
     */
    public function addOptionsPage() {
        add_options_page(self::PLUGIN_NAME, self::PLUGIN_NAME, 'manage_options', self::TEXT_DOMAIN, array($this, 'renderOptionsPage'));
    }

    /**
     * Renders the options page
     */
    public function initOptionsPage() {
        // add the possibility to add settings
        register_setting(self::OPTIONS_PAGE_NAME, self::TEXT_DOMAIN . '_settings');

        // set section name
        $sectionName = implode('_', array(self::TEXT_DOMAIN, self::OPTIONS_PAGE_NAME, 'general'));

        // add section
        add_settings_section(
            $sectionName,
            __('General settings'),
            null,
            self::OPTIONS_PAGE_NAME
        );

        // add fields
        add_settings_field(
            self::FIELD_IMAGE_MAX_WIDTH,
            __('Maximum width of images'),
            array($this, 'renderFieldGeneralImageMaxWidth'),
            self::OPTIONS_PAGE_NAME,
            $sectionName
        );
        add_settings_field(
            self::FIELD_IMAGE_MAX_HEIGHT,
            __('Maximum height of images'),
            array($this, 'renderFieldGeneralImageMaxHeight'),
            self::OPTIONS_PAGE_NAME,
            $sectionName
        );
        add_settings_field(
            self::FIELD_IMAGE_MAX_QUALITY,
            __('Quality of images (0-100)'),
            array($this, 'renderFieldGeneralImageMaxQuality'),
            self::OPTIONS_PAGE_NAME,
            $sectionName
        );
    }

    /**
     * Renders the options page
     */
    public function renderOptionsPage() {
        ?>
        <form action='options.php' method='post'>
            <h1><?php echo self::PLUGIN_NAME; ?></h1>

            <p><?php echo _('Below you can configure which maximum dimensions images uploaded to your site should have.'); ?></p>
            <?php
            settings_fields(self::OPTIONS_PAGE_NAME);
            do_settings_sections(self::OPTIONS_PAGE_NAME);
            submit_button();
            ?>
        </form>
    <?php
    }

    /**
     * Renders a field
     *
     * @param string $name
     * @param string [$type]
     */
    protected function _renderField($name, $type = 'number') {
        $options = wp_parse_args(get_option(self::TEXT_DOMAIN . '_settings'), $this->_defaultOptions);
        ?>
        <input type='<?php echo $type; ?>' name='<?php echo self::TEXT_DOMAIN . '_settings'; ?>[<?php echo $name; ?>]'
               value='<?php echo $type == 'number' ? abs((int)$options[$name]) : $options[$name]; ?>'>
    <?php
    }

    /**
     * Renders a specific field
     */
    public function renderFieldGeneralImageMaxWidth() {
        $this->_renderField(self::FIELD_IMAGE_MAX_WIDTH);
    }

    /**
     * Renders a specific field
     */
    public function renderFieldGeneralImageMaxHeight() {
        $this->_renderField(self::FIELD_IMAGE_MAX_HEIGHT);
    }

    /**
     * Renders a specific field
     */
    public function renderFieldGeneralImageMaxQuality() {
        $this->_renderField(self::FIELD_IMAGE_MAX_QUALITY);
    }

    /**
     * Sets image re-sizing settings
     * [Does all the magic :3]
     *
     * @param array $defaults
     * @return array
     */
    public function setImageSettings(array $defaults) {
        // get options
        $options = wp_parse_args(get_option(self::TEXT_DOMAIN . '_settings'), $this->_defaultOptions);

        // set values
        $defaults['resize'] = array(
            'width' => abs((int)$options[self::FIELD_IMAGE_MAX_WIDTH]),
            'height' => abs((int)$options[self::FIELD_IMAGE_MAX_HEIGHT]),
            'quality' => abs((int)$options[self::FIELD_IMAGE_MAX_QUALITY]),
        );
        return $defaults;
    }
}

// init
new Canvas_Image_Resize();
