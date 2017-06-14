<?php
/*
Plugin Name: Woocommerce Line Notify
Plugin URI:  https://github.com/iamapinan/wc_linenotify
Description: Woocommerce new order notify to line chat.
Version:     1.0
Author:      Apinan Woratrakun
Author URI:  apinu.com
License:     GNU General Public License v3.0
License URI: https://github.com/iamapinan/wc_linenotify/blob/master/LICENSE
Text Domain: wc_linenotify
Domain Path: /languages
*/

// Not allow direct access to this file
defined ( 'ABSPATH' ) or die ( "No direct script access allowed." );

// Check if woocommerce is present in the system.
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // Create the class for plugin.
    class wc_linenotify {

        // Constructor for wc_linenotify class.
        // @access public
        // @return void
        public function __construct() {
            $this->id = 'wc-linenotify';
            $this->title = 'Woocommerce Line Notify';
            $this->notify_api_endpoint = 'https://notify-api.line.me/api/notify';
            $this->token = 'TAuixZoMjynNQyb8qX82TrPDHWwcuRHu6EnasiTiOut'; //For test only.

            $this->init();
        }

        function init() {
            // Initial plugin and plugin events.

            add_action( 'admin_menu', array( $this, 'wc_line_notify_add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'wc_line_notify_page_init' ) );
        }

        function SendNotify( $message ) {
            // Post to line notify server.
        }
        
        public function wc_line_notify_add_plugin_page() {
            add_menu_page(
                'Woocommerce Line Notify', // page_title
                'WC Line Notify', // menu_title
                'manage_options', // capability
                $this->id, // menu_slug
                array( $this, '_admin_page' ), // function
                'dashicons-admin-comments', // icon_url
                71 // position
            );
        }

        public function _admin_page() {
            $this->wc_line_notify_options = get_option( '_option_name' ); ?>

            <div class="wrap">
                <h2>Woocommerce Line Notify</h2>
                <p>Send order update to line notify.</p>
                
                <img src="<?php echo WP_PLUGIN_URL . '/wc_linenotify/src/image/wc_line.png'; ?>">
                
                <?php settings_errors(); ?>

                <form method="post" action="options.php">
                    <?php
                        settings_fields( '_option_group' );
                        do_settings_sections( 'notify-admin' );
                        submit_button();
                    ?>
                </form>
                <p>Created by Apinan Woratrakun</p>
            </div>
        <?php }

        public function wc_line_notify_page_init() {
            register_setting(
                '_option_group', // option_group
                '_option_name', // option_name
                array( $this, '_sanitize' ) // sanitize_callback
            );

            add_settings_section(
                '_setting_section', // id
                'Settings', // title
                array( $this, '_section_info' ), // callback
                'notify-admin' // page
            );

            add_settings_field(
                'token', // id
                'Line Notify Token', // title
                array( $this, 'token_callback' ), // callback
                'notify-admin', // page
                '_setting_section' // section
            );

            add_settings_field(
                'pattern', // id
                'Message Pattern', // title
                array( $this, 'pattern_callback' ), // callback
                'notify-admin', // page
                '_setting_section' // section
            );
        }

        public function _sanitize($input) {
            $sanitary_values = array();
            if ( isset( $input['token'] ) ) {
                $sanitary_values['token'] = sanitize_text_field( $input['token'] );
            }

            if ( isset( $input['pattern'] ) ) {
                $sanitary_values['pattern'] = esc_textarea( $input['pattern'] );
            }

            return $sanitary_values;
        }

        public function _section_info() {
            
        }

        public function token_callback() {
            printf(
                '<input class="regular-text" type="text" name="_option_name[token]" id="token" value="%s">',
                isset( $this->wc_line_notify_options['token'] ) ? esc_attr( $this->wc_line_notify_options['token']) : ''
            );
        }

        public function pattern_callback() {
            printf(
                '<textarea class="large-text" rows="5" name="_option_name[pattern]" id="pattern">%s</textarea>',
                isset( $this->wc_line_notify_options['pattern'] ) ? esc_attr( $this->wc_line_notify_options['pattern']) : ''
            );
        }

    }

    if ( is_admin() )
        $wc_notify = new wc_linenotify();
}

