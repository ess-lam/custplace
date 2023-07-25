<?php 
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    };

    /**
     * custom option and settings
     */
    function myplugin_settings_init() {
        // Register a new setting for "myplugin-settings" page.
        register_setting( 'myplugin-settings', 'myplugin_options' );

        // Register a new section in the "myplugin-settings" page.
        add_settings_section(
            'myplugin_section_developers',
            __( 'Section Title 1', 'myplugin-settings' ),
            'myplugin_section_developers_callback',
            'myplugin-settings'
        );

        // Register a new field in the "myplugin_section_developers" section, inside the "myplugin-settings" page.
        add_settings_field(
            'myplugin_field_first', // As of WP 4.6 this value is used only internally.
                                    // Use $args' label_for to populate the id inside the callback.
            __( 'First field', 'myplugin-settings' ),
            'myplugin_field_first_cb',
            'myplugin-settings',
            'myplugin_section_developers',
            array(
                'label_for'         => 'myplugin_field_first',
                'class'             => 'myplugin_row',
                'myplugin_custom_data' => 'custom',
            )
        );
    }

    /**
     * Register our myplugin_settings_init to the admin_init action hook.
     */
    add_action( 'admin_init', 'myplugin_settings_init' );

    /**
     * Custom option and settings:
     *  - callback functions
     */


    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    function myplugin_section_developers_callback( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Section description 1', 'myplugin-settings' ); ?></p>
        <?php
    }

    /**
     * first field callback function.
     *
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    function myplugin_field_first_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'myplugin_options' );
        ?>
        <select
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['myplugin_custom_data'] ); ?>"
                name="myplugin_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
            <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'red color', 'myplugin-settings' ); ?>
            </option>
            <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'blue color', 'myplugin-settings' ); ?>
            </option>
        </select>
        <p class="description">
            <?php esc_html_e( 'Choose the blue color if you like the color of the sky.', 'myplugin-settings' ); ?>
        </p>
        <p class="description">
            <?php esc_html_e( 'Choose the red color if you like the color of the rose.', 'myplugin-settings' ); ?>
        </p>
        <?php
    }

    /**
     * Add the top level menu page.
     */
    function myplugin_settings_menu() {

        add_menu_page(
            __( 'Custplace Settings Page', 'my-plugin' ),
            __( 'Custplace Settings', 'my-plugin' ),
            'manage_options',
            'myplugin-settings',
            'myplugin_settings_page_html'
        );

    }
    add_action('admin_menu', 'myplugin_settings_menu');

    function myplugin_settings_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // add error/update messages

        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'myplugin_settings_messages', 'myplugin_settings_message', __( 'Settings Saved', 'myplugin-settings' ), 'updated' );
        }

        // show error/update messages
        settings_errors( 'myplugin_settings_messages' );
            ?>
            <div class="wrap">
            <h1>My Plugin Config Page</h1>
            <form method="post" action="options.php">
                <?php
                // output security fields for the registered setting "myplugin-settings"
                settings_fields( 'myplugin-settings' );
                // output setting sections and their fields
                // (sections are registered for "myplugin-settings", each field is registered to a specific section)
                do_settings_sections( 'myplugin-settings' );
                // output save settings button
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }