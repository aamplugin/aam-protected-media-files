<?php

/**
 * Plugin Name: AAM Protected Media Files
 * Description: Manage access to a physical file and prevent from a direct access
 * Version: 1.0.0
 * Author: Vasyl Martyniuk <vasyl@vasyltech.com>
 * Author URI: https://vasyltech.com
 *
 * -------
 * LICENSE: This file is subject to the terms and conditions defined in
 * file 'LICENSE', which is part of AAM Protected Media Files source package.
 **/

namespace AAM\AddOn\ProtectedMediaFiles;

require_once __DIR__ . '/application/Handler.php';

/**
 * Main add-on's bootstrap class
 *
 * @package AAM\AddOn\ProtectedMediaFiles
 * @author Vasyl Martyniuk <vasyl@vasyltech.com>
 * @version 1.0.0
 */
class Bootstrap
{

    /**
     * Single instance of itself
     *
     * @var Bootstrap
     *
     * @access private
     * @version 1.0.0
     */
    private static $_instance = null;

    /**
     * Initialize the Bootstrap object
     *
     * @return void
     *
     * @access protected
     * @version 1.0.0
     */
    protected function __construct()
    {
    }

    /**
     * Hook on WP core init
     *
     * @return void
     *
     * @access public
     * @version 1.0.0
     */
    public static function onInit()
    {
        Bootstrap::getInstance();

        // Check Media Access if needed
        if (filter_input(INPUT_GET, 'aam-media')) {
            Handler::bootstrap()->authorize();
        }
    }

    /**
     * Initialize the plugin
     *
     * @return Bootstrap
     *
     * @access public
     * @version 1.0.0
     */
    public static function getInstance()
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
     *
     * @access public
     * @version 1.0.0
     */
    public static function activate()
    {
        global $wp_version;

        if (version_compare(PHP_VERSION, '5.6.40') === -1) {
            exit(__('PHP 5.6.40 or higher is required.'));
        } elseif (version_compare($wp_version, '4.7.0') === -1) {
            exit(__('WP 4.7.0 or higher is required.'));
        } elseif (!defined('AAM_VERSION') || (version_compare(AAM_VERSION, '6.0.3') === -1)) {
            exit(__('Free Advanced Access Manager plugin 6.0.3 or higher is required.'));
        }
    }

}

if (defined('ABSPATH')) {
    // Init hook
    add_action('init', __NAMESPACE__ . '\Bootstrap::onInit');

    // Activation hooks
    register_activation_hook(__FILE__, __NAMESPACE__ . '\Bootstrap::activate');
}