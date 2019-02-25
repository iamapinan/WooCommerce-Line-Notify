=== Woocommerce Line Notify ===
Contributors: iamapinan
Donate link: https://paypal.me/apinu
Tags: woocommerce, notify, messager, alert, order, line
Requires at least: 4.8
Tested up to: 5.1
Stable tag: 5.1
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Send woocommerce order status changes to Line messaging api.

== Description ==

Send woocommerce order notification to Line notify API. You can customize message pattern and notify to your chat room or your chat group in your pattern.

= What's benefit of line notify can do. =
Send a notification to your self.
Send a notifycation to your group.
Can send text, image and line sticker to the notification message.

= Available shortcode =
`
[order_status]
[order_id]
[order_time]
[order_total]
[order_payment]
[order_address]
[order_customer]
[order_phone]
[order_company]
[order_note]
[order_province]
[order_url]
[products] //List of product in order.
`

= 3rd service =
https://notify-bot.line.me
https://terms.line.me/line_rules?lang=en

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-line-notify` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Line Notify screen to configure the plugin


== Frequently Asked Questions ==

= Can I send notify with order item info? =

Yes you can do it by adding shortcode [product] to your message pattern.


== Screenshots ==

1. Settings screen `assets/setting-screen.png`
2. Notify screen `assets/noti-demo.png`

== Changelog ==

= 1.0.6 =
- Add Shortcode `[product]` to have product list in message pattern.
- Remove require to attacth image.
- Click on short code to automatic add short code in message pattern.
- Fixed style.
- Increase stable and more.

== Upgrade Notice ==

= 1.0.6 =
Improved performance and add more features.

== Features ==

1. Add line token.
2. Message pattern with order short code supported.
3. Send notify to line group or user.
4. Send notify when have order activity.
5. Add logo or image banner to notification.
6. Only support Thai language for this version .

== Term of Privacy ==
To understand what we do with your data and compile with The EU General Data Protection Regulation (GDPR)This plugin is call to external service Line Messaging API it use to be send an order data such as 
Order Id, Order customer name, Order Total, Order Product, Order timestamp, Order payment method 
depend on your settings to your Line Messager account or Line group related with your Token ID.
= 3rd party service =
We send a transaction to this domain https://notify-bot.line.me
Privacy Policy rules of Line Messaging https://terms.line.me/line_rules?lang=en