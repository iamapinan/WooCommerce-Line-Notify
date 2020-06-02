[![CodeFactor](https://www.codefactor.io/repository/github/iamapinan/woocommerce-line-notify/badge)](https://www.codefactor.io/repository/github/iamapinan/woocommerce-line-notify)

# Woocommerce Line Notify
Send woocommerce order notification to Line notify API.  
You can customize message pattern and notify to your chat room or your chat group in your pattern.

---
![Woocommerce Line Notify icon](src/image/download_button.png)   
[https://wordpress.org/plugins/woo-line-notify](https://wordpress.org/plugins/woo-line-notify)

### Features
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

### Short code.
```
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
[products] <-- List of product in order.
```

### Dynamic shortcode
```
[shipping *]
[billing *]
[customer *]
[order *]
[meta *]
```

### Short code
#### Customer Fields
```	
first_name	[custoemr first_name]
last_name	[custoemr last_name]
email	        [custoemr email]
phone	        [custoemr phone]
```

#### Shipping fields
```
company	        [shipping company]
address_1	[shipping address_1]
address_2	[shipping address_2]
city	        [shipping city]
state	        [shipping state]
postcode	[shipping postcode]
country	        [shipping country]
address_index	[shipping address_index]
```

#### Billing fields
```
company	        [billing company]
address_1	[billing address_1]
address_2	[billing address_2]
city	        [billing city]
state	        [billing state]
postcode	[billing postcode]
country	        [billing country]
email	        [billing email]
phone	        [billing phone]
address_index	[billing address_index]
```
#### Order fields
```
order_key	        [order order_key]
customer_user	        [order customer_user]
payment_method	        [order payment_method]
payment_method_title	[order payment_method_title]
transaction_id	        [order transaction_id]
customer_ip_address	[order customer_ip_address]
customer_user_agent	[order customer_user_agent]
created_via	        [order created_via]
date_completed	        [order date_completed]
completed_date	        [order completed_date]
date_paid	        [order date_paid]
paid_date	        [order paid_date]
cart_hash	        [order cart_hash]
order_currency	        [order order_currency]
cart_discount	        [order cart_discount]
cart_discount_tax	[order cart_discount_tax]
order_shipping	        [order order_shipping]
ordertax	        [order ordertax]
order_tax	        [order order_tax]
order_total	        [order order_total]
order_version	        [order order_version]
prices_include_tax	[order prices_include_tax]
download_permissions_granted	[order download_permissions_granted]
recorded_sales	        [order recorded_sales]
recorded_coupon_usage_counts	[order recorded_coupon_usage_counts]
order_stock_reduced	[order order_stock_reduced]
edit_lock	        [order edit_lock]
edit_last	        [order edit_last]
```
#### Meta
Just use `[meta your_meta_name]` to take custom order meta by `get_post_meta()` function.

### API for Developer
To send message via static method just put your message to the method like below.
```
WooLineNotify::Send_Line_Notify( 'Some message to send' );
```

### REST API
- URL: `domain/wp-json/woo-line-notify/v1/notify`  
- METHOD: `POST`   
- PARAMETERS: `message <Your message to send> ` 
- Authentication: `Basic Authen`  
- USERNAME/PASSWORD: `<API Key>:<API_Key>`

#### For example.
Send post request to `http://localhost/wp-json/woo-line-notify/v1/notify` you must be send parameter `message` by form/data and add Authenticate to header with value `Basic base64_encode( some_api_key_from_api_option:some_api_key_from_api_option )`

## How to install
1. Upload the entire wc_linenotify folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.


## Term of Privacy

To understand what we do with your data and compile with The EU General Data Protection Regulation (GDPR)This plugin is call to external service Line Messaging API it use to be send an order data such as 
`Order Id, Order customer name, Order Total, Order Product, Order timestamp, Order payment method`
depend on your settings to your Line Messager account or Line group related with your Token ID.
### 3rd party service term
We send a transaction to this domain [https://notify-bot.line.me](https://notify-bot.line.me)  
Privacy Policy rules of Line Messaging [https://terms.line.me/line_rules?lang=en](https://terms.line.me/line_rules?lang=en)

### [Change log](CHANGELOG)

## Roadmap
- Message pattern for selected status.
- Multiple token id support for message broadcast.

## Contibute guidelines
Thank you for your suggestion and support to the project. I very happy to help and give the thing I can do to the world. If you can help me to develop this plugin or join as a team members please follow below. I'll add your name to contributor members if you've joined.
1. Fork this repo on development branch and sync to your local.
2. run `php composer install`
3. Make change to the project.
4. Push & commit update to your branch.
5. Create pull request with update details to developer.
6. Waiting for approved.

### Changelog
#### version 1.1.0
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
---

### Welcome to the opensource world.

### [Wiki](https://git.iotech.co.th/iamapinan/woocommerce-line-notify/wikis), [Issue](https://git.iotech.co.th/iamapinan/woocommerce-line-notify/issues)

## Any support I can do.
* Email: [apinan@iotech.co.th](mailto:apinan@iotech.co.th)
* LineID: iamapinan
* Facebook: [facebook.com/9apinan](https://www.facebook.com/9apinan)

## License
[GNU General Public License v3.0](https://github.com/iamapinan/wc_linenotify/blob/master/LICENSE)
