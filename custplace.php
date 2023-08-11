<?php
    /*
    * Plugin Name: Custplace
    * Description: Custplace plugin test.
    */
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };
        

    if (! class_exists( 'Custplace' )) {

        class Custplace
        {
            function __construct()
            {
                // create the custplace page  
                add_action( 'admin_menu', array($this, 'add_admin_menu')  );
                add_action( 'admin_init', array($this, 'settings_init') );

                // hooks to manage an order on completed status 
                add_action( 'woocommerce_order_status_completed', array($this, 'get_completed_orders_infos'), 10, 1 );
                add_filter( 'woocommerce_order_actions', array($this, 'add_custplace_order_action'));
                add_action( 'woocommerce_order_action_custplace_order_action', array($this, 'custplace_order_action_function') );
                
                // display a table of completed order status after Being sent to Custplace API                
                add_action( 'add_meta_boxes', array( $this, 'custplace_order_status_meta_box') );
            }
        
            /**
             * This static method creates a table named "wp_custplace" on activation.
             *
             * @return void
             */
            public static function activate()
            {
                global $wpdb;
                $table_name = $wpdb->prefix . 'custplace';
                $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    id int unsigned NOT NULL AUTO_INCREMENT,
                    id_order int unsigned NOT NULL,
                    date_order date NOT NULL,
                    status_order varchar(255) NOT NULL,
                    PRIMARY KEY  (id)
                    );";
        
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
            }
        
            /**
             * Adds a top-level menu page named "Custplace" .
             * 
             * @return void
             */
            function add_admin_menu(  ) 
            { 
                add_menu_page( 'Custplace Page', 'Custplace',
                'manage_options', 'custplace', array($this, 'options_page') );
            }
        
            /**
             * Register "custp_settings" data in the option table.
             * 
             * Defines the sections and fields and adds them to the "pluginPage" option_group.
             * @return void
             */
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
        
                foreach( $sections as $section ) {
                    add_settings_section(
                        $section['id'], 
                        __( $section['title'], 'custplace_plugin' ), 
                        fn() => "", 
                        'pluginPage'
                    );
                } 
        
                /**
                 * The fields array contains the informations of the first four input fields.
                 * 
                 * field attributes : name, label, type, section, description.
                 * 
                 */ 
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
        
                /**
                 * add the radios fields to the third section .
                 */ 
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
        
            /**
             * Callback function of "add_menu_page" to render the Custplace plugin page .
             *
             * @return void
             */
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
        
            /**
             * Callback function to render the settings fields except the fields 
             * of type radio .
             *
             * @param   array   $args
             * @return  void
             */ 
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
        
            /**
             * Callback function to render the fields of type radio .
             *
             * @param   array   $args
             * @return  void
             */
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
        
            /**
             * Callback function fires when the order status is completed.
             * 
             * Get the order data of status completed and send them via an API  
             * to Custplace web server.
             * 
             * Order attributes : order_ref, lastname, firstname, email, order_date,
             *                    products(sku, name, url, image)
             * 
             * @param   integer  $order_id
             * @return void
             */ 
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
                
                /**
                 * Insert order data in the "wp_custplace" table before sending them to the web server .
                 * 
                 */
                global $wpdb; 
                
                $table_name =  $wpdb->prefix . 'custplace';
                $id_order = $order_infos['order_ref'];
                $date = $order->get_date_completed();
                $wpdb->query( "INSERT INTO $table_name(id_order, date_order, status_order) VALUES('$id_order', '$date', 'pending')" );
                 
        
                /**
                 * Create "CustplaceApi" object to send order data to the web server .
                 */ 
                require_once  __DIR__ . '/includes/CustplaceApi.php';
                $custplace_api_obj = new CustplaceApi();
        
                $options = get_option( 'custp_settings' );        
                $order_infos['order_date'] = $date->date('d/m/Y'); // format the $date variable to (dd/mm/yyyy) format 
        
                $response = $custplace_api_obj->send($order_infos, $options['id_client'], $options['cle_api']);
                
                /**
                 * Update the "status_order" in the "wp_custplace" table depending on
                 * the response of the web server .
                 */ 
                $status_order = $response == "success"? "OK" : "KO";
                 
                $wpdb->query("UPDATE $table_name SET status_order = '$status_order' WHERE id_order = '$id_order' AND status_order = 'pending'"); 
                
                // var_dump($response); die();
            } 
            
            /**
             * Callback function displays an action option on the metabox 
             * drop-down of the edit order page .
             *
             * @param   array    $actions
             * @return  array
             */
            function add_custplace_order_action( $actions )
            {
                global $theorder;
                
                if ( $theorder->get_status() != "completed" || !is_a( $theorder, 'WC_Order') ) {
                    return $actions;
                }
        
                $actions['custplace_order_action'] = __( "envoyer une sollicitation d'avis", 'custplace_plugin' );
                return $actions;
            }
        
            /**
             * Callback function fires when my custom order action is selected.
             *
             * @param  object   $order
             * @return void
             */
            function custplace_order_action_function( $order )
            {
                $this->get_completed_orders_infos( $order->id );
            }
        
        
            /**
             * Callback function to add a custom metabox that shows the "date_order" and "status_order"
             * of the "wp_custplace" table .
             * 
             * @return void
             */          
            function custplace_order_status_meta_box() 
            {
                add_meta_box( 
                    'custplace_custom_data_metabox',
                    __( 'Custplace Data Table', 'text-domain' ),
                    array($this, 'custplace_order_status'),
                    'shop_order',
                    'side',
                    'low' 
                );
            }
            /**
             * Callback function to display the content of the order custom metabox
             * in the single order edit page.
             * 
             * content : a table of two columns (Date, Status)
             *
             * @return void
             */
            function custplace_order_status() {

                // Check if we are on the order edit page
                if (get_post_type() === 'shop_order' && isset($_GET['post'])) {
                    $order_id = $_GET['post'];
                    
                    global $wpdb;
        
                    $table_name = $wpdb->prefix . 'custplace';
        
                    $query = "SELECT date_order, status_order FROM $table_name WHERE id_order = $order_id";
                    $results = $wpdb->get_results( $query, ARRAY_A );

                    echo '<table class="widefat fixed">';
                    echo '<thead><tr><th>Date</th><th>Status</th></tr></thead>';
                    echo '<tbody>';

                    foreach ( $results as $row ) {
                        echo '<tr>';
                        echo '<td>' . esc_html( $row['date_order'] ) . '</td>';
                        echo '<td>' . esc_html( $row['status_order'] ) . '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                                
                }

                
            }
        }           
    }

    $custplace_obj = new Custplace();
        
    register_activation_hook( __FILE__, array('Custplace' ,'activate') );
