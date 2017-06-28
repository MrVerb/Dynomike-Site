=== Featured Category Widget ===
Contributors: tepelstreel
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RMF326NZYFL6L
Tags: column, sidebar, widget, category, newspaper, image, multi widget, teaser, featured, post, featured post, random post
Requires at least: 2.9
Tested up to: 4.5	
Stable tag: 2.5

The Featured Category Widget is basically a Featured Post Widget for a category.

== Description ==

The Featured Category Widget is mainly designed because there were people for whom the Featured Post Widget was not enough. They wanted to put a category of their blog in the highlight.
If there is a post thumbnail, it will be displayed above the headline of the post. If there is no thumbnail, the first picture of the post is taken. You can set the size for the thumbnail or just take the standard from your options. Decide yourself, whether you want to show the excerpt, saved with your post or just the first three sentences or the first twenty words of the post. Style the widget individually, ready.

The Featured Category was tested up to WP 4.5. It should work with versions down to 2.9 but was never tested on those.

== Installation ==

1. Upload the `category-feature` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place and customize your widgets
4. Ready

== Frequently Asked Questions ==

= I styled the widget container myself and it looks bad. What do I do? =

The styling of the widget requires some knowledge of css. If you are not familiar with that, try adding

`padding: 10px;
margin-bottom: 10px;`
 
to the style section.

= My widget should have rounded corners, how do I do that? =

Add something like

`-webkit-border-top-left-radius: 5px;
-webkit-border-top-right-radius: 5px;
-moz-border-radius-topleft: 5px;
-moz-border-radius-topright: 5px;
border-top-left-radius: 5px;
border-top-right-radius: 5px;`
 
to the widget style. This is not supported by all browsers yet, but should work in almost all of them.

= My widget should have a shadow, how do I do that? =

Add something like

`-moz-box-shadow: 10px 10px 5px #888888;
-webkit-box-shadow: 10px 10px 5px #888888;
box-shadow: 10px 10px 5px #888888;`
 
to the widget style to get a nice shadow down right of the container. This is not supported by all browsers yet, but should work in almost all of them.

== Screenshots ==

1. The plugin's work on our testing page
2. The widget's settings section

== Changelog ==

= 2.5 =

* WP 4.5 ready
* New framework

= 2.4.1 =

* forgotten var_dump() taken out

= 2.4 =

* WP 4.3 ready
* New framework

= 2.3.1 =

* bug with visiblity fixed

= 2.3 =

* WP 4.1 ready
* New framework

= 2.2.2 =

* Mistake in image class fixed

= 2.2.1 =

* Interference with Wordpress Page Widgets eliminated

= 2.2 =

* Bug fixed with featured images

= 2.1 =

* Posts now sortable in alphabetical order
* DSS compressable

= 2.0.1 =

* Made compliant with WP 3.9.1 and small bugfix

= 2.0 =

* New feature to have post of the main loop in the widget on single pages

= 1.9 =

* All 'Devided by Zero' errors should be eliminated

= 1.8 =

* Improvement in image recognition, more flexible

= 1.7.1 =

* image border added and some streamlining done

= 1.7 =

* streamlined code
* alignment of the thumbnail now possible
* you can show the post date
* more responsive

= 1.6 =

* adjustments in the framework
* some more customizing was asked

= 1.5 =

* you can now link the widget title to the displayed category

= 1.4 =

* possibility to change size of thumbnail added; small bug fixed

= 1.3 =
* small bugfix in auto excerpt

= 1.2 =
* code overhaul
* hooks into the [Ads Easy Plugin](http://wordpress.org/extend/plugins/adeasy) if Google AdSense Tags are in use

= 1.1 =
* possibility to choose random posts

= 1.0 =
* initial release

== Upgrade Notice ==

= 1.0 =
just new

= 1.1 =
possibility to choose random posts

= 1.2 =
code overhaul and plugin hooks into the Ads Easy Plugin if Google AdSense Tags are in use

= 1.3 =
small bugfix in auto excerpt

= 1.4 =

possibility to change size of thumbnail added; small bug fixed

= 1.5 =

you can now link the widget title to the displayed category

= 1.6 =

streamlined and more functionality added

= 1.7 =

new features and streamlined code

= 1.7.1 =

image border added and code streamlined

= 1.8 =

Improvement in image recognition, more flexible

= 1.9 =

All 'Devided by Zero' errors should be eliminated

= 2.0 =

New feature to have post of the main loop in the widget on single pages

= 2.0.1 =

Made compliant with WP 3.9.1 and small bugfix

= 2.1 =

Posts now sortable in alphabetical order; DSS compressable

= 2.2 =

Bug fixed with featured images

= 2.2.1 =

Interference with Wordpress Page Widgets eliminated

= 2.2.2 =

Mistake in image class fixed

= 2.3 =

WP 4.1 ready; new framework

= 2.3.1 =

bug with visablity fixed

= 2.4 =

WP 4.3 ready; new framework

= 2.4.1 =

Forgotten var_dump() taken out

= 2.5 =

WP 4.5 ready; new framework