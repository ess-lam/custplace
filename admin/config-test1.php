<?php 
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };

    
    add_action( 'admin_menu', 'cp_add_admin_menu' );
    add_action( 'admin_init', 'cp_settings_init' );
    
    
    function cp_add_admin_menu(  ) { 
        add_menu_page( 'Custplace Page', 'Custplace',
        'manage_options', 'custplace', 'cp_options_page' );
    }
    
    
    function cp_settings_init(  ) { 
    
        register_setting( 'pluginPage', 'custp_settings' );
        
        $sections = array( 
            array(
                'id' => 'cp_pluginPage_section1',
                'title' => 'Informations de connexion'
            ),
            array(
                'id' => 'cp_pluginPage_section2',
                'title' => 'Delai de sollicitation'
            ),
            array(
                'id' => 'cp_pluginPage_section3',
                'title' => 'Clé Widget'
            )
        );

        // add the sections
        foreach( $sections as $section ) {
            add_settings_section(
                $section['id'], 
                __( $section['title'], 'custplace_plugin' ), 
                'cp_settings_section_callback', 
                'pluginPage'
            );
        }
            // Array contains the infos of the input fields
        $fields = array(
            array(
                'name'    => 'cp_field1_section1',
                'label'   => 'ID client',
                'type'    => 'number',
                'section' => 'cp_pluginPage_section1',
                'description' => ""
            ),
            array(
                'name'    => 'cp_field2_section1',
                'label'   => 'Clé API',
                'type'    => 'password',
                'section' => 'cp_pluginPage_section1',
                'description' => ""
            ),
            array(
                'name'    => 'cp_field1_section2',
                'label'   => 'Delai de sollicitation',
                'type'    => 'number',
                'section' => 'cp_pluginPage_section2',
                'description' => "jours"
            ),
            array(
                'name'    => 'cp_field1_section3',
                'label'   => 'Clé widget',
                'type'    => 'password',
                'section' => 'cp_pluginPage_section3',
                'description' => ""
            )
        );
        
        // add the fields of the first section 
        foreach( $fields as $field ) {
            add_settings_field( 
                $field['name'], 
                __( $field['label'], 'custplace_plugin' ), 
                'cp_input_field_render', 
                'pluginPage', 
                $field['section'],
                array(
                    'label_for'   => $field['name'],
                    'type'        => $field['type'],
                    'description' => $field['description']                    
                )
            );    
        }

        // add the radios fields of the third section
        add_settings_field( 
            'cp_field2_section3', 
            __( 'Widget Avis Produit', 'custplace_plugin' ), 
            'cp_input_radio_field_render', 
            'pluginPage', 
            'cp_pluginPage_section3',
            array(
                'id'          => "cp_field2_section3",
                'description' => "Activer le widget avis produit
                afin d'atteindre les avis de votre clients"
            )
        );
        
        add_settings_field( 
            'cp_field3_section3', 
            __( 'Widget Sceau de confiance', 'custplace_plugin' ), 
            'cp_input_radio_field_render', 
            'pluginPage', 
            'cp_pluginPage_section3',
            array(
                'id'          => "cp_field3_section3",
                'description' => "Activer le widget sceau de confiance"
            )
        );

    }

        // the function to render the fields
    function cp_input_field_render( $args ) { 
        $options = get_option( 'custp_settings' );
        ?>
        <input 
            type='<?php echo esc_attr( $args['type']); ?>' 
            name='custp_settings[<?php echo esc_attr( $args['label_for'] ); ?>]' 
            value='<?php echo isset( $options[ $args['label_for'] ] ) ? ( $options[ $args['label_for'] ] ) : ( '' ); ?>'
        />
        <?php
            if($args['label_for'] == "cp_field1_section2") {
        ?>
                <p class="description">
                <?php esc_html_e( $args[ 'description' ], 'myplugin-settings' ); ?>
                </p>
        <?php
            }
           
    }
         // the function to render the radio fields
    function cp_input_radio_field_render( $args ) {
        $options = get_option( 'custp_settings' );
        ?>
        <!-- first radio button -->
        <label>
            <input 
                type='radio' 
                name='custp_settings[<?php echo esc_attr( $args['id'] ); ?>]' 
                <?php echo isset( $options[ $args['id'] ] ) ? ( checked( $options[ $args['id'] ], 'Non' ) ) : ( '' ); ?>
                value= "Non"
            />
            <?php esc_html_e( 'Non &nbsp;&nbsp;', 'myplugin-settings' ); ?>
        </label>
        <!-- second radio button -->
        <label>
            <input 
                type='radio' 
                name='custp_settings[<?php echo esc_attr( $args['id'] ); ?>]' 
                <?php echo isset( $options[ $args['id'] ] ) ? ( checked( $options[ $args['id'] ], 'Oui' ) ) : ( 'checked' ); ?>
                value= "Oui"
            />
            <?php esc_html_e( 'Oui', 'myplugin-settings' ); ?>
        </label>
        <!-- description under the radio buttons -->
        <p class="description">
            <?php esc_html_e( $args[ 'description' ], 'myplugin-settings' ); ?>
        </p>
        <?php
    }

        // callback function to render the intro of the section 
    function cp_settings_section_callback(  ) { 
        echo __( '', 'custplace_plugin' );
    }

        // callback function of "cp_add_admin_menu" to render the settings page 
    function cp_options_page(  ) { 
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // add error/update messages

        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'myplugin_settings_messages', 'myplugin_settings_message', __( 'Settings Saved', 'custplace_plugin' ), 'updated' );
        }

        // show error/update messages
        settings_errors( 'myplugin_settings_messages' );
        ?>
        <form action='options.php' method='post'>

            <h2>Custplace</h2>

            <?php
            settings_fields( 'pluginPage' );
            do_settings_sections( 'pluginPage' );
            submit_button();
            ?>

        </form>
        <?php
        
    }
        


        // get the order infos with the status completed 
    function cp_woocommerce_order_status_completed( $order_id ) {
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
    add_action( 'woocommerce_order_status_completed', 'cp_woocommerce_order_status_completed', 10, 1 );
    
    
    

    