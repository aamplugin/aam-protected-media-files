<?php

/**
 * Plugin Name: AAM Protected Media Files
 * Description: Manage access to a physical file and prevent from a direct access
 * Version: 1.3.2
 * Author: Vasyl Martyniuk <vasyl@vasyltech.com>
 * Author URI: https://vasyltech.com
 *
 * -------
 * LICENSE: This file is subject to the terms and conditions defined in
 * file 'LICENSE', which is part of AAM Protected Media Files source package.
 **/

namespace AAM\AddOn\ProtectedMediaFiles;

use AAM;

require_once __DIR__ . '/application/Handler.php';
require_once __DIR__ . '/application/HandlerV7.php';

/**
 * Main add-on's bootstrap class
 *
 * @since 1.1.0 Deeper integration with AAM core services (URI Access & Access
 *              Denied Redirect)
 * @since 1.0.0 Initial implementation of the method
 *
 * @package AAM\AddOn\ProtectedMediaFiles
 * @author Vasyl Martyniuk <vasyl@vasyltech.com>
 * @version 1.1.0
 */
class Bootstrap
{

    /**
     * Single instance of itself
     *
     * @var Bootstrap
     * @access private
     *
     * @version 1.0.0
     */
    private static $_instance = null;

    /**
     * Initialize the Bootstrap object
     *
     * @return void
     * @access protected
     *
     * @since 1.1.0 Adding new setting to the AAM Settings area
     * @since 1.0.0 Initial implementation of the method
     *
     * @version 1.1.0
     */
    protected function __construct()
    {
        if (is_admin()) {
            add_filter(
                'aam_settings_list_filter',
                function($options, $type) {
                    return $this->_register_additional_settings($options, $type);
                }, 10, 2
            );
        }
    }

    /**
     * Extend AAM Settings "Content" tab with additional option
     *
     * @param array  $options
     * @param string $type
     *
     * @return array
     * @access private
     *
     * @version 1.3.0
     */
    private function _register_additional_settings($options, $type)
    {
        if ($type === 'content') {
            if (self::is_v7()) {
                $key   = 'addon.protected_media_files.settings.denied_redirect';
                $value = AAM::api()->config->get($key, false);
            } else {
                $key   = 'addon.protected-media-files.settings.deniedRedirect';
                $value = AAM::api()->getConfig($key, false);
            }

            $options[$key] = array(
                'title'       => __(
                    'Use Access Denied Redirect For Restricted Media Items',
                    'aam-protected-media-files'
                ),
                'description' => __(
                    'When direct access to a physical file is restricted, the default behavior is to return HTTP 401 (Unauthorized) response. If you enable this option, the Access Denied Redirect rule applies instead.',
                    'aam-protected-media-files'
                ),
                'value'       => $value
            );
        }

        return $options;
    }

    /**
     * Hook on WP core init
     *
     * @return void
     * @access public
     *
     * @since 1.3.0 Ability to handle AAM v6 & v7
     * @since 1.0.0 Initial implementation of the method
     *
     * @version 1.3.0
     */
    public static function on_init()
    {
        Bootstrap::get_instance();

        // Check Media Access if needed
        if (filter_input(INPUT_GET, 'aam-media')) {
            if (self::is_v7()) {
                HandlerV7::bootstrap()->authorize();
            } else {
                Handler::bootstrap()->authorize();
            }
        }
    }

    /**
     * Check if AAM is V7
     *
     * @return boolean
     * @access public
     *
     * @version 1.3.0
     */
    public static function is_v7()
    {
        return version_compare(AAM_VERSION, '7.0.0-alpha.1') >= 0;
    }

    /**
     * Initialize the plugin
     *
     * @return Bootstrap
     * @access public
     *
     * @version 1.0.0
     */
    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Activation hook
     *
     * @return void
     * @access public
     *
     * @version 1.0.0
     */
    public static function activate()
    {
        global $wp_version;

        if (version_compare(PHP_VERSION, '5.6.40') === -1) {
            exit(__('PHP 5.6.40 or higher is required.', 'aam-protected-media-files'));
        } elseif (version_compare($wp_version, '5.0.0') === -1) {
            exit(__('WP 5.0.0 or higher is required.', 'aam-protected-media-files'));
        } elseif (!defined('AAM_KEY')) {
            exit(__(
                'Free Advanced Access Manager plugin is required to be active.',
                'aam-protected-media-files'
            ));
        }
    }

}

if (defined('ABSPATH')) {
    // Init hook
    add_action('wp_loaded', __NAMESPACE__ . '\Bootstrap::on_init', PHP_INT_MAX);

    // Activation hooks
    register_activation_hook(__FILE__, __NAMESPACE__ . '\Bootstrap::activate');
}