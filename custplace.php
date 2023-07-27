<?php
    /*
    * Plugin Name: Custplace
    * Description: Custplace plugin test.
    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };

    register_activation_hook(
        __FILE__,
        'flush_rewrite_rules'
    );

    register_deactivation_hook(
        __FILE__,
        'flush_rewrite_rules'
    );

    if (is_admin()) {
        require_once __DIR__ . '/admin/config-test1.php';
        // require_once plugin_dir_path(__FILE__) . 'admin/config-test1.php';
    }

    