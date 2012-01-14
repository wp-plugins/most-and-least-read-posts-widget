=== Most And Least Read Posts Widget ===
Contributors: whiletrue
Donate link: http://www.whiletrue.it/
Tags: most, least, read, post, posts, most read, least read, more, less, more read, top read, top, less read, sidebar, widget, plugin, links 
Requires at least: 2.9+
Tested up to: 3.3.1
Stable tag: 1.5

Provide two widgets, showing lists of the most and reast read posts.

== Description ==
"Most And Least Read Posts Widget" is a free plugin for Wordpress, developed by the Whiletrue.it staff to generate lists of the most and least read posts.

The following options are customizable:

* number of posts to show
* exclude posts whose title contains certain words
* show post hits after the title (style customizable via CSS class)
* exclude posts older than XX days

The plugin starts counting hits once activated, storing them in the "custom_total_hits" custom field without the need of external accounts.

The most popular web crawlers (e.g. Googlebot) are recognized and their hits discarded.

Optionally, the number of hits can be shown inside the post content, with:

* a custom phrase, e.g. "This post has already been read XX times!"
* a custom position (above the post, below the post, both)
* a custom CSS style

For more informations: http://www.whiletrue.it/en/projects/wordpress/29-most-and-least-read-posts-widget-per-wordpress.html

Do you like this plugin? Give a chance to our other works:

* [Really Simple Facebook Twitter Share Buttons](http://www.whiletrue.it/en/projects/wordpress/22-really-simple-facebook-twitter-share-buttons-per-wordpress.html "Really Simple Facebook Twitter Share Buttons")
* [Random Tweet Widget](http://www.whiletrue.it/en/projects/wordpress/33-random-tweet-widget-per-wordpress.html "Random Tweet Widget")
* [Reading Time](http://www.whiletrue.it/en/projects/wordpress/17-reading-time-per-wordpress.html "Reading Time")
* [Really Simple Twitter Feed Widget](http://www.whiletrue.it/en/projects/wordpress/25-really-simple-twitter-feed-widget-per-wordpress.html "Really Simple Twitter Feed Widget")
* [Tilted Twitter Cloud Widget](http://www.whiletrue.it/en/projects/wordpress/26-tilted-twitter-cloud-widget-per-wordpress.html "Tilted Twitter Cloud Widget")


== Installation ==
1. Upload the `most_and_least_read_posts_widget` directory into the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Inside the `Themes->Widget` menu, place the Least Read Posts Widget inside a sidebar, customize the settings and save
4. Enjoy!

== Frequently Asked Questions ==

= I get an error message that says "no results available" =
The plugin starts collecting hits once installed, so there are "no results available" for a short time, until the first data is stored. 
It's better to show the widget some hours (or days) after having installed it.

= The same post shows up multiple times "
This uncommon issue can be caused by duplicated custom fields in some posts. 
To solve it, inspect the post custom fields and delete unnecessary duplicates of the "custom_total_hits" field. 


== Screenshots ==
Coming soon

== Changelog ==

= 1.5 =
Added: option to exclude posts older than XX days also in Least Read Posts (default: 365 days)
Added: Frequently Asked Questions

= 1.4 =
Added: option to exclude posts older than XX days in Most Read Posts (default: 365 days)

= 1.3 =
Added: style customization of hits on widget, through the "most_and_least_read_posts_hits" CSS class
Changed: hits on widget put out of the link
Fixed: query limited only to published posts
Fixed: error while saving widget options
Fixed: error while opening the Settings page

= 1.2 =
Added: option to show hits after the post title, inside the widgets

= 1.1 =
Added: optionally show hits inside the post content, with phrase, position and css style customization

= 1.0.0 =
Initial release


== Upgrade Notice ==

= 1.0.0 =
Initial release
