=== Woocommerce Line Notify ===
Contributors: iamapinan
Donate link: https://paypal.me/apinu
Tags: woocommerce line notify, woo line notify, woo-line-notify, notification, line notification, line notify, woocommerce, notify, messager, alert, order, line, line bot
Requires at least: 4.8
Tested up to: 5.2.2
Stable tag: 1.1.1
Requires PHP: 7.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Send woocommerce order status changes to Line messaging api.

== Description ==

Send woocommerce order notification to Line notify API. You can customize message pattern and notify to your chat room or your chat group in your pattern.

= What's benefit of line notify can do. =
1. Add line token.
2. Message pattern with order short code supported.
3. Send notify to line group or user.
4. Send notify when have order activity.
5. Multi language support.
6. API to send message with basic authen security for developer.
7. Static method to send message for developer.
8. Debug mode option.
9. Dynamic fields to unlock your need.
10. Dashboard widget.
11. Can use without Woocommerce.

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
[order_postcode]
[products] //List of product in order.
`

= Dynamic shortcode =
`
[shipping *]
[billing *]
[customer *]
[order *]
[meta *]
`
= Customer Fields =	
first_name	[custoemr first_name]
last_name	[custoemr last_name]
email	[custoemr email]
phone	[custoemr phone]

= Shipping fields =
company	[shipping company]
address_1	[shipping address_1]
address_2	[shipping address_2]
city	[shipping city]
state	[shipping state]
postcode	[shipping postcode]
country	[shipping country]
address_index	[shipping address_index]

= Billing fields =	
company	[billing company]
address_1	[billing address_1]
address_2	[billing address_2]
city	[billing city]
state	[billing state]
postcode	[billing postcode]
country	[billing country]
email	[billing email]
phone	[billing phone]
address_index	[billing address_index]

= Order fields =	
order_key	[order order_key]
customer_user	[order customer_user]
payment_method	[order payment_method]
payment_method_title	[order payment_method_title]
transaction_id	[order transaction_id]
customer_ip_address	[order customer_ip_address]
customer_user_agent	[order customer_user_agent]
created_via	[order created_via]
date_completed	[order date_completed]
completed_date	[order completed_date]
date_paid	[order date_paid]
paid_date	[order paid_date]
cart_hash	[order cart_hash]
order_currency	[order order_currency]
cart_discount	[order cart_discount]
cart_discount_tax	[order cart_discount_tax]
order_shipping	[order order_shipping]
ordertax	[order ordertax]
order_tax	[order order_tax]
order_total	[order order_total]
order_version	[order order_version]
prices_include_tax	[order prices_include_tax]
download_permissions_granted	[order download_permissions_granted]
recorded_sales	[order recorded_sales]
recorded_coupon_usage_counts	[order recorded_coupon_usage_counts]
order_stock_reduced	[order order_stock_reduced]
edit_lock	[order edit_lock]
edit_last	[order edit_last]

= Meta =
Just use `[meta your_meta_name]` to take custom order meta by `get_post_meta()` function.

== API for Developer ==
To send message via static method just put your message to the method like below.
`
    WooLineNotify::Send_Line_Notify( 'Some message to send' );
`

== REST API ==
URL: domain/wp-json/woo-line-notify/v1/notify
METHOD: POST
PARAMETERS: `message` <Your message to send>
Authentication: Basic Authen
USERNAME/PASSWORD: <API Key>:<API_Key>

= For example. =
Send post request to `http://localhost/wp-json/woo-line-notify/v1/notify` you must be send parameter `message` by form/data and add Authenticate to header with value `Basic base64_encode( some_api_key_from_api_option:some_api_key_from_api_option )`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woo-line-notify` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Line Notify screen to configure the plugin


== Frequently Asked Questions ==

= Can I send notify with order item info? =

Yes you can do it by adding shortcode [product] to your message pattern.


== Screenshots ==
1. Notification setting.
2. Notify screen demo.
3. Debug setting.
4. API setting.
5. Dashboard widget.

== Changelog ==
= 1.1.1 =
- add code

= 1.1.0 =
- Change admin ui
- Change class name duplicate.
- Multi language support.
- API
- Add postcode shortcode.
- Direct method
- Debug option.
- Dynamic fields
- Dashboard widget
- Can use without Woocommerce

= 1.0.9 =
- Update meta

= 1.0.8 =
- Fixed metadata for svn.
- Change token field type to password.

= 1.0.7 =
- Change setting design.
- Add debug function.
- Update code and folder structure.
- Change plugin slug to match with Plugin directory.
- Fixed many warning.
- Remove banner from setting page.
- Add icon to setting page.
- Make all text translatable.
- Full support Thai and English language.

= 1.0.6 =
- Add Shortcode `[product]` to have product list in message pattern.
- Remove require to attacth image.
- Click on short code to automatic add short code in message pattern.
- Fixed style.
- Increase stable and more.

== Upgrade Notice ==
= 1.0.9 =
- Update meta
= 1.0.8 =
Fixed metatag
= 1.0.7 =
Update design and fixed many error make it can update from store and make it translatable.
= 1.0.6 =
Improved performance and add more features.

== Features ==
1. Add line token.
2. Message pattern with order short code supported.
3. Send notify to line group or user.
4. Send notify when have order activity.
5. Multi language support.
6. API to send message with basic authen security for developer.
7. Static method to send message for developer.
8. Debug mode option.
9. Dynamic fields to unlock your need.
10. Dashboard widget.
11. Can use without Woocommerce.

== Term of Privacy ==
To understand what we do with your data and compile with The EU General Data Protection Regulation (GDPR)This plugin is call to external service Line Messaging API it use to be send an order data such as 
Order Id, Order customer name, Order Total, Order Product, Order timestamp, Order payment method 
depend on your settings to your Line Messager account or Line group related with your Token ID.

= Line Notify Term =
Privacy policy rules of Line Messaging please read [Term of privacy](https://terms.line.me/line_rules?lang=en)