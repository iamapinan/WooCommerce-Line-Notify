<?php
/*
Plugin Name: Woocommerce Line Notify
Plugin URI:  https://github.com/iamapinan/wc_linenotify
Description: Woocommerce new order notify to line chat.
Version:     1.0.6
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
            $this->version  = '1.0.6';
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
                "[order_customer]",
                "[order_phone]",
                "[order_company]",
                "[order_note]",
                "[order_province]",
                "[order_url]",
                "[products]"
            ];
            $this->defualt_pattern = "\nเมื่อเวลา [order_time]คำสั่งซื้อหมายเลข [order_id]\nจาก [order_customer]\nมีสถานะ [order_status]\nจำนวนเงิน:  [order_total] ชำระโดย: [order_payment]\nที่อยู่จัดส่ง [order_address] [order_province]\n===สินค้าดังนี้===\n[products]\n-------------\n ตรวจสอบ: [order_url]\n[order_note]";

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
            delete_option('pattern');
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

                // Generate order management url.
                $orderUri = get_admin_url(null, '/post.php?post='.$order_id.'&action=edit');
                // Getting an instance of the WC_Order object from a defined ORDER ID
                $get_products = wc_get_order( $order_id ); 
                $product_line = '';

                // Iterating through each "line" items in the order
                foreach ($get_products->get_items() as $item_id => $item_data) {
                    // Get an instance of corresponding the WC_Product object
                    $product = $item_data->get_product();
                    $product_name = $product->get_name(); // Get the product name
                    $item_quantity = $item_data->get_quantity(); // Get the item quantity
                    $item_total = $item_data->get_total(); // Get the item line total
                    // Displaying this data (to check)
                    $product_line .= __('- Product: ').$product_name.' Quantity: '.$item_quantity.' Item total: '. number_format( $item_total, 2 );
                }

                $message = $this->wc_line_notify_options['pattern'];
                $message = str_replace('[order_id]', $order_id, $message);
                $message = str_replace('[order_status]', wc_get_order_status_name( 'wc-' . $the_data['status'] ), $message);
                $message = str_replace('[order_time]', $the_data['date_modified']->date('d/m/Y, H:i'), $message);
                $message = str_replace('[order_total]', $the_data['total'], $message);
                $message = str_replace('[order_payment]', $the_data['payment_method_title'], $message);
                $message = str_replace('[order_customer]', $the_data['billing']['first_name'] .' '. $the_data['billing']['last_name'], $message);
                $message = str_replace('[order_address]', $the_data['billing']['address_1'] . ' ' . $the_data['billing']['address_2'] . ' ' . $the_data['billing']['city'] , $message);
                $message = str_replace('[order_phone]', $the_data['billing']['phone'], $message);
                $message = str_replace('[order_company]', $the_data['billing']['company'], $message);
                $message = str_replace('[order_province]', $this->the_states($the_data['billing']['state']), $message);
                $message = str_replace('[order_note]', $the_data['customer_note'], $message);
                $message = str_replace('[order_url]', $orderUri, $message);
                $message = str_replace('[products]', $product_line, $message);
                $this->AlertText = $message;

                $this->SendNotify();
            }
        }

        /**
         * Change woocommerce state name to Thailand state.
         */
        public function the_states( $states ) {
            $states_set = array(
                'TH-81' => 'กระบี่',
                'TH-10' => 'กรุงเทพมหานคร',
                'TH-71' => 'กาญจนบุรี',
                'TH-46' => 'กาฬสินธุ์',
                'TH-62' => 'กำแพงเพชร',
                'TH-40' => 'ขอนแก่น',
                'TH-22' => 'จันทบุรี',
                'TH-24' => 'ฉะเชิงเทรา',
                'TH-20' => 'ชลบุรี',
                'TH-18' => 'ชัยนาท',
                'TH-36' => 'ชัยภูมิ',
                'TH-86' => 'ชุมพร',
                'TH-57' => 'เชียงราย',
                'TH-50' => 'เชียงใหม่',
                'TH-92' => 'ตรัง',
                'TH-23' => 'ตราด',
                'TH-63' => 'ตาก',
                'TH-26' => 'นครนายก',
                'TH-73' => 'นครปฐม',
                'TH-48' => 'นครพนม',
                'TH-30' => 'นครราชสีมา',
                'TH-80' => 'นครศรีธรรมราช',
                'TH-60' => 'นครสวรรค์',
                'TH-12' => 'นนทบุรี',
                'TH-96' => 'นราธิวาส',
                'TH-55' => 'น่าน',
                'TH-38' => 'บึงกาฬ',
                'TH-31' => 'บุรีรัมย์',
                'TH-13' => 'ปทุมธานี',
                'TH-77' => 'ประจวบคีรีขันธ์',
                'TH-25' => 'ปราจีนบุรี',
                'TH-94' => 'ปัตตานี',
                'TH-14' => 'พระนครศรีอยุธยา',
                'TH-56' => 'พะเยา',
                'TH-82' => 'พังงา',
                'TH-93' => 'พัทลุง',
                'TH-66' => 'พิจิตร',
                'TH-65' => 'พิษณุโลก',
                'TH-76' => 'เพชรบุรี',
                'TH-67' => 'เพชรบูรณ์',
                'TH-54' => 'แพร่',
                'TH-83' => 'ภูเก็ต',
                'TH-44' => 'มหาสารคาม',
                'TH-49' => 'มุกดาหาร',
                'TH-58' => 'แม่ฮ่องสอน',
                'TH-35' => 'ยโสธร',
                'TH-95' => 'ยะลา',
                'TH-45' => 'ร้อยเอ็ด',
                'TH-85' => 'ระนอง',
                'TH-21' => 'ระยอง',
                'TH-70' => 'ราชบุรี',
                'TH-16' => 'ลพบุรี',
                'TH-52' => 'ลำปาง',
                'TH-51' => 'ลำพูน',
                'TH-42' => 'เลย',
                'TH-33' => 'ศรีสะเกษ',
                'TH-47' => 'สกลนคร',
                'TH-90' => 'สงขลา',
                'TH-91' => 'สตูล',
                'TH-11' => 'สมุทรปราการ',
                'TH-75' => 'สมุทรสงคราม',
                'TH-74' => 'สมุทรสาคร',
                'TH-27' => 'สระแก้ว',
                'TH-19' => 'สระบุรี',
                'TH-17' => 'สิงห์บุรี',
                'TH-64' => 'สุโขทัย',
                'TH-72' => 'สุพรรณบุรี',
                'TH-84' => 'สุราษฎร์ธานี',
                'TH-32' => 'สุรินทร์',
                'TH-43' => 'หนองคาย',
                'TH-39' => 'หนองบัวลำภู',
                'TH-15' => 'อ่างทอง',
                'TH-37' => 'อำนาจเจริญ',
                'TH-41' => 'อุดรธานี',
                'TH-53' => 'อุตรดิตถ์',
                'TH-61' => 'อุทัยธานี',
                'TH-34' => 'อุบลราชธานี'
            );
            return $states_set[$states];
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
                'Line Notify', // menu_title
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
                <img src="<?php echo WP_PLUGIN_URL . '/woocommerce-line-notify/assets/banner-772x250.png'; ?>">
                
                <?php 
                    settings_errors();
                ?>
                <textarea id="_pattern_default" style="display: none;"><?php echo $this->defualt_pattern;?></textarea>
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
            wp_register_script( 'wcl_src', plugin_dir_url( __FILE__ ) . 'src/src.js' );
            wp_enqueue_style('wcl_style');
            wp_enqueue_script('wcl_src');

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

            // add_settings_field(
            //     'image', // id
            //     'Add your image url [optional]', // title
            //     array( $this, 'image_callback' ), // callback
            //     'notify-admin', // page
            //     '_setting_section' // section
            // );

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
                '<input class="regular-text" type="text" name="_option_name[token]" id="token" value="%s" required> <a href="https://notify-bot.line.me/my/" target="_blank" class="button button-secondary">Create line token</a>',
                isset( $this->wc_line_notify_options['token'] ) ? esc_attr( $this->wc_line_notify_options['token']) : ''
            );
        }

        public function image_callback() {
            printf(
                '<input class="regular-text" type="text" name="_option_name[image]" placeholder="https://..." id="image" value="%s"> <span class="textReq">Support jpg, png Max size: 1024x1024px</span>',
                isset( $this->wc_line_notify_options['image'] ) ? esc_attr( $this->wc_line_notify_options['image']) : ''
            );
        }

        public function pattern_callback() {
            printf(
                '<textarea class="large-text" maxlength="500" placeholder="คุณมีคำสั่งซื้ออยู่ในสถานะ [order_status]" rows="5" name="_option_name[pattern]" id="pattern" style="max-width: 580px;">%s</textarea><p><a href="" class="button button-secondary" id="add_default_pattern">ใช้ค่าเริ่มต้น</a></p><p>Max charactor length <span class="textReq">800</span><p> <p>Shortcode: <span class="shortcodeBadge"> <a href="#" class="shortcode-code">' . join('</a><a href="#" class="shortcode-code">', $this->patterns) . '</span></p>',
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

                $isCheck = @(in_array($sid, $statusSelected)) ? 'checked' : '';
                echo "<span class='order_status_check'><input type='checkbox' name='_option_name[status][]' $isCheck value='$sid' id='status_$sid'> $sv</span>&nbsp;&nbsp;";
            }
        }

    }

    // Start service.
    $wc_notify = new wc_linenotify();
}

