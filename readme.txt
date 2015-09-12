=== Site Icon Extended ===

Plugin Name:       Site Icon Extended
Plugin URI:        http://wordpress.org/plugins/site-icon-extended/
Author URI:        http://leaves-and-love.net
Author:            Felix Arntz
Donate link:       http://leaves-and-love.net/wordpress-plugins/
Contributors:      flixos90
Requires at least: 4.3 
Tested up to:      4.3
Stable tag:        0.1.0
Version:           0.1.0
License:           GPL v2 
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Tags:              wordpress, plugin, site-icon, icon, favicon, browser, compatibility

This plugin enhances the WordPress Site Icon feature by adding more icon formats for improved cross-browser compatibility.

== Description ==

The WordPress Site Icon feature added with version 4.3 introduces a standardized way to add a site icon (also referred to as just "icon" or "favicon") to your site - awesome! No more bloated plugins necessary to have this feature, no more custom code required.

Why this plugin then? -, you might ask. While WordPress does a pretty decent job in terms of cross-browser compatibility of the icon, it still has some room to improve if you are interested in supporting all kinds of different devices. *Site Icon Extended* is a lightweight plugin to address just that, built upon the native Site Icon feature of WordPress.

= Features =

* adds more icon sizes for the default icon and Apple Touch Icon to better support different resolutions
* generates an `.ico` file to use for the general shortcut icon
* generates a `browserconfig.xml` file to support Windows 8.1
* adds a Customizer field to specify an icon background color used by some Windows devices

**Note:** This plugin requires PHP 5.3.0 at least.

== Installation ==

1. Upload the entire `site-icon-extended` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Open the Customizer to specify a Site Icon.

== Frequently Asked Questions ==

= Some of the different sizes are not generated correctly. Why is that? =

If you see that the `link` and `meta` tags in the head of your site do not output the file that corresponds to the size specified in the tag, you might have specified the Site Icon before activating the *Site Icon Extended* plugin. In this case, simply remove the Site Icon and then add it back again.

= Why can't I access the generated XML file? =

To access the generated XML (and to allow the browser to access it), some rewrite rules are necessary. If you are not able to access the file, it's probably because your rewrite rules have not been flushed correctly. Please navigate to the *Settings > Permalinks* page in the WordPress admin and just hit *Save* without changing anything to flush the rewrite rules.

= Where should I submit my support request? =

I preferably take support requests as [issues on Github](https://github.com/felixarntz/site-icon-extended/issues), so I would appreciate if you created an issue for your request there. However, if you don't have an account there and do not want to sign up, you can of course use the [wordpress.org support forums](https://wordpress.org/support/plugin/site-icon-extended) as well.

= How can I contribute to the plugin? =

If you're a developer and you have some ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request in the [Github repository for the plugin](https://github.com/felixarntz/site-icon-extended).

== Screenshots ==

1. The generated meta tags
2. The browserconfig.xml file
3. The new Site Icon background field in the Customizer

== Changelog ==

= 0.1.0 =
* Initial release

== Credits ==

Some of the code related to integrate the `browserconfig.xml` file was heavily inspired by the *Yoast SEO* plugin which uses a similar technique to handle its sitemap files.

= Third-Party Libraries =

The plugin uses the [PHP_ICO](https://github.com/chrisbliss18/php-ico) class to generate the `.ico` file.
