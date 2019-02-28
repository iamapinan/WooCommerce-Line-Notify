<?php
/**
 *  Admin
 * 
 * Package: woo-line-notify
 * (c) Apinan Woratrakun (iOTech Enterprise Co.,Ltd.) <apinan@iotech.co.th>
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// Load extra functions.
define('WLN_API_END_POINT', 'https://notify-api.line.me/api/notify');
include_once( WOO_LINE_NOTIFY_PATH . 'inc/Extras.php' );
class Admin {
    var $plugin_data;
    var $author;
    var $options;
    var $pattern;
    var $default_pattern;
    var $notify_msg = null;

    public function __construct() {
        $this->plugin_data = get_plugin_data( WOO_LINE_NOTIFY_PATH . 'woo-line-notify.php' );
        $this->author = '<a href="' . $this->plugin_data['AuthorURI'] . '" target="_blank">' . $this->plugin_data['Author'] . '</a>';
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
        $this->default_pattern = "\nเมื่อเวลา [order_time] คำสั่งซื้อหมายเลข [order_id]\nจาก [order_customer]\nมีสถานะ [order_status]\nจำนวนเงิน:  [order_total] ชำระโดย: [order_payment]\nที่อยู่จัดส่ง [order_address] [order_province]\n===สินค้าดังนี้===\n[products]\n-------------\n ตรวจสอบ: [order_url]\n[order_note]";
    }
    /**
     * Add admin menu.
     */
    public function menu() {
        // Admin menu.
        add_menu_page(
            $this->plugin_data['Name'], // page_title
            'Line Notify', // menu_title
            'manage_options', // capability
            $this->plugin_data['TextDomain'], // menu_slug
            array($this, 'wln_admin_page'), // function
            'dashicons-admin-comments', // icon_url
            71 // position
        );
    }
    /**
     * Message pattern improvement.
     */
    public function SendAlert ( $order_id ) {
        // Get order info.
        $order = wc_get_order( $order_id );
        $the_data = $order->get_data(); // The Order data
    
        // Get plugin options
        $get_option = get_option( '_option_name' );
        
        // Check notify rule
        $availableStatus = json_decode( $get_option['status'] );
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
                $product_line .= __('+ Product: ', 'woo-line-notify') . $product_name . __(' Quantity: ', 'woo-line-notify') . $item_quantity . __(' Total: ', 'woo-line-notify') . number_format( $item_total, 2 ) . "\n";
            }
    
            $message = $get_option['pattern'];
            $message = str_replace('[order_id]', $order_id, $message);
            $message = str_replace('[order_status]', wc_get_order_status_name( 'wc-' . $the_data['status'] ), $message);
            $message = str_replace('[order_time]', $the_data['date_modified']->date('d/m/Y, H:i'), $message);
            $message = str_replace('[order_total]', number_format( $the_data['total'], 2), $message);
            $message = str_replace('[order_payment]', $the_data['payment_method_title'], $message);
            $message = str_replace('[order_customer]', $the_data['billing']['first_name'] .' '. $the_data['billing']['last_name'], $message);
            $message = str_replace('[order_address]', $the_data['billing']['address_1'] . ' ' . $the_data['billing']['address_2'] . ' ' . $the_data['billing']['city'] , $message);
            $message = str_replace('[order_phone]', $the_data['billing']['phone'], $message);
            $message = str_replace('[order_company]', $the_data['billing']['company'], $message);
            $message = str_replace('[order_province]', Extras::thai_states($the_data['billing']['state']), $message);
            $message = str_replace('[order_note]', $the_data['customer_note'], $message);
            $message = str_replace('[order_url]', $orderUri, $message);
            $message = str_replace('[products]', $product_line, $message);
            
            if(isset( $get_option['debug'] )) {
                // Loging output
                file_put_contents( WOO_LINE_NOTIFY_PATH .'logs/debug.log', $message );
                file_put_contents( WOO_LINE_NOTIFY_PATH .'logs/debug.log', "===================================\n", FILE_APPEND );
                file_put_contents( WOO_LINE_NOTIFY_PATH .'logs/debug.log', json_encode( Admin::SendNotify($message) ) . "\n\n", FILE_APPEND );
            } else {
                Admin::SendNotify($message);
            }
        }
    }
    
    /**
     * Send notify action.
     */
    public static function SendNotify($msg) {
        // Get plugin options
        $get_option = get_option( '_option_name' );
    
        // Header setup
        $headers = array( 
                'Authorization' => 'Bearer '. $get_option['token'],
            );
        // Request prepare
        $args = array(
            'method' => 'POST',
            'timeout' => 45,
            'httpversion' => '1.0',
            'headers' => $headers,
            'body' => array( 
                'message' => $msg
            ),
        );
        // Post to line notify server.
        $response = wp_remote_post( WLN_API_END_POINT, $args );
        return $response;
    }
    
    /**
     * Admin settings page output.
     */
    public function wln_admin_page() {
        ?>
        
        <div class="wlnWrap"> 
            <h2>
                <span class="wln-logo"><img src="<?php echo plugins_url( 'src/image/line-notify-logo.png', __DIR__ ); ?>"></span>
                <?php _e( 'Woocommerce Line Notify', 'woo-line-notify' );?>
            </h2>
            <?php 
                settings_errors();
            ?>
            <p><?php _e( 'Send order update to line notify.','woo-line-notify' );?></p>
            <textarea id="_pattern_default" style="display: none;"><?php echo $this->default_pattern;?></textarea>
            <form method="post" action="options.php" id="WooLineNotifySettings">
                <?php
                    settings_fields( '_option_group' );
                    do_settings_sections( 'notify-admin' );
                    submit_button();
                ?>
            </form>
            <p><strong>Version:</strong> <?php echo $this->plugin_data['Version'];?> <strong>Created by:</strong> <?php echo $this->author;?></p>
        </div>
    <?php }
    
    /**
     * Setting fields setup and stylesheet register.
     */
    public function wc_line_notify_page_init() {
        // Get plugin options
        $this->options = get_option( '_option_name' );
    
        wp_register_style( 'wln_style', plugins_url( 'src/wc_linenotify_style.css', __DIR__ ) );
        wp_register_script( 'wln_src', plugins_url( 'src/src.js', __DIR__ ) );
        wp_enqueue_style('wln_style');
        wp_enqueue_script('wln_src');
    
        register_setting(
            '_option_group', // option_group
            '_option_name', // option_name
            array( $this, '_sanitize' ) // sanitize_callback
        );
    
        add_settings_section(
            '_setting_section', // id
            __( 'Settings','woo-line-notify' ), // title
            array( $this, '_section_info' ), // callback
            'notify-admin' // page
        );
    
        add_settings_field(
            'token', // id
            __( 'Line Notify Token','woo-line-notify' ), // title
            array( $this, 'token_callback' ), // callback
            'notify-admin', // page
            '_setting_section' // section
        );
    
        add_settings_field(
            'pattern', // id
            __( 'Message Pattern','woo-line-notify' ), // title
            array( $this, 'pattern_callback' ), // callback
            'notify-admin', // page
            '_setting_section' // section
        );
    
        add_settings_field(
            'status', // id
            __( 'Order status to notify','woo-line-notify' ), // title
            array( $this, 'order_status_callback' ), // callback
            'notify-admin', // page
            '_setting_section' // section
        );
    
        add_settings_field(
            'timezone', // id
            __( 'Time zone setting','woo-line-notify' ), // title
            array( $this, 'timezone_callback' ), // callback
            'notify-admin', // page
            '_setting_section' // section
        );

        add_settings_field(
            'debug', // id
            __( 'Debug','woo-line-notify' ), // title
            array( $this, 'debug_callback' ), // callback
            'notify-admin', // page
            '_setting_section' // section
        );
        
    }

    public function _section_info() {
        
    }

    public function debug_callback() {
        $check = ( isset( $this->options['debug'] ) ) ? 'checked' : '';
        $text = ( isset( $this->options['debug'] ) ) ? '<a href="'. plugins_url( 'logs/debug.log', __DIR__ ) .'" target="_blank">' . __('View debug log.', 'woo-line-notify') . '</a>' : __('Click to enable debug option.', 'woo-line-notify');
        printf(
            "<input type='checkbox' $check name='_option_name[debug]' id='debug' value='yes'> %s",
            $text
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
    
        if ( isset( $input['pattern'] ) ) {
            $sanitary_values['pattern'] = esc_textarea( $input['pattern'] );
        }
    
        if ( isset( $input['status'] ) ) {
            $sanitary_values['status'] = json_encode($input['status']);
        }

        if ( isset( $input['debug'] ) ) {
            $sanitary_values['debug'] = json_encode($input['debug']);
        }
    
        return $sanitary_values;
    }
    
    
    public function token_callback() {
        printf(
            '<input class="regular-text" type="password" name="_option_name[token]" id="token" value="%s" required> <a href="https://notify-bot.line.me/my/" target="_blank" class="button button-secondary">' . __('Create Token', 'woo-line-notify') . '</a>',
            isset( $this->options['token'] ) ? esc_attr( $this->options['token']) : ''
        );
    }
    
    
    public function pattern_callback() {
        printf(
            '<textarea class="large-text" maxlength="500" placeholder="' . __('You have new order on', 'woo-line-notify') . ' [order_status]" rows="5" name="_option_name[pattern]" id="pattern" style="max-width: 580px;">%s</textarea>
            <p>
                <a href="" class="button button-secondary" id="add_default_pattern">' . __('Use default value', 'woo-line-notify') . '</a>
            </p>
            <p>' . __('Line API charactors limit', 'woo-line-notify') . ': <span class="textReq">1000</span></p> 
            <p>
                ' . __('Shortcode', 'woo-line-notify') . ': <span class="shortcodeBadge"> 
                <a href="#" class="shortcode-code">' . join('</a>
                <a href="#" class="shortcode-code">', $this->patterns ) . '</span>
            </p>',
            isset( $this->options['pattern'] ) ? esc_attr( $this->options['pattern'] ) : ''
        );
    }
    
    public function timezone_callback() {
        $timezone_name =  ( get_option( 'gmt_offset' )>0 ) ? '+' . get_option( 'gmt_offset' ) : '-'. get_option( 'gmt_offset' );
        print('UTC' . $timezone_name . ' <a href="/wp-admin/options-general.php"> '. __('Change your setting', 'woo-line-notify').'</a>');
    }
    
    public function order_status_callback() {
        $statusSelected = json_decode( $this->options['status'] );
    
        $orderStatus = wc_get_order_statuses();
        foreach($orderStatus as $sid => $sv) {
            $check = @(in_array($sid, $statusSelected)) ? 'checked' : '';
            echo "<span class='order_status_check'><input type='checkbox' name='_option_name[status][]' $check value='$sid' id='status_$sid'> $sv</span>&nbsp;&nbsp;";
        }
    }
}