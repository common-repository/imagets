<?php

    /*
    Plugin Name: ImageTS
    Plugin URI: https://imagets.com
    Description: You can collect, convert and upload images. You can do all of them with ImageTS easily. It works in a compatible way with Wordpress.
    Author: ImageTS
    Version: 2.0.1
    Author URI: https://imagets.com
    */

    if ( ! defined( 'ABSPATH' ) ) exit;

    define('IMAGETS_PLUGIN_NAME', 'ImageTS - Media from web');
    define('IMAGETS_PLUGIN_URL', 'http://imagets.com');
    define('IMAGETS_PLUGIN_MAIN_FILE_PATH', __FILE__);
    define('IMAGETS_DEFAULT_BOX_STYLE', 'clean');
    define('IMAGETS_CONFIG_MENU_TEXT', 'ImageTS Settings');

    require_once('plugin.php');
    IMAGETS_Plugin_init();

    register_activation_hook(IMAGETS_PLUGIN_MAIN_FILE_PATH,   array('IMAGETS_Plugin', 'activate')); 
    register_deactivation_hook(IMAGETS_PLUGIN_MAIN_FILE_PATH, array('IMAGETS_Plugin', 'deactivate')); 
    register_uninstall_hook(IMAGETS_PLUGIN_MAIN_FILE_PATH,    array('IMAGETS_Plugin', 'uninstall'));