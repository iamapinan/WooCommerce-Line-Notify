<?php
/*
Plugin Name: Woocommerce Line Notify
Plugin URI:  https://github.com/iamapinan/wc_linenotify
Description: Woocommerce new order notify to line chat.
Version:     1.0.3
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
            $this->version  = '1.0.3';
            $this->author = '<a href="https://facebook.com/9apinan" target="_blank">Apinan Woratrakun</a>';
            $this->title = 'Woocommerce Line Notify';
            $this->notify_api_endpoint = 'https://notify-api.line.me/api/notify';
            $this->patterns = [
                "[order_status]",
                "[order_id]",
                "[order_time]",
                "[order_total]",
                "[order_payment]",
                "[order_address]",
                "[order_customer]"
            ];
            $this->AlertText = 'Null';
            $this->wc_line_notify_options = get_option( '_option_name' ); 
            $this->init();
        }

        function init() {
            add_action( 'woocommerce_order_status_changed', array( $this, 'SendAlert'), 10, 2 );

            // Initial plugin and plugin events.
            add_action( 'admin_menu', array( $this, 'wc_line_notify_add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'wc_line_notify_page_init' ) );
            

        }

        function SendAlert ( $order_id ) {
            $order = wc_get_order( $order_id );
            $the_data = $order->get_data(); // The Order data

            // Pattern convert.
            $message = $this->wc_line_notify_options['pattern'];
            $message = str_replace('[order_id]', $order_id, $message);
            $message = str_replace('[order_status]', $the_data['status'], $message);
            $message = str_replace('[order_time]', date('d/m/Y, H:i', $the_data['date_created']->getTimestamp()), $message);
            $message = str_replace('[order_total]', $the_data['total'], $message);
            $message = str_replace('[order_payment]', $the_data['payment_method_title'], $message);
            $message = str_replace('[order_customer]', $the_data['shipping']['first_name'] .' '. $the_data['shipping']['last_name'], $message);
            $message = str_replace('[order_address]', $the_data['billing']['address_1'] . ' ' . $the_data['billing']['address_2'] . ' ' . $the_data['billing']['city'] , $message);
            $this->AlertText = $message;
            $this->SendNotify();
        }

        public function SendNotify() {
            // Post to line notify server.
            $headers = array( 
                    'Authorization' => 'Bearer '.$this->wc_line_notify_options['token'],
                );

            $args = array(
                'method' => 'POST',
                'timeout' => 45,
                'httpversion' => '1.0',
                'headers' => $headers,
                'body' => array( 'message' => $this->AlertText ),
            );
            $response = wp_remote_post( $this->notify_api_endpoint, $args );
            // if ( is_wp_error( $response ) ) {
            //     $error_message = $response->get_error_message();
            //     echo "Something went wrong: $error_message";
            //  } else {
            //     echo 'Response:<pre>';
            //     print_r( $response );
            //     echo '</pre>';
            //  }
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

            ?>

            <div class="wrap">
                <h2>Woocommerce Line Notify</h2>
                <p>Send order update to line notify.</p>
                <img src="<?php echo WP_PLUGIN_URL . '/wc_linenotify/src/image/wc_line.png'; ?>">
                
                <?php 
                    settings_errors(); 
                    // $this->SendNotify('test');
                ?>
                
                <form method="post" action="options.php">
                    <?php
                        settings_fields( '_option_group' );
                        do_settings_sections( 'notify-admin' );
                        submit_button();
                        ?>
                </form>
                <p>Created by <?php echo $this->author;?></p>
                <p><b>Version: </b><?php echo $this->version;?><b> API Endpoint:</b> <?php echo $this->notify_api_endpoint;?></p>
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
                '<input class="regular-text" type="text" name="_option_name[token]" id="token" value="%s" required> <a href="https://notify-bot.line.me/my/" target="_blank">Create line token</a>',
                isset( $this->wc_line_notify_options['token'] ) ? esc_attr( $this->wc_line_notify_options['token']) : ''
            );
        }

        public function pattern_callback() {
            printf(
                '<textarea class="large-text" maxlength="500" placeholder="คุณมีคำสั่งซื้ออยู่ในสถานะ [order_status]" rows="5" name="_option_name[pattern]" id="pattern" style="max-width: 580px;">%s</textarea><br>Max charactor length 500<br> Supported short code: ' . join(', ', $this->patterns),
                isset( $this->wc_line_notify_options['pattern'] ) ? esc_attr( $this->wc_line_notify_options['pattern']) : ''
            );
        }

    }


    $wc_notify = new wc_linenotify();
}

