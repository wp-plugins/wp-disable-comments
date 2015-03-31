<?php
/*
Plugin Name: WP Disable Comments
Plugin URI:  https://wordpress.org/plugins/wp-disable-comments/
Description: Disable comments, trackbacks and/or pingbacks
Version:     0.4
Author:      Henri Benoit
Author URI:  http://benohead.com
*/

/*
 * This plugin was built on top of WordPress-Plugin-Skeleton by Ian Dunn.
 * See https://github.com/iandunn/WordPress-Plugin-Skeleton for details.
 */

if (!defined('ABSPATH')) {
    die('Access denied.');
}

define('WPDC_NAME', 'WP Disable Comments');
define('WPDC_REQUIRED_PHP_VERSION', '5.3'); // because of get_called_class()
define('WPDC_REQUIRED_WP_VERSION', '3.1'); // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function wpdc_requirements_met()
{
    global $wp_version;

    if (version_compare(PHP_VERSION, WPDC_REQUIRED_PHP_VERSION, '<')) {
        return false;
    }

    if (version_compare($wp_version, WPDC_REQUIRED_WP_VERSION, '<')) {
        return false;
    }

    return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function wpdc_requirements_error()
{
    global $wp_version;

    require_once(dirname(__FILE__) . '/views/requirements-error.php');
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if (wpdc_requirements_met()) {
    require_once(__DIR__ . '/classes/wpdc-module.php');
    require_once(__DIR__ . '/classes/wp-disable-comments.php');
    require_once(__DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php');
    require_once(__DIR__ . '/classes/wpdc-settings.php');
    require_once(__DIR__ . '/classes/wpdc-instance-class.php');

    if (class_exists('WordPress_Disable_Comments')) {
        $GLOBALS['wpdc'] = WordPress_Disable_Comments::get_instance();
        register_activation_hook(__FILE__, array($GLOBALS['wpdc'], 'activate'));
        register_deactivation_hook(__FILE__, array($GLOBALS['wpdc'], 'deactivate'));
    }
} else {
    add_action('admin_notices', 'wpdc_requirements_error');
}
