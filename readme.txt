=== WP Disable Comments ===
Contributors: benohead, amazingweb-gmbh
Donate link: http://benohead.com/donate/
Tags: comments, disable
Requires at least: 3.0.1
Tested up to: 4.1.1
Stable tag: 0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Disable comments, trackbacks and/or pingbacks globally or for specific post types, categories, tags, referrers and more. In a comprehensive and flexible way. Per site or for a whole network.

== Description ==

This plugin allows administrators to disable comments, trackbacks and/or pingbacks on a site or a network. The goal of this plugin is to be as comprehensive as possible and at the same time provide the flexibility to just as much as you want to.

You can choose what to disable:

* Comments
* Pingbacks
* Trackbacks
* XML-RPC
* RSD links
* Recent Comments Widget in Dashboard

You can choose when and where you want to disable it:

* for logged in users
* for specific post/page IDs (including ranges of IDs)
* for specific categories
* for specific tags
* for specific authors
* for specific post formats
* for specific post types
* for specific languages (this option is only available with the plugin qTranslate or mqTranslate)
* for specific URL paths
* for specific referrers
* for specific IP addresses

If you disable comments or trackbacks for specific post types, the corresponding meta boxes in the post editor will also be removed.

In a multisite environment, you can either activate it for the whole network (the settings will then be available in the network admin page and affect all blogs) or for specific blogs (you can the specify different settings for different blogs).

As an alternative you can also keep commenting enabled but have the discussion comment checkboxes unchecked by default on pages, posts or any custom post type. This allows your authors to explicitly enable comments but have them disabled by default so that they do not need to remove the checkboxes for every new post.
You can also disable the comment author URL field or just remove URLs with Google authorship link (to prevent someone from trying to take authorship by posting a comment).

== Installation ==

1. Upload the folder `wp-disable-comments` to the `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Modify the settings to define where comments should be disabled.

== Frequently Asked Questions ==

= How can I contact you with a complaint, a question or a suggestion? =
Send an email to henri.benoit@gmail.com

== Screenshots ==

1. WP Disable Comments Settings

2. Disabling the discussion checkboxes by default but without completely disabling commenting.

== Changelog ==

= 0.4 =

* Added checks to prevent warnings for undefined variables

= 0.3.3 =

* Fixed disabling checkboxes for post type Media

= 0.3.2 =

* Fixed plugin description and change log entry for 0.3.1

= 0.3.1 =

* Fixed strict PHP warnings

= 0.3 =

* Added option to disable comment author link.
* Added option to remove comment URLs with Google authorship link.

= 0.2 =

* Added option to disable the discussion checkboxes by default but without completely disabling commenting.

= 0.1 =

* First version.

== Upgrade Notice ==

n.a.
