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
    
    function wc_linenotify_init() {

        if ( ! class_exists( 'wc_linenotify' ) ) {
        // Create the class for plugin.
            class wc_linenotify extends WC_Order {
                // Constructor for wc_linenotify class.
                // @access public
                // @return void

                public function __construct() {
                    $this->id = 'wc-linenotify';
                    $this->title = 'Woocommerce Line Notify';
                    $this->notify_api_endpoint = '';
                    $this->token = 'y6dxyTcM1crbJRpvRDSnbWJxOewmj6I7vVDxiV8cKez'; //For test only.
                    $this->api_endpoint = '';
                    $this->init();
                }

                function lineauthorize() {

                }

                function SendNotify( $message ) {
                    // Post to line notify server.
                }

                function AdminSetting() {
                    // Setting page.
                }

                function init() {
                    // Initial plugin and plugin events.
                }
            }
        }
    }
    
    

}

