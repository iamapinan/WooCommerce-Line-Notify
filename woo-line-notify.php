<?php
/*
Plugin Name: Woocommerce Line Notify
Plugin URI: https://git.iotech.co.th/iamapinan/woocommerce-line-notify
Description: Woocommerce new order notify to line chat.
Version: 1.0.9
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

if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
// Check if woocommerce is present in the system.
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Load class
    include_once( WOO_LINE_NOTIFY_PATH . 'inc/Admin.php' );
    // Trigger when order status changes.
    add_action( 'woocommerce_order_status_changed', array('Admin', 'SendAlert') );

    // Add menu
    add_action( 'admin_menu', function () {
        $admin = new Admin;
        $admin->menu();
    } );
    // Add form
    add_action( 'admin_init', function () {
        $admin = new Admin;
        $admin->wc_line_notify_page_init();
    } );
}
