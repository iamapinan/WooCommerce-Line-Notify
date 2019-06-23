<?php
/*
Plugin Name: Woocommerce Line Notify
Plugin URI: https://git.iotech.co.th/iamapinan/woocommerce-line-notify
Description: Woocommerce new order notify to line chat.
Version: 1.1.4
Author: Apinan Woratrakun
Author URI: https://facebook.com/9apinan
License: GNU General Public License v3.0
License URI: https://git.iotech.co.th/iamapinan/woocommerce-line-notify/blob/master/LICENSE
Text Domain: woo-line-notify
Domain Path: /languages
*/

// Not allow direct access to this file
defined ( 'ABSPATH' ) or die ( "No direct script access allowed." );

define( 'WOO_LINE_NOTIFY_PATH', plugin_dir_path( __FILE__ ) );

// Check get_plugin_data exists.
if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Load class
include_once( WOO_LINE_NOTIFY_PATH . 'inc/LineNotifyAdmin.php' );
include_once( WOO_LINE_NOTIFY_PATH . 'inc/Actions.php' );

if(!class_exists('WooLineNotify')){
    class WooLineNotify{
        var $admin;
        /**
         * Construction on plugin load.
         */
        public function __construct() {
            // Create instance
            $this->admin = new LineNotifyAdmin;
            $this->Trigger();
            // Activate action
            register_activation_hook( __FILE__, array( $this, 'install' ) );
            add_action( 'upgrader_process_complete', array( $this, 'install' ));
            add_action('rest_api_init', array($this, 'register_rest_api') );
            add_action('wp_dashboard_setup', array($this, 'dashboard_widget'));
        }

        public function register_rest_api() {
            register_rest_route( 'woo-line-notify/v1', '/notify',array(
                'methods'  => 'POST',
                'callback' => array( $this, 'wln_rest' )
            ));
        }
        /**
         * Trigger action when status changed.
         */
        public function Trigger() {
            // Check if woocommerce is present in the system.
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                // Trigger when order status changes.
                add_action( 'woocommerce_order_status_changed', array( $this->admin, 'NotificationTrigger' ) );
            }
        }
        /**
         * Static method to send Line notification from custom action.
         */
        public static function Send_Line_Notify ( $msg ) {
            $action = new Actions;
            $action->send_notify( $msg, 'static' );
        }

        /**
         * Intial default data.
         */
        public function install() {
            add_option( 'wln-api-endpoint', 'https://notify-api.line.me/api/notify' );
            add_option( 'wln-api-key', hash('md5', time()) );
        }
        /**
         * API Logic
         */
        public function wln_rest($request_data) {
            // Authorize api
            $user = $_SERVER['PHP_AUTH_USER'];
            $pass = $_SERVER['PHP_AUTH_PW'];
            $key = get_option( 'wln-api-key' );

            $validated = ( $user == $key && $pass == $key );
            if(!$validated) {
                header('WWW-Authenticate: Basic Realm="Login please"');
                return new WP_Error( 'Unauthorized', 'invalidate user or password', array('status' => 401) );
                exit;
            }

            $parameters = $request_data->get_params();
            $args = array(
                'msg' => $parameters['message']
            );

            $posts = get_posts($args);
            // Check if empty post
            if (empty($args['msg'])) {
                return new WP_Error( 'empty_parameter', 'There are invalid post data.', array('status' => 400) );
            }

            $action = new Actions;
            $resp = $action->send_notify( $args['msg'], 'api' );

            return $resp;

        }
        /**
         * Create dashboard widget.
         */
        public function dashboard_widget() {
            global $wp_meta_boxes;
            wp_add_dashboard_widget('wln-status', '<span class="dashicons dashicons-format-status"></span> Woocommerce Line Notify - Status', array($this, 'wln_statistic'));
        }
        /**
         * Dashboard widget render here.
         */
        public function wln_statistic() {
            $local = (int)get_option('wln_source_local');
            $api = (int)get_option('wln_source_api');
            $static = (int)get_option('wln_source_static');

            ?>
            <ul style="margin: 0 -12px -12px -12px !important;">				
                <li style="width: 100%;display: block;padding: 20px 0;font-size: 26px;text-align: center;color: #22ad59;border-bottom: 1px solid #ddd;">
                    <span class="dashicons dashicons-buddicons-groups"></span> <?php echo $local+$api+$static;?> Request
                    <br><small style="font-size: 12px;color: #aaa;">Total successfully request</small><br>
                    <a href="<?php echo admin_url('admin.php?page=woo-line-notify');?>" style="color: #8f9291;font-size: 16px;vertical-align: top;margin-top: 10px;display: block;font-weight: bold;"><span class="dashicons dashicons-admin-generic"></span> Settings</a>
				</li>
                <li style="width: 32%;display: inline-block;padding: 10px 0;font-size: 22px;text-align: center;color: #dc46b5;border-right: 1px solid #ddd;">
                    <?php echo $local;?> request
                    <br><small style="font-size: 12px;color: #aaa;">Woocommerce</small>
                </li>
                <li style="width: 32%;display: inline-block;padding: 10px 0;font-size: 22px;text-align: center;color: #2179ad;border-right: 1px solid #ddd;">
                    <?php echo $api;?> request
                    <br><small style="font-size: 12px;color: #aaa;">API</small>
                </li>
                <li style="width: 32%;display: inline-block;padding: 10px 0;font-size: 22px;text-align: center;color: #e68929;">
                    <?php echo $static;?> request
                    <br><small style="font-size: 12px;color: #aaa;">Static</small>
                </li>
			
			</ul>
            <?php
        }

    }
    // Start plugin.
    new WooLineNotify;
}