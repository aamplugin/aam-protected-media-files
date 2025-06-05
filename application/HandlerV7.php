<?php

/**
 * =========================================================================
 * LICENSE: This file is subject to the terms and conditions defined in    *
 * file 'LICENSE', which is part of the AAM Protected Media Files package  *
 * =========================================================================
 */

namespace AAM\AddOn\ProtectedMediaFiles;

use AAM;

/**
 * File access handler
 *
 * @package AAM\AddOn\ProtectedMediaFiles
 * @author Vasyl Martyniuk <vasyl@vasyltech.com>
 *
 * @version 1.3.0
 */
class HandlerV7
{
    /**
     * Instance of itself
     *
     * @var AAM\AddOn\ProtectedMediaFiles\HandlerV7
     * @access private
     *
     * @version 1.3.0
     */
    private static $_instance = null;

    /**
     * Requested file
     *
     * @var string
     * @access protected
     *
     * @version 1.3.0
     */
    protected $request;

    /**
     * Initialize the access to files
     *
     * @return void
     * @access protected
     *
     * @version 1.3.0
     */
    protected function __construct()
    {
        $media = $this->get_from_query('aam-media');

        if (is_numeric($media)) { // Redirecting with aam-media=1 query param
            $request = $this->get_from_server('REQUEST_URI');
        } else { // Otherwise, this is most likely Nginx redirect rule
            $request = $media;
        }

        // Hooking into URI Access Service
        add_filter('aam_uri_match_filter', function($match, $uri, $s) {
            $media = $this->get_from_query('aam-media');

            if (empty($match) && !empty($media)) {
                $match = ($uri === $media);
            }

            return $match;
        }, 100, 3);

        // Stripping any query params
        $this->request = urldecode(
            ltrim(preg_replace('/(\?.*|#)$/', '', $request), '/')
        );
    }

    /**
     * Authorize direct access to file
     *
     * @return void
     * @access public
     *
     * @version 1.3.0
     */
    public function authorize()
    {
        // First, let's check if URI is restricted
        $service = AAM::api()->urls();
        $uri     = '/' . $this->request;

        if ($service->is_denied($uri)) {
            $redirect = $service->get_redirect($uri);

            if (empty($redirect) || $redirect['type'] === 'default') {
                AAM::api()->redirect->do_access_denied_redirect();
            } else {
                AAM::api()->redirect->do_redirect($redirect);
            }
        }

        // If there are no restrictions with URL service, then let's see if there are
        // any defined with Content service
        $media = $this->find_media_item();

        if ($media === null) { // File is not part of the Media library
            $this->_output_file_content($this->_prepare_file_absolute_path());
        } else {
            if (AAM::api()->posts()->is_restricted($media)) {
                if (AAM::api()->config->get(
                    'addon.protected_media_files.settings.denied_redirect', false
                )) {
                    AAM::api()->redirect->do_access_denied_redirect();
                } else {
                    http_response_code(401); exit;
                }
            } else {
                $this->_output_file_content($this->_prepare_file_absolute_path());
            }
        }
    }

    /**
     * Find file based on the URI
     *
     * @return WP_Post|null
     * @access protected
     * @global WPDB $wpdb
     *
     * @version 1.3.0
     */
    protected function find_media_item()
    {
        global $wpdb;

        $file_path = $this->_prepare_file_absolute_path();

        // 1. Get base path and use in the research
        $s = basename($file_path);

        // 2. Replace the path to the media
        $basedir = wp_upload_dir();

        // Covering the scenario when filename is actually something like
        // 2019/11/banner-1544x500.png
        $relpath_base = str_replace($basedir['basedir'], '', $s);
        $relpath_full = str_replace($basedir['basedir'], '', $file_path);

        $pm_query  = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s ";
        $pm_query .= "AND meta_value LIKE %s";

        $id = $wpdb->get_var($wpdb->prepare($pm_query,
            '_wp_attachment_metadata',
            '%:"' . $wpdb->esc_like($s) . '";%'
        ));

        if (empty($id)) { // Try to find the image by GUID
            $id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE %s",
                    // Replace the cropped extension for images
                    [ '%' . preg_replace('/(-[\d]+x[\d]+)(\.[\w]+)$/', '$2', $relpath_base) ]
                )
            );
        }

        // Allow other add-ons to hook into the media search if none is found
        if (empty($id)) {
            $id = apply_filters(
                'aam_find_media_id_filter', $id, $relpath_base, $relpath_full
            );
        }

        return $id ? get_post($id) : null;
    }

    /**
     * Compute correct physical location to the file
     *
     * @return string
     * @access private
     *
     * @version 1.3.0
     */
    private function _prepare_file_absolute_path()
    {
        // Get the sub dir path if website is located in subdirectory
        $sub_folder = ltrim(dirname($this->get_from_server('PHP_SELF')), '/');

        if (strpos($this->request, $sub_folder . '/') === 0) {
            $request = substr($this->request, strlen($sub_folder) + 1);
        } else {
            $request = $this->request;
        }

        // To cover scenarios where the absolute path does not really reflect the
        // actual path to the file (containerized WP instances).
        $absdir = AAM::api()->config->get(
            'addon.protected_media_files.settings.absolute_path',
            ABSPATH
        );

        return realpath($absdir . $request);
    }

    /**
     * Output file if valid
     *
     * @param string      $filename
     * @param string|null $mime
     *
     * @return void
     * @access private
     *
     * @version 1.3.0
     */
    private function _output_file_content($filename, $mime = null)
    {
        $filename = realpath(urldecode($filename));

        if (is_string($filename) && $this->_is_file_allowed($filename)) {
            if (empty($mime)) {
                if (function_exists('mime_content_type')) {
                    $mime = mime_content_type($filename);
                } else {
                    $mime = 'application/octet-stream';
                }
            }

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($filename));

            // Calculate etag
            $last_modified = gmdate('D, d M Y H:i:s', filemtime($filename));
            $etag = '"' . md5( $last_modified ) . '"';

            header("Last-Modified: $last_modified GMT");
            header("ETag: {$etag}");
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 100000000) . ' GMT');

            if (defined('WP_DEBUG') && WP_DEBUG) {
                header('X-Served-By: AAM Protected Media Files');
            }

            // Finally read the file
            readfile($filename);
        } else {
            http_response_code(403);
        }

        exit;
    }

    /**
     * Making sure that file can be released
     *
     * @param string $filename
     *
     * @return boolean
     * @access private
     *
     * @version 1.3.0
     */
    private function _is_file_allowed($filename)
    {
        $response = true; // By default, allowing access to request file unless ...

        // Check if file extension is valid
        $type_check = wp_check_filetype(basename($filename));

        // If file has invalid mime type, then do not take it into consideration.
        // There is no way to define access to this file anyway
        if (empty($type_check['ext'])) {
            $response = false;
        }

        // Security checks. Making sure that file is located in the uploads directory
        // and the file extension is valid to current WP instance
        return $response;
    }

    /**
     * Get data from the GET/Query
     *
     * @param string $param
     * @param int    $filter
     * @param int    $options
     *
     * @return mixed
     * @access protected
     *
     * @version 1.3.0
     */
    protected function get_from_query($param, $filter = FILTER_DEFAULT, $options = 0)
    {
        $get = filter_input(INPUT_GET, $param, $filter, $options);

        if (is_null($get)) {
            $get = filter_var($this->_read_from_array(
                $_GET, $param), $filter, $options
            );
        }

        return $get;
    }

    /**
     * Get data from the super-global $_SERVER
     *
     * @param string $param
     * @param int    $filter
     * @param int    $options
     *
     * @return mixed
     * @access protected
     *
     * @version 1.3.0
     */
    protected function get_from_server($param, $filter = FILTER_DEFAULT, $options = 0)
    {
        $var = filter_input(INPUT_SERVER, $param, $filter, $options);

        // Cover the unexpected server issues (e.g. FastCGI may cause unexpected null)
        if (empty($var)) {
            $var = filter_var(
                $this->_read_from_array($_SERVER, $param), $filter, $options
            );
        }

        return $var;
    }

    /**
     * Check array for specified parameter and return the it's value or
     * default one
     *
     * @param array  $array   Global array _GET, _POST etc
     * @param string $param   Array Parameter
     * @param mixed  $default Default value
     *
     * @return mixed
     * @access private
     *
     * @version 1.3.0
     */
    private function _read_from_array($array, $param, $default = null)
    {
        $value = $default;

        if (is_null($param)) {
            $value = $array;
        } else {
            $chunks = explode('.', $param);
            $value = $array;
            foreach ($chunks as $chunk) {
                if (isset($value[$chunk])) {
                    $value = $value[$chunk];
                } else {
                    $value = $default;
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * Bootstrap the handler
     *
     * @return AAM\AddOn\ProtectedMediaFiles\HandlerV7
     * @access public
     *
     * @version 1.3.0
     */
    public static function bootstrap()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

}