=== Verse-O-Matic ===
Contributors: cbutlerjr
Donate link: http://butlerblog.com/verse-o-matic/
Tags: feed, plugin, random, quotes, sidebar, widget, verse, VOTD
Requires at least: 2.5
Tested up to: 2.8.4
Stable tag: 4.1.0

This is a plugin to insert a scripture verse into your blog. 

== Description ==

Verse-O-Matic is a dynamic scripture verse display for WordPress with multiple output options.  It may be used to load a random scripture verse every page load, pick a random verse for the day, load a specific scripture verse for a specific day, keep a verse in display until changed by the admin, or choose from various "Verse of the Day" (VOTD) feeds.

Since this plugin includes widget support, no changes are necessary to your theme is it is widget enabled.  You do not need to know html or php to use this plugin.  It is designed to run "out-of-the-box" for even the most novice WP user.

The page for information on this plugin is http://butlerblog.com/verse-o-matic/


== Installation ==

Installation of the Verse-O-Matic plugin is very straight forward.  

1. Upload the verse-o-matic.php file to your /wp-content/plugins/ folder.
2. Go to the "Plugins" menu in your WordPress admin panel.
3. Activate the plugin.
4. If you are able to use widgets, go to the Appearance > Widgets menu.
5. Drag the Verse-O-Matic widget to your sidebar where you would like it to display.
6. Save changes.

Alternate installation instructions for non-widget themes:

1. Follow steps 1 - 3 above.
2. Go to Appearance > Editor to edit your theme via your WordPress admin panel.
3. Select sidebar.php
4. Where you would like the Verse-O-Matic to display, use php to call the verse_o_matic fuction:  verse_o_matic(); (Note: depending on your theme, you may need to wrap this with HTML ordered list markup.)

Now that you have installed the Verse-O-Matic, you may make changes to the settings and add/edit scripture verses in the WP admin panel.  The Verse-O-Matic settings are available as a tab under the "Settings" menu: Settings > Verse-O-Matic.

Manage Settings:

* Display Method: This is where you choose how you would like the Verse-O-Matic to display the scripture you have loaded into it.  There are six settings:

1. Random - displays a random selection every time a page is loaded.
2. Daily Random - if there is no verse with a date that equals today, a new random verse is selected and the date field set.  That verse will display the remainder of the day. It will automatically reset when the date changes.
3. Daily Specific - you have the option of setting the date field yourself.  You may do this to set a verse to display on a specific date.  The order can be set as far in advance as desired, the Verse-O-Matic will do the rest.
4. Static Specific - this will display a specific verse until it is changed manually by the admin.
5. ESV VOTD - this displays the "Verse of the Day" feed from Good News Publishers ESV site.
6. Bible Gateway VOTD - same as (5) above, but uses the Bible Gateway VOTD feed.

* Static ID - This is used in conjunction with the Static Specific display method.  If you have selected Static Specific, you must specify the ID number of the verse you wish to display (ID is noted in the table of verses).

* Turn on alternate version links? - This is set to either "Yes" or "No."  If you choose to display alternate version links, the Verse-O-Matic will determine the translation version (i.e. KJV, NIV, ESV) of the displayed verse and list links to alternate versions on either GoodNews Press (ESV) or Bible Gateway (all others).

* Limit number of alternate version links? - If set to yes, the alternate versions will be limited to four.  This was added for sidebars that might be tight on space and roll the alternate version bar to a second line.

Verses:

These are the verses that are loaded in to your Verse-O-Matic.  Verse-O-Matic installs several examples when the plugin is first activated.  You may use them if you wish, or delete them.  They are merely provided as examples of various features of the Verse-O-Matic.

* This table can be sorted by ID, by Reference (alphabetically), by translation version (alphabetically), or by date.

* The "Link" column indicates if the verse content has a hyperlink.  If a verse is in the Verse-O-Matic, but not set to be visible on the site, the "Visible" column will indicate "No.".  The "Date" Column indicates the date the verse is set to display.  If the verse is the Verse of the Day, the line will be highlighted in green.

* The final two columns allow you to edit or delete a verse.

* Reset Daily Random Verse will reset whatever the random verse of the day is.  CAUTION: this clears ALL dates to NULL.  If you have preset a number of verses to display on specific days, I would advise not using this button since your work will be lost.

Add Verse:

This is where you add new content to the Verse-O-Matic.  Verse, Book, Chapter, Verse, and Version should all be self explanatory.

* Link - this field allows you to set the text of the verse to hyperlink to anything you wish.  If you are linking to a site outside your site, be sure to set the link to http://thedomain.com.  If it's a page in your site, you can make it relative, i.e. /mypage.php  (the Link column above will allow you to check the link).  If you are not using this field you may leave it blank.

* Date - use this if you are setting verses to display on specific dates (and have the display method set to date specific).  Otherwise, leave it blank.

* Visible - if you have set a verse but do not want it to display, set to "No" otherwise leave it at the default - "Yes."

Edit a Verse:

If you need to make edits to verses you've added, select "Edit" in the Verses table.  You will get the same fields as "Add Verse" but they will be populated with the verse to edit.



== Changelog ==

= 4.1.0 =

* Added the ability to use a "Verse of the Day" (VOTD) feed from the ESV (Good News Publishers) or Bible Gateway (NIV or KJV).  These can be selected directly via the admin panel.
* Continued cleanup of the admin panel to accommodate WP's localization features (language translation).
