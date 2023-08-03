<?php

class Custplace
{
    function __construct()
    {
        add_action( 'admin_menu', array($this, 'add_admin_menu')  );
        add_action( 'admin_init', array($this, 'settings_init') );
        add_action( 'woocommerce_order_status_completed', array($this, 'get_completed_orders_infos'), 10, 1 );
        add_action( 'woocommerce_after_order_notes', 
            function ($check) { echo "this is a text after order notes";} 
        );
    }

    public static function activate()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custplace';
        $sql = "CREATE TABLE $table_name (
            id int unsigned NOT NULL AUTO_INCREMENT,
            id_order int unsigned NOT NULL,
            date_order date NOT NULL,
            status_order varchar(255) NOT NULL,
            PRIMARY KEY  (id)
            );";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Clear the permalinks after the post type has been registered.
	    flush_rewrite_rules(); 
    }

    function add_admin_menu(  ) 
    { 
        add_menu_page( 'Custplace Page', 'Custplace',
        'manage_options', 'custplace', array($this, 'options_page') );
    }

    function settings_init(  ) 
    {
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
                function() { return ""; }, 
                'pluginPage'
            );
        }
            // Array contains the infos of the input fields
        $fields = array(
            array(
                'name'    => 'id_client',
                'label'   => 'ID client',
                'type'    => 'number',
                'section' => 'cp_pluginPage_section1',
                'description' => ""
            ),
            array(
                'name'    => 'cle_api',
                'label'   => 'Clé API',
                'type'    => 'password',
                'section' => 'cp_pluginPage_section1',
                'description' => ""
            ),
            array(
                'name'    => 'delai_sollicitation',
                'label'   => 'Delai de sollicitation',
                'type'    => 'number',
                'section' => 'cp_pluginPage_section2',
                'description' => "jours"
            ),
            array(
                'name'    => 'cel_widget',
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
                array($this, 'render_input_field'), 
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
            array($this, 'render_input_radio_field'), 
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
            array($this, 'render_input_radio_field'), 
            'pluginPage', 
            'cp_pluginPage_section3',
            array(
                'id'          => "cp_field3_section3",
                'description' => "Activer le widget sceau de confiance"
            )
        );

    }

        // callback function of "add_admin_menu" to render the settings page 
    function options_page(  ) 
    { 
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

            // the function to render the fields
    function render_input_field( $args ) 
    { 
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
    function render_input_radio_field( $args ) 
    {
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

        // get the order infos with the status completed 
    function get_completed_orders_infos( $order_id ) {
        $order = new WC_Order($order_id);

        $order_infos['order_ref'] = $order->get_id();
        $order_infos['lastname'] = $order->get_billing_last_name();
        $order_infos['firstname'] = $order->get_billing_first_name();
        $order_infos['email'] = $order->get_billing_email();
        $order_infos['products'] = array();

        foreach( $order->get_items() as $item_id => $item ) {
            // $product_id = $item->get_product_id();
            $product_name = $item->get_name();
            $product = $item->get_product();
            $item_sku = $product->get_sku();
            $product_link = $product->get_permalink();

            $order_infos['products'][] = array(
                'sku'   => $item_sku,
                'name'  => $product_name,
                'url'   => $product_link,
                'image' => $product_link
            );
        }
        

        // insert data in the database before sending them via the API
        global $wpdb; 
        
        $table_name =  $wpdb->prefix . 'custplace';
        $id_order = $order_infos['order_ref'];
        $date = date('Y-m-d');        

        $wpdb->query( "INSERT INTO $table_name(id_order, date_order, status_order) VALUES( '$id_order', '$date', 'pending')" );
         
        

        // create "CustplaceApi" object to send order data to the web server
        require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/CustplaceApi.php';
        $custplace_api_obj = new CustplaceApi();

        $options = get_option( 'custp_settings' );        
        $order_infos['order_date'] = date('d/m/Y');
        $response = $custplace_api_obj->save($order_infos, $options['id_client'], $options['cle_api']);
        
        $status_order = $response == "success"? "OK" : "KO";
         
        $wpdb->query("UPDATE $table_name SET status_order = '$status_order' WHERE id_order = '$id_order'"); 
        
        // var_dump($response); die();
    }                       

}   

$custplace_obj = new Custplace();
