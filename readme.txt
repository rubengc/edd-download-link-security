=== EDD Download Link Security ===
Contributors: rubengc
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=64N6CERD8LPZN
Tags: easy digital downloads, digital, download, downloads, edd, rubengc, download, link, security, restriction, protection, secure, e-commerce
Requires at least: 4.0
Tested up to: 4.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically adds extra security to download file links

== Description ==
This plugin requires [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/ "Easy Digital Downloads") v2.3 or greater.

Once activated, EDD Download Link Security will add own download file verification process based on this checks:
1. User is logged
1. User has been purchased this item
1. Purchase email matches with user email
1. Payment has accepted

If verification process fails then user will be redirected to configured redirect page. You can set the redirect page to:
1. Default wordpress error page (wp_die)
1. Download's page
1. Any published page

There's a [GIT repository](https://github.com/rubengc/edd-download-link-security) too if you want to contribute a patch.


== Installation ==

1. Unpack the entire contents of this plugin zip file into your `wp-content/plugins/` folder locally
1. Upload to your site
1. Navigate to `wp-admin/plugins.php` on your site (your WP Admin plugin page)
1. Activate this plugin
1. That's it! download links will automatically be protected

OR you can just install it with WordPress by going to Plugins >> Add New >> and type this plugin's name

== Frequently Asked Questions ==

== Screenshots ==

== Upgrade Notice ==

== Changelog ==

= 1.0 =
* Initial release
