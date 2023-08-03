<?php
    /*
    * Plugin Name: Custplace
    * Description: Custplace plugin test.
    */
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };
        

    if (! class_exists( 'Custplace' )) {

        require_once __DIR__ . '/includes/Custplace.php';
        register_activation_hook( __FILE__, array('Custplace' ,'activate') );

    }
