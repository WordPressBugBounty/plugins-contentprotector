=== ContentProtector - password protect your page, post or text ===
Contributors: antonphp
Tags: password, content protection, password protect, protection, restrict content
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect your content with passwords using easy-to-use shortcodes. Supports both global protection and partial content protection.

== Description ==

**ContentProtector** is a lightweight and flexible plugin that allows you to protect your WordPress content with passwords. You can protect an entire post or page, or just a specific portion of the content.

### Features
- **Global Protection**: Set a global password for all posts or pages.
- **Partial Content Protection**: Use a shortcode to protect only a specific part of your content.
- **Customizable**: Easy to set up with a clean and intuitive admin interface.
- **Secure**: Uses WordPress Nonces and cookies for secure password handling.

### How It Works
1. Use the shortcode `[cpwp_protect password="your_password"]` to protect an entire post or page.
2. Use `[cpwp_protect_content password="your_password"]` to protect only a portion of your content.

Example for partial content protection:
```html
[cpwp_protect_content password="pass"]
This content is protected by a password. Enter the password to view it.
[/cpwp_protect_content]

== Installation ==

Download the plugin and upload the contentprotector folder to the /wp-content/plugins/ directory.
Activate the plugin through the "Plugins" menu in WordPress.
Go to the ContentProtector menu in the WordPress dashboard to configure settings.

== Frequently Asked Questions ==

= Can I use different passwords for different posts or content? =
Yes! Each shortcode instance can have its own password.

= What happens if a user enters the wrong password? =
The plugin will show an error message and prompt the user to try again.

= Can I use this plugin on a multisite installation? =
Yes, ContentProtector works perfectly on WordPress multisite.

== Screenshots ==

1. Admin settings page with configuration options.
2. Password protect block

== Changelog ==

= 1.0 =

Initial release.

== Upgrade Notice ==
N/A

