<?php
    /*
    * Plugin Name: Custplace
    * Description: Custplace plugin test.
    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };
        

    // Setup class autoloader
    require_once dirname(__FILE__) . '/src/MyPlugin/Autoloader.php';
    MyPlugin_Autoloader::register();

    $myplugin = new MyPlugin_Plugin(__FILE__);
    add_action('wp_loaded', array($myplugin, 'load'));









    // if (is_admin()) {
    //     require_once __DIR__ . '/admin/config-test1.php';
    //     // require_once plugin_dir_path(__FILE__) . 'admin/config-test1.php';
    // }

    