<?php
/*
Plugin Name: Woocommerce Line Notify
Plugin URI:  https://github.com/iamapinan/wc_linenotify
Description: Woocommerce new order notify to line chat.
Version:     1.0.4
Author:      Apinan Woratrakun
Author URI:  https://iotech.co.th
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
            $this->version  = '1.0.4';
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

            $this->AlertText = null;
            // System initial.
            $this->init();
        }
        /**
         * Initial main process.
         */
        function init() {
            // Trigger when order status changes.
            add_action( 'woocommerce_order_status_changed', array( $this, 'SendAlert'), 10, 2 );

            // Initial plugin and plugin events.
            add_action( 'admin_menu', array( $this, 'wc_line_notify_add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'wc_line_notify_page_init' ) );
        }

        /**
         * Message pattern improvement.
         */
        function SendAlert ( $order_id ) {
            // Get order info.
            $order = wc_get_order( $order_id );
            $the_data = $order->get_data(); // The Order data

            // Get plugin options
            $this->wc_line_notify_options = get_option( '_option_name' );
            
            // Check notify rule
            $availableStatus = json_decode( $this->wc_line_notify_options['status'] );
            if( in_array( 'wc-' . $the_data['status'], $availableStatus) ) {

                // Pattern convert.
                $message = $this->wc_line_notify_options['pattern'];
                $message = str_replace('[order_id]', $order_id, $message);
                $message = str_replace('[order_status]', $the_data['status'], $message);
                $message = str_replace('[order_time]', $the_data['date_modified']->date('d/m/Y, H:i'), $message);
                $message = str_replace('[order_total]', $the_data['total'], $message);
                $message = str_replace('[order_payment]', $the_data['payment_method_title'], $message);
                $message = str_replace('[order_customer]', $the_data['billing']['first_name'] .' '. $the_data['billing']['last_name'], $message);
                $message = str_replace('[order_address]', $the_data['billing']['address_1'] . ' ' . $the_data['billing']['address_2'] . ' ' . $the_data['billing']['city'] , $message);
                $this->AlertText = $message;
                $this->SendNotify();
            }
        }

        /**
         * Send notify action.
         */
        public function SendNotify() {
            // Get plugin options
            $this->wc_line_notify_options = get_option( '_option_name' );

            // Header setup
            $headers = array( 
                    'Authorization' => 'Bearer '.$this->wc_line_notify_options['token'],
                );
            // Request prepare
            $args = array(
                'method' => 'POST',
                'timeout' => 45,
                'httpversion' => '1.0',
                'headers' => $headers,
                'body' => array( 
                    'message' => $this->AlertText, 
                    'imageFullsize' => (!empty($this->wc_line_notify_options['image'])) ? $this->wc_line_notify_options['image'] : '', 
                    'imageThumbnail' => (!empty($this->wc_line_notify_options['image'])) ? $this->wc_line_notify_options['image'] : ''
                ),
            );
            // Post to line notify server.
            $response = wp_remote_post( $this->notify_api_endpoint, $args );
            return $response;
            // Debug here.
            // if ( is_wp_error( $response ) ) {
            //     $error_message = $response->get_error_message();
            //     echo "Something went wrong: $error_message";
            //  } else {
            //     echo 'Response:<pre>';
            //     print_r( $args );
            //     // print_r( $response );
            //     echo '</pre>';
            //  }
        }
        
        /**
         * Initial admin menu page.
         */
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

        /**
         * Admin settings page output.
         */
        public function _admin_page() {
            ?>

            <div class="wrap">
                <h2>Woocommerce Line Notify</h2>
                <p>Send order update to line notify.</p>
                <img src="<?php echo WP_PLUGIN_URL . '/wc_linenotify/src/image/wc_line.png'; ?>">
                
                <?php 
                    settings_errors();
                ?>
                
                <form method="post" action="options.php" id="WCLineNotifySettings">
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

        /**
         * Setting fields setup and stylesheet register.
         */
        public function wc_line_notify_page_init() {
            // Get plugin options
            $this->wc_line_notify_options = get_option( '_option_name' );

            wp_register_style( 'wcl_style', plugin_dir_url( __FILE__ ) . 'src/wc_linenotify_style.css' );
            wp_enqueue_style('wcl_style');

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

            add_settings_field(
                'image', // id
                'Add your image url', // title
                array( $this, 'image_callback' ), // callback
                'notify-admin', // page
                '_setting_section' // section
            );

            add_settings_field(
                'status', // id
                'Notify me only', // title
                array( $this, 'order_status_callback' ), // callback
                'notify-admin', // page
                '_setting_section' // section
            );

            add_settings_field(
                'timezone', // id
                'Time zone setting', // title
                array( $this, 'timezone_callback' ), // callback
                'notify-admin', // page
                '_setting_section' // section
            );
            
        }

        /**
         * Sanitize settings data before save it.
         */
        public function _sanitize($input) {
            $sanitary_values = array();
            if ( isset( $input['token'] ) ) {
                $sanitary_values['token'] = sanitize_text_field( $input['token'] );
            }

            if ( isset( $input['image'] ) ) {
                $sanitary_values['image'] = sanitize_text_field( $input['image'] );
            }

            if ( isset( $input['pattern'] ) ) {
                $sanitary_values['pattern'] = esc_textarea( $input['pattern'] );
            }

            if ( isset( $input['status'] ) ) {
                $sanitary_values['status'] = json_encode($input['status']);
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

        public function image_callback() {
            printf(
                '<input class="regular-text" type="text" name="_option_name[image]" placeholder="https://..." id="image" value="%s" required> <span class="textReq">Support jpg, png Max size: 1024x1024px</span>',
                isset( $this->wc_line_notify_options['image'] ) ? esc_attr( $this->wc_line_notify_options['image']) : ''
            );
        }

        public function pattern_callback() {
            printf(
                '<textarea class="large-text" maxlength="500" placeholder="คุณมีคำสั่งซื้ออยู่ในสถานะ [order_status]" rows="5" name="_option_name[pattern]" id="pattern" style="max-width: 580px;">%s</textarea><br>Max charactor length <span class="textReq">500</span><br> Supported short code: <span class="shortcodeBadge">' . join(', ', $this->patterns) . '</span>',
                isset( $this->wc_line_notify_options['pattern'] ) ? esc_attr( $this->wc_line_notify_options['pattern']) : ''
            );
        }

        public function timezone_callback() {
            $timezone_name =  (get_option( 'gmt_offset' )>0 ) ? '+' . get_option( 'gmt_offset' ) : '-'. get_option( 'gmt_offset' );
            echo 'UTC' . $timezone_name . ' <a href="/wp-admin/options-general.php">Change your setting</a>';
        }

        public function order_status_callback() {
            $statusSelected = json_decode( $this->wc_line_notify_options['status'] );

            $orderStatus = wc_get_order_statuses();
            foreach($orderStatus as $sid => $sv) {

                $isCheck = (in_array($sid, $statusSelected)) ? 'checked' : '';
                echo "<span class='order_status_check'><input type='checkbox' name='_option_name[status][]' $isCheck value='$sid' id='status_$sid'> $sv</span>&nbsp;&nbsp;";
            }
        }

    }

    // Start service.
    $wc_notify = new wc_linenotify();
}

