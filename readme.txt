=== Most And Least Read Posts Widget ===
Contributors: whiletrue
Donate link: http://www.whiletrue.it/
Tags: most, least, read, post, posts, most read, least read, more, less, more read, top read, top, less read, sidebar, widget, plugin, links 
Requires at least: 2.9+
Tested up to: 3.8
Stable tag: 2.1.3

Provide two widgets, showing lists of the most and reast read posts.

== Description ==
"Most And Least Read Posts Widget" is a free plugin for Wordpress, developed by the Whiletrue.it staff to generate lists of the most and least read posts.

The following options are customizable:

* number of posts to show
* exclude posts whose title contains certain words
* show post hits after the title (style customizable via CSS class)
* exclude posts older than XX days

The plugin starts counting hits once activated, storing them in the "custom_total_hits" custom field without the need of external accounts.

The most popular web crawlers (e.g. Googlebot) are recognized and their hits discarded; also Admin hits are discarded.

Archived post hits are shown in a column inside the backend post list.

The plugin is compatible with multi-language WPML plugin, showing most/least read posts for current language.

Optionally, the number of hits can be shown inside the post content, with:

* a custom phrase, e.g. "This post has already been read XX times!"
* a custom position (above the post, below the post, both)
* a custom CSS style

If you want to show the post hits anywhere inside the template loop, you can the php function provided, e.g.: 
`<?php echo most_and_least_read_posts_get_hits(get_the_ID()); ?>`

= Reference =

For more informations: [www.whiletrue.it](http://www.whiletrue.it/most-and-least-read-posts-widget-for-wordpress/ "www.whiletrue.it")

Do you like this plugin? Give a chance to our other works:

* [Really Simple Facebook Twitter Share Buttons](http://www.whiletrue.it/really-simple-facebook-twitter-share-buttons-for-wordpress/ "Really Simple Facebook Twitter Share Buttons")
* [Really simple Twitter Feed Widget](http://www.whiletrue.it/really-simple-twitter-feed-widget-for-wordpress/ "Really simple Twitter Feed Widget")
* [Tilted Tag Cloud Widget](http://www.whiletrue.it/tilted-tag-cloud-widget-per-wordpress/ "Tilted Tag Cloud Widget")
* [Reading Time](http://www.whiletrue.it/reading-time-for-wordpress/ "Reading Time")


== Installation ==
Best is to install directly from WordPress. If manual installation is required, please make sure to put all of the plugin files in a folder named `most_and_least_read_posts_widget` (not two nested folders) in the plugin directory, then activate the plugin through the `Plugins` menu in WordPress.

== Frequently Asked Questions ==

= I get an error message that says "no results available" =
The plugin starts collecting hits once installed, so there are "no results available" for a short time, until the first data is stored. 
It's better to show the widget some hours (or days) after having installed it.

= The same post shows up multiple times =
This uncommon issue can be caused by duplicated custom fields in some posts. 
To solve it, inspect the post custom fields and delete unnecessary duplicates of the "custom_total_hits" field. 

= Can I customize the thumbs? =
Yes, you can do it editing the "mlrp_ul" class in your template style.css file.
E.g. 50x50 pixels images, floating on the right:
.mlrp_ul img { width: 50px; height: 50px; float: right; }

== Changelog ==

= 2.1.3 =
* Added: Donate link

= 2.1.2 =
* Changed: Code cleaning

= 2.1.1 =
* Changed: Code cleaning

= 2.1 =
* Fixed: Show post hits also when user is admin

= 2.0.3 =
* Changed: Skip updating hits if user is admin

= 2.0.2 =
* Added: WhileTrue RSS Feed

= 2.0.1 =
* Fixed: Fix for the "Exclude post whose title contains any of these words" option - thank you Thomas!

= 2.0 =
* Added: Use the comma "," for thousands digits
* Added: Append a custom text (e.g. the word "views") next to total hits

= 1.9 =
* Added: support for WPML plugin, showing most/least read posts for current language

= 1.8 =
* Added: show post thumbs option
* Added: "mlrp_ul" ul class for easy CSS styling
* Fixed: better bot recognition

= 1.7 =
* Added: php function to retrieve and show hits inside the template loop

= 1.6 =
* Added: archived post hits are now shown in a column inside the backend post list.

= 1.5 =
* Added: option to exclude posts older than XX days also in Least Read Posts (default: 365 days)
* Added: Frequently Asked Questions

= 1.4 =
* Added: option to exclude posts older than XX days in Most Read Posts (default: 365 days)

= 1.3 =
* Added: style customization of hits on widget, through the "most_and_least_read_posts_hits" CSS class
* Changed: hits on widget put out of the link
* Fixed: query limited only to published posts
* Fixed: error while saving widget options
* Fixed: error while opening the Settings page

= 1.2 =
* Added: option to show hits after the post title, inside the widgets

= 1.1 =
* Added: optionally show hits inside the post content, with phrase, position and css style customization

= 1.0.0 =
Initial release


== Upgrade Notice ==

= 1.0.0 =
Initial release
