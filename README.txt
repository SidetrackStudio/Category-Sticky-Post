=== Category Sticky Post ===
Contributors: tommcfarlin
Donate link: http://tommcfarlin.com/category-sticky-post/
Tags: categories, post
Requires at least: 3.4.1
Tested up to: 3.5
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Mark a post to be placed at the top of a specified category archive. It's sticky posts specifically for categories.

== Description ==

Category Sticky Post allows you to mark a post to be displayed - or stuck - to the top of each archive page for the specified
category.

Category Sticky Post...

* Allows you to select which category in which to stick a post
* Will display the post on the top of the first page of the archive just like built-in sticky posts
* Will only allow you to stick a single post per category
* Displays whether or not a post is stuck in a category on the Post Edit dashboard
* Provides light styling that should look good in most themes
* Is available on each post editor page
* Is fully localized and ready for translation
* Includes a custom.css file so that you can style the plugin to look however you want

For more information or to follow the project, check out the [project page](http://tommcfarlin.com/category-sticky-post/).

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' Plugin Dashboard
1. Select `category-sticky-post.zip` from your computer
1. Upload
1. Activate the plugin on the WordPress Plugin Dashboard

= Using FTP =

1. Extract `category-sticky-post.zip` to your computer
1. Upload the `category-sticky-post` directory to your `wp-content/plugins` directory
1. Activate the plugin on the WordPress Plugins dashboard

== Screenshots ==

1. A post marked as a 'Category Sticky' displaying at the top of an archive page
2. The new menu item for selecting which in which category to stick the given post
3. Disabled options show that a category already has a sticky post
4. The post dashboard indicating which entries are category sticky posts

== Changelog ==

= 1.1.2 =
* Removing the custom.css support as it was causing issues with other plugin upgrades. Will be restored later, if requested.

= 1.1.1 =
* Improving support for adding custom.css so that the file is also managed properly during the plugin update process
* Updating localization files

= 1.1 =
* Updating function calls to use updated PHP conventions
* Adding a function to dynamically create a custom.css file if one doesn't exist
* Verifying compatibility with WordPress 3.5

= 1.0 =
* Initial release

== Development Information ==

Category Sticky Post was built with...

* The desire to perform the same functionality on my own blog
* [WordPress Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards)
* Native WordPress API's (specifically the [Plugin API](http://codex.wordpress.org/Plugin_API))
* [CodeKit](http://incident57.com/codekit/) using [LESS](http://lesscss.org/), [JSLint](http://www.jslint.com/lint.html), and [jQuery](http://jquery.com/)
* Some advice from [Konstantin Kovshenin](http://twitter.com/kovshenin) on query optimization
* Respect for WordPress bloggers everywhere :)