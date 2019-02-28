# Woocommerce Line Notify
Send woocommerce order notification to Line notify API.  
You can customize message pattern and notify to your chat room or your chat group in your pattern.

### [Download here](https://wordpress.org/plugins/woo-line-notify)

## Available short code for implement message pattern.
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
[products] //List of product in order.
```

## Features
- Add line token.
- Message pattern with order short code supported.
- Send notify to line group or user.
- Send notify when have order activity.
- Add logo or image banner to notification.
- Only support Thai language for this version .

## Benefits. 
1. Send a notification to your self.
2. Send a notifycation to your group.
3. Can send text, image and line sticker to the notification message.

## How to install
1. Upload the entire wc_linenotify folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.


## Term of Privacy

To understand what we do with your data and compile with The EU General Data Protection Regulation (GDPR)This plugin is call to external service Line Messaging API it use to be send an order data such as 
`Order Id, Order customer name, Order Total, Order Product, Order timestamp, Order payment method`
depend on your settings to your Line Messager account or Line group related with your Token ID.
### 3rd party service
We send a transaction to this domain [https://notify-bot.line.me](https://notify-bot.line.me)
Privacy Policy rules of Line Messaging [https://terms.line.me/line_rules?lang=en](https://terms.line.me/line_rules?lang=en)

### [Change log](CHANGELOG)

## Roadmap
- Message pattern for selected status.
- Multiple token id support for message broadcast.
- Message broadcast to line group.
- Add line sticker.
- Attach event url.

## Contibute guidelines
Thank you for your suggestion and support to the project. I very happy to help and give the thing I can do to the world. If you can help me to develop this plugin or join as a team members please follow below. I'll add your name to contributor members if you've joined.
1. Fork this repo on development branch and sync to your local.
2. run `php composer install`
3. Make change to the project.
4. Push & commit update to your branch.
5. Create pull request with update details to developer.
6. Waiting for approved.

### Welcome to the opensource world.

### [Wiki](https://git.iotech.co.th/iamapinan/woocommerce-line-notify/wikis), [Issue](https://git.iotech.co.th/iamapinan/woocommerce-line-notify/issues)

## Any support I can do.
* Email: [apinan@iotech.co.th](mailto:apinan@iotech.co.th)
* LineID: iamapinan
* Facebook: [facebook.com/9apinan](https://www.facebook.com/9apinan)

## License
[GNU General Public License v3.0](https://github.com/iamapinan/wc_linenotify/blob/master/LICENSE)
