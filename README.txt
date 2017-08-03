=== Delete Del ===
Contributors: lilyfan
Tags: del, delete, rss, feed, excerpt, html, element
Requires at least: 2.0
Tested up to: 2.9
Stable tag: 0.9.5

"Delete Del" removes del elements from RSS 1.0 feeds, and removes HTML comments from feeds and post/page content.

== Description ==

Ins and del elements are used to show the differences between previous revision and current one of posts/pages. An ordinary XHTML+CSS output, ins elements are shown as underline, del elements are shown as strikeout text.
As RSS 1.0 feeds, all XHTML markups are eliminated. Ins and del markup are also removed. Readers of RSS 1.0 feeds cannot identify the ins and del markups.
It is safe that ins markups are erased, but removing del markup is harmful. The text inside del elements are also needed to be erased.

This plugin does that: removing del elements with included text from RSS/ATOM feeds.


Also, HTML comments in post/page content causes syntax corrupt with current WordPress HTML filter. For this purpose, this plugin removes HTML comments (<!-- -->) from feeds and post/page content before WordPress HTML filter.

== Requirements ==

* WordPress 2.0 or later
* PHP 4.2 or later

== Installation ==

1. Unzip the plugin archive and put "delete-del" folder into your "plugins" directory (wp-content/plugins/) of the server. You can put only the PHP file "delete-del.php" into the plugin directory.
1. Activate the plugin.

== Licence ==

The license of this plugin is GPL v2.

== Getting a support ==

To get support for this plugin, please send an email to ikeda.yuriko+deletedel_@_ GMAIL COM. (You need adjust to valid address)

== Changelog ==

= 0.9.5 (2010-01-04) =
* Distribute at the official WordPress plugin directory.
* Delete HTML comments in feeds and post contents.
* Delete del elements in comment RSS 1.0 feed.
* Apply excerpt_more, wp_trim_excerpt filter.
* Follow the code of WP-Multibyte-Patch plugin for creating multibyte excerpt.
= 0.9.0 (2008-07-15) =
* Initial version

== Upgrade Notice ==
= 0.9.5 =
Improved creating multibyte text summary. Followed other plugins that filters excerpt length, excerpt text.
