<?php
    /*
    * Plugin Name: Custplace
    * Description: Custplace plugin test.
    */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };
        

    if (! class_exists( 'CustpGonfig' )) {
        require_once __DIR__ . '/includes/CustpGonfig.php';
    }

    $custp_config_obj = new CustpGonfig();

    // get the order infos with the status completed 
    function get_completed_orders_infos( $order_id ) {
    $order = new WC_Order($order_id);

    $order_infos['order_id'] = $order->get_id();
    $order_infos['costumer_last_name'] = $order->get_billing_last_name();
    $order_infos['costumer_first_name'] = $order->get_billing_first_name();
    $order_infos['costumer_email'] = $order->get_billing_email();
    $order_infos['products'] = array();

    foreach( $order->get_items() as $item_id => $item ) {
        // $product_id = $item->get_product_id();
        $product_name = $item->get_name();
        $product = $item->get_product();
        $item_sku = $product->get_sku();
        $product_link = $product->get_permalink();

        array_push($order_infos['products'], array(
            'sku'           => $item_sku,
            'name'          => $product_name,
            'product_link'  => $product_link 
        ));
    }
    var_dump($order_infos); 
    die();
    }
    add_action( 'woocommerce_order_status_completed', 'get_completed_orders_infos', 10, 1 );
            
        