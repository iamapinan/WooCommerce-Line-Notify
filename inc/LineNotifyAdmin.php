<?php
/**
 *  Admin
 * 
 * Package: woo-line-notify
 * (c) Apinan Woratrakun (iOTech Enterprise Co.,Ltd.) <apinan@iotech.co.th>
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LineNotifyAdmin {
    var $plugin_data;
    var $author;
    var $options;
    var $pattern;
    var $default_pattern;
    var $dynamic_fields;
    var $notify_msg = null;
    var $actions;

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
            "[order_postcode]",
            "[order_url]",
            "[products]"
        ];

        $this->dynamic_fields = [
            "[shipping *]",
            "[billing *]",
            "[customer *]",
            "[order *]",
            "[meta *]"
        ];
        // Default message.
        $this->default_pattern = __("You got new order at [order_time] order number [order_id]\nFrom: [order_customer]\nStatus: [order_status]\nTotal:  [order_total] Payment method: [order_payment]\nAddress: [order_address]\n=== Products ===\n[products]\n\nCheck it now [order_url]\n-------------\n[order_note]", 'woo-line-notify');

        add_action( 'admin_menu', array($this, 'WlnMenu'));
        add_action( 'admin_init', array($this, 'Wln_Page_Init'));

        $this->actions = new Actions;
    }
    /**
     * Add admin menu.
     */
    public function WlnMenu() {
        // Admin menu.
        add_menu_page(
            $this->plugin_data['Name'], // page_title
            'Line Notify', // menu_title
            'manage_options', // capability
            $this->plugin_data['TextDomain'], // menu_slug
            array($this, 'Wln_admin_option'), // function
            'dashicons-format-status', // icon_url
            71 // position
        );
    }
    /**
     * Dynamic fields replacement
     */
    public function sanitize_dynamic($message, $order_id) {

        $post_data = get_post_meta( $order_id );

        // Customer
        preg_match('/\[customer+\s.*\]/', $message, $customer_); // Extract keyword
        preg_match('/\s[A-Za-z0-9_-]+/', $customer_[0], $customer_code); // Extract keyword requirement
        $customer_code = preg_replace('/\s+/', '', $customer_code); // Clean requirement
        $customer_data = $post_data['_billing_'.$customer_code[0]][0]; // Get data from requirement
        $message = str_replace($customer_, $customer_data, $message); // Replace data to requirement
        // Billing
        preg_match('/\[billing+\s.*\]/', $message, $billing_); // Extract keyword
        preg_match('/\s[A-Za-z0-9_-]+/', $billing_[0], $billing_code); // Extract keyword requirement
        $billing_code = preg_replace('/\s+/', '', $billing_code); // Clean requirement
        $billing_data = $post_data['_billing_'.$billing_code[0]][0]; // Get data from requirement
        $message = str_replace($billing_, $billing_data, $message); // Replace data to requirement
        // Shipping
        preg_match('/\[shipping+\s.*\]/', $message, $shipping_); // Extract keyword
        preg_match('/\s[A-Za-z0-9_-]+/', $shipping_[0], $shipping_code); // Extract keyword requirement
        $shipping_code = preg_replace('/\s+/', '', $shipping_code); // Clean requirement
        $shipping_data = $post_data['_shipping_'.$shipping_code[0]][0]; // Get data from requirement
        $message = str_replace($shipping_, $shipping_data, $message); // Replace data to requirement
        // Order
        preg_match('/\[order+\s.*\]/', $message, $order_); // Extract keyword
        preg_match('/\s[A-Za-z0-9_-]+/', $order_[0], $order_code); // Extract keyword requirement
        $order_code = preg_replace('/\s+/', '', $order_code); // Clean requirement
        $order_data = $post_data['_'.$order_code[0]][0]; // Get data from requirement
        $message = str_replace($order_, $order_data, $message); // Replace data to requirement
        // Custom meta
        preg_match('/\[meta+\s.*\]/', $message, $meta_); // Extract keyword
        preg_match('/\s[A-Za-z0-9_-]+/', $meta_[0], $meta_code); // Extract keyword requirement
        $meta_code = preg_replace('/\s+/', '', $meta_code); // Clean requirement
        $meta_data = get_post_meta($order_id, $meta_code[0], true); // Get data from requirement
        $message = str_replace($meta_, $meta_data, $message); // Replace data to requirement
        
        return $message;
    } 
    /**
     * Match and replace shortcode with data.
     */
    public function sanitize_code($message, $order_data) {

        // Generate order management url.
        $orderUri = get_admin_url(null, '/post.php?post='.$order_data['id'].'&action=edit');
        // Getting an instance of the WC_Order object from a defined ORDER ID
        $get_products = wc_get_order( $order_data['id'] ); 
        $product_line = '';

        // Iterating through each "line" items in the order
        foreach ($get_products->get_items() as $item_id => $item_data) {
            // Get an instance of corresponding the WC_Product object
            $product = $item_data->get_product();
            $product_name = $product->get_name(); // Get the product name
            $item_quantity = $item_data->get_quantity(); // Get the item quantity
            $item_total = $item_data->get_total(); // Get the item line total
            $product_line .= __('- Product: ', 'woo-line-notify') . $product_name . __(' Quantity: ', 'woo-line-notify') . $item_quantity . __(' Total: ', 'woo-line-notify') . number_format( $item_total, 2 ) . "\n";
        }

        $message = str_replace('[order_id]', $order_data['id'], $message);
        $message = str_replace('[order_status]', wc_get_order_status_name( 'wc-' . $order_data['status'] ), $message);
        $message = str_replace('[order_time]', $order_data['date_modified']->date('d/m/Y, H:i'), $message);
        $message = str_replace('[order_total]', number_format( $order_data['total'], 2), $message);
        $message = str_replace('[order_payment]', $order_data['payment_method_title'], $message);
        $message = str_replace('[order_customer]', $order_data['billing']['first_name'] .' '. $order_data['billing']['last_name'], $message);
        $message = str_replace('[order_address]', $order_data['billing']['address_1'] . ' ' . $order_data['billing']['address_2'] . ' ' . $order_data['billing']['city'] . ' ' . $this->actions->thai_states($order_data['billing']['state']) . ' ' . $order_data['billing']['postcode'] , $message);
        $message = str_replace('[order_phone]', $order_data['billing']['phone'], $message);
        $message = str_replace('[order_company]', $order_data['billing']['company'], $message);
        $message = str_replace('[order_postcode]', $order_data['billing']['postcode'], $message);
        $message = str_replace('[order_province]', $this->actions->thai_states($order_data['billing']['state']), $message);
        $message = str_replace('[order_note]', $order_data['customer_note'], $message);
        $message = str_replace('[order_url]', $orderUri, $message);
        $message = str_replace('[products]', $product_line, $message);

        return $message;
    } 
    /**
     * Message pattern improvement.
     */
    public function NotificationTrigger( $order_id ) {
        // Get order info.
        $order = wc_get_order( $order_id );
        $order_data = $order->get_data(); // The Order data
        
        // Get plugin options
        $get_option = get_option( '_option_notify' );
      
        // Check notify rule
        $availableStatus = json_decode( $get_option['status'] );
        if( in_array( 'wc-' . $order_data['status'], $availableStatus) ) {

            $data = $this->sanitize_code($get_option['pattern'], $order_data);
            $data = $this->sanitize_dynamic($data, $order_id);

            // Start sending
            $this->actions->send_notify($data);
        }
    }

    /**
     * Admin settings page output.
     */
    public function Wln_admin_option() {
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'notification_setting';
        wp_register_script( 'wln_src', plugins_url( 'src/src.js', __DIR__ ) );
        wp_enqueue_script('wln_src');
        ?>
        <style>
        input[type=text], 
        input[type=password],
        input[type=number] {
            height: 28px;
        }

        input[type=checkbox] {
            border-radius: 2px;
        }
        .active {
            background-color: #fff;
            color: #444;
        }
        </style>
        <div class="wlnWrap"> 
            <h2>
                <span class="wln-logo"><img src="<?php echo plugins_url( 'src/image/line-notify-logo.png', __DIR__ ); ?>" width="60"></span>
                <?php _e( 'Woocommerce Line Notify', 'woo-line-notify' );?> 
            </h2>
            <?php settings_errors(); ?>
            <p><?php _e( 'Send order update to Line.', 'woo-line-notify' );?></p>
            <h2 class="nav-tab-wrapper">
                <a href="?page=woo-line-notify&tab=notification_setting" class="nav-tab <?php echo ( $active_tab == 'notification_setting' ) ? 'active' : '';?>"><span class="dashicons dashicons-admin-comments"></span> <?php _e('Notification','woo-line-notify');?></a>

                <a href="?page=woo-line-notify&tab=debug" class="nav-tab <?php echo ( $active_tab == 'debug' ) ? 'active' : '';?>"><span class="dashicons dashicons-sos"></span> <?php _e('Debug','woo-line-notify');?></a>

                <a href="?page=woo-line-notify&tab=api" class="nav-tab <?php echo ( $active_tab == 'api' ) ? 'active' : '';?>"><span class="dashicons dashicons-admin-site-alt3"></span> <?php _e('API','woo-line-notify');?></a>

                <a href="?page=woo-line-notify&tab=about" class="nav-tab <?php echo ( $active_tab == 'about' ) ? 'active' : '';?>"><span class="dashicons dashicons-format-quote"></span> <?php _e('About','woo-line-notify');?></a>
            </h2>
            <form method="post" action="options.php" id="WooLineNotifySettings">
                <?php
                switch ( $active_tab ) {
                    case 'notification_setting':
                        ?><textarea id="_pattern_default" style="display: none;"><?php echo str_replace("\t", "", $this->default_pattern);?></textarea><?php
                        settings_fields( '_notification_group' );
                        do_settings_sections( 'notification_setting' );
                    break;
                    case 'debug':
                        settings_fields( '_debug_group' );
                        do_settings_sections( 'debug' );
                    break;
                    case 'api':
                        settings_fields( '_api_group' );
                        do_settings_sections( 'api' );
                    break;
                    case 'about':
                        settings_fields( '_about_group' );
                        do_settings_sections( 'about' );
                    break;

                }
                
                ?>
                <table class="form-table">
                    <tr>
                    <th scope='row'></th>
                    <td><?php submit_button();?></td>
                    </tr>
                </table>
            </form>
            
        </div>
    <?php }
    
    /**
     * Setting fields setup and stylesheet register.
     */
    public function Wln_Page_Init() {
        
        if(isset( $_POST['api-endpoint'] )) {
            $sanitary_values['endpoint'] = sanitize_text_field( $_POST['api-endpoint'] );
            // Update API Endpoint
            update_option('wln-api-endpoint', $sanitary_values['endpoint']);
        }

        // Notification setting
        register_setting(
            '_notification_group', // option_group
            '_option_notify', // option_name
            array( $this, 'noti_sanitize' ) // sanitize_callback
        );
        add_settings_section(
            '_setting_section', // id
            __( 'Notification settings','woo-line-notify' ), // title
            array( $this, '_section_info' ), // callback
            'notification_setting' // page
        );

        // Debug setting
        register_setting(
            '_debug_group', // option_group
            '_option_debug' // option_name
        );
        add_settings_section(
            '_debug_section', // id
            __( 'Debug','woo-line-notify' ), // title
            array( $this, '_section_info' ), // callback
            'debug' // page
        );

        // API setting
        register_setting(
            '_api_group', // option_group
            'wln-api-key' // option_name
        );
        add_settings_section(
            '_api_section', // id
            __( 'API','woo-line-notify' ), // title
            array( $this, '_section_info' ), // callback
            'api' // page
        );

        // About
        register_setting(
            '_about_group', // option_group
            '_option_notify' // option_name
            // array( $this, '_sanitize' ) // sanitize_callback
        );
        add_settings_section(
            '_info_section', // id
            __( 'About','woo-line-notify' ), // title
            array( $this, '_section_info' ), // callback
            'about' // page
        );
    }

    public function _section_info() {
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'notification_setting';

        switch ( $active_tab ) {
            case 'notification_setting':
                $this->options = get_option( '_option_notify' );
                echo __('Order notification setting','woo-line-notify');
                add_settings_field(
                    'endpoint', // id
                    __( 'Line Notify API','woo-line-notify' ), // title
                    array( $this, 'endpoint_callback' ), // callback
                    'notification_setting', // page
                    '_setting_section' // section
                );

                add_settings_field(
                    'token', // id
                    __( 'Line Notify Token','woo-line-notify' ), // title
                    array( $this, 'token_callback' ), // callback
                    'notification_setting', // page
                    '_setting_section' // section
                );
            
                add_settings_field(
                    'pattern', // id
                    __( 'Message Pattern','woo-line-notify' ), // title
                    array( $this, 'pattern_callback' ), // callback
                    'notification_setting', // page
                    '_setting_section' // section
                );
            
                add_settings_field(
                    'status', // id
                    __( 'Order status to notify','woo-line-notify' ), // title
                    array( $this, 'order_status_callback' ), // callback
                    'notification_setting', // page
                    '_setting_section' // section
                );
            
                add_settings_field(
                    'timezone', // id
                    __( 'Time zone setting','woo-line-notify' ), // title
                    array( $this, 'timezone_callback' ), // callback
                    'notification_setting', // page
                    '_setting_section' // section
                );
            break;
            case 'debug':
                echo __('Debug setting');
                add_settings_field(
                    'debug', // id
                    __( 'Debug','woo-line-notify' ), // title
                    array( $this, 'debug_callback' ), // callback
                    'debug', // page
                    '_debug_section' // section
                );
            break;
            case 'api':
                echo __('API Setting');
                add_settings_field(
                    'api', // id
                    __( 'API Key','woo-line-notify' ), // title
                    array( $this, 'api_callback' ), // callback
                    'api', // page
                    '_api_section' // section
                );
            break;
            case 'about':
                ?>
                    <p>
                    <strong><?php echo $this->plugin_data['Name'];?></strong><br>
                    <?php _e('Send woocommerce order notification to Line notify API.<br>You can customize message pattern and notify to your chat room or your chat group in your pattern.','woo-line-notify');?><br>
                    <?php _e( 'The new concept is not colorful but powerful and also free forever.','woo-line-notify' );?><br>
                    <?php _e( 'I removed the design and keep focus on features development.','woo-line-notify' );?>
                    </p>
                    <?php _e('
                    <p><strong>Features</strong></p>
                    <p>
                    1. Add line token.<br>
                    2. Message pattern with order short code supported.<br>
                    3. Send notify to line group or user.<br>
                    4. Send notify when have order activity.<br>
                    5. Multi language support.<br>
                    6. API to send message with basic authen security for developer.<br>
                    7. Static method to send message for developer.<br>
                    8. Debug mode option.<br>
                    9. Dynamic fields to unlock your need.<br>
                    10. Dashboard widget.<br>
                    11. Can use without Woocommerce.
                    </p>', 'woo-line-notify');?>
                
                    <p><strong><label for="term"><?php _e( 'Term and privacy','woo-line-notify' );?></label></strong></p>
                    <p>
                        To understand what we do with your data and compile with The EU General Data Protection Regulation (GDPR)<br>
                        This plugin is call to external service Line Messaging API it use to be send an order data such as <br>
                        Order Id, Order customer name, Order Total, Order Product, Order timestamp, Order payment method <br>
                        depend on your settings to your Line Messager account or Line group related with your Token ID.<br>
                        <br>
                        Line Notify Term<br>
                        Privacy policy rules of Line Messaging please read <a href="https://terms.line.me/line_rules?lang=en">https://terms.line.me/line_rules?lang=en</a>
                    </p>
                    <p><span class="dashicons dashicons-carrot"></span> <?php _e('To contribute this plugin please follow the <a href="https://git.iotech.co.th/iamapinan/woocommerce-line-notify#contibute-guidelines" target="_blank">contributing guide.</a>', 'woo-line-notify');?></p>
                    <p>
                    <strong>Developer</strong><br>
                    <img src="https://avatars0.githubusercontent.com/u/1413490?s=100" style="display: block;border-radius: 50%;max-width: 60px;margin: 20px auto 10px 30px;">
                    Apinan Woratrakun<br>CEO of <a href="https://iotech.co.th/" target="_blank">iOTech Enterprise</a>.
                    </p>
                    <p><strong>Version:</strong> <?php echo $this->plugin_data['Version'];?> <br/>
                    <a href="https://paypal.me/apinu" target="_blank"><span class="dashicons dashicons-heart"></span> Buy me a coffee?</a>
                    </p>
                    <style>.submit{display:none;}</style>
                <?php
            break;
            default:
                echo __('Order notification');
            break;

        }
    }

    public function debug_callback() {
        $option_ = get_option( '_option_debug' );
        $check = ( isset( $option_['debug'] ) ) ? 'checked' : '';
        $text = ( isset( $option_['debug'] ) ) ? __('Debug option is working.', 'woo-line-notify') : __('Click to enable debug option.', 'woo-line-notify');
        printf(
            "<input type='checkbox' $check name='_option_debug[debug]' id='debug' value='yes'> %s",
            $text
        );
        if( isset( $option_['debug'] ) ) {
            $degubLogs = (file_exists( WOO_LINE_NOTIFY_PATH . '/logs/debug.log' )) ? file_get_contents( WOO_LINE_NOTIFY_PATH . '/logs/debug.log' ) : '';
            printf( "<textarea class='large-text' readonly style='height: 300px;display: block;margin-top: 10px;background-color: #fff;'>%s</textarea>", $degubLogs );
        }
    }

    /**
     * Sanitize settings data before save it.
     */
    public function noti_sanitize($input) {
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
    
        return $sanitary_values;
    }

    /**
     * Sanitize settings data before save it.
     */
    public function debug_sanitize($input) {

        if ( isset( $input['debug'] ) ) {
            $sanitary_values['debug'] = $input['debug'];
        }
    
        return $sanitary_values;
    }
    
    public function api_callback() {
        $key = get_option( 'wln-api-key' );
        printf(
            '<input class="regular-text" type="text" name="wln-api-key" id="wln-api-key" value="%s" required>', esc_attr( $key )
        );
    }
    public function token_callback() {
        printf(
            '<input class="regular-text" type="password" name="_option_notify[token]" id="token" value="%s" required> <a href="https://notify-bot.line.me/my/" target="_blank" class="button button-secondary">' . __('Create Token', 'woo-line-notify') . '</a>',
            isset( $this->options['token'] ) ? esc_attr( $this->options['token']) : ''
        );
    }

    public function endpoint_callback() {
        $endpoint = get_option('wln-api-endpoint');
        printf(
            '<input class="regular-text" type="text" name="api-endpoint" id="api-endpoint" value="%s" required readonly>', $endpoint
        );
    }
    
    
    public function pattern_callback() {
        printf(
            '<textarea class="large-text" maxlength="500" placeholder="' . __('You have new order on', 'woo-line-notify') . ' [order_status]" rows="5" name="_option_notify[pattern]" id="pattern" style="max-width: 580px;height: 200px;">%s</textarea>
            <p>
                <a href="" class="button button-secondary" id="add_default_pattern">' . __('Use default value', 'woo-line-notify') . '</a>
            </p>
            <p>
            <strong>' . __('Shortcode', 'woo-line-notify') . '</strong> <br><span class="shortcodeBadge" style="max-width: 500px;display:block;"> 
                <a href="#" class="shortcode-code" style="text-decoration: none;">' . join('</a>
                <a href="#" class="shortcode-code" style="text-decoration: none;">', $this->patterns ) . '</a></span>
            </p>
            <p>
            <strong>' . __('Dynamic fields', 'woo-line-notify') . '</strong> <br><span class="shortcodeBadge" style="max-width: 500px;display:block;"> 
                ' . join(' ', $this->dynamic_fields ) . '</span>
            </p>
            ',
            isset( $this->options['pattern'] ) ? esc_attr( $this->options['pattern'] ) : ''
        );
    }
    
    public function timezone_callback() {
        $timezone_name =  ( get_option( 'gmt_offset' )>0 ) ? '+' . get_option( 'gmt_offset' ) : '-'. get_option( 'gmt_offset' );
        print('UTC' . $timezone_name . ' <a href="/wp-admin/options-general.php" style="text-decoration: none;"><span class="dashicons dashicons-clock"></span> '. __('Change your setting', 'woo-line-notify').'</a>');
    }
    
    public function order_status_callback() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            if( isset( $this->options['status'] ) ) {
                $statusSelected = json_decode( $this->options['status'] );
            }

            $orderStatus = wc_get_order_statuses();
            foreach($orderStatus as $sid => $sv) {
                $check = @(in_array($sid, $statusSelected)) ? 'checked' : '';
                echo "<p><span class='order_status_check'><input type='checkbox' name='_option_notify[status][]' $check value='$sid' id='status_$sid'> <label for='status_$sid'>$sv</label></span>&nbsp;&nbsp;</p>";
            }
        } else {
            echo __("<p>Please install and activate <a href='https://th.wordpress.org/plugins/woocommerce/'>Woocommerce</a> to use this feature.</p>", 'woo-line-notify');
        }
    }
}