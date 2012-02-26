Crawl Rate Tracker 2
====================

An updated version of [Crawl Rate Tracker](http://www.blogstorm.co.uk/wordpress-crawl-rate-tracker/) with support for WordPress Multisite, nicer graphs, the ability to filter by date, and tracking for many more search bots.

![Crawl Rate Graphs](https://raw.github.com/chrisguitarguy/Crawl-Rate-Tracker-2/master/screenshot-1.png)

![Crawl Rate List Table](https://raw.github.com/chrisguitarguy/Crawl-Rate-Tracker-2/master/screenshot-2.png)

Installation
------------

1. [Download](https://github.com/chrisguitarguy/Crawl-Rate-Tracker-2/zipball/0.5.3) the latest version
2. Back up your WordPress database. This plugin adds a table, so it's a good idea to back up before installing
3. Unzip and upload the plugin folder to your `wp-content/plugins` directory or install the zip file via the WordPress admin area
4. Activate the plugin!

FAQ
---
**Can I use this with WordPress Multisite?**

Yes.  It should work fine for Multisite.  I'm using it on a client's multisite installation right now!

**How does this thing work?**

When a client visits your site, they send a user agent string.  This plugin looks in that string and searches for patterns that match several known search bots. This is, of course, extremely easy to spoof.  The numbers you get from this plugin should be used just like any other web analytics: good information, but not the sole factor in making decisions.

**Does this track when bots view my images and static files?**

No.  Anytime a request goes through your WordPress install itself, the tracker will work.  Static files and the like don't go through WordPress at all.  You'll have to dig into your server logs to get that info.

**What search bots does this thing track?**

- Google
- Bing
- Yahoo!
- MSN
- blekko
- Duck Duck Go
- Yandex
- Baidu

**This plugin isn't in the WP.org repo, how will I get updates?!**

You'll get them from this Git repo. About once a day your site "phones out" to the WordPress plugin and theme repository checking to see if any updates are available.  In the case of Crawl Rate Tracker 2, the HTTP request will be made to the github API.  If it finds a new version, you'll get an upgrade notice.  Upgrading will be just the same as upgrading a plugin in the WordPress.org repository.

If you have problems upgrading due to the WordPress HTTP API not recognizing github's SSL certificate, you can use [this work around](https://gist.github.com/1500624).

Adding & Removing Bot Tracking
------------------------------

Say you don't care about tracking Yandex and would like to remove it.  Hook into the filter `cd_crt_bots` to modify which bots get tracked.  If you want to remove yandex.

    <?php
    add_filter( 'cd_crt_bots', 'your_bot_filter_function' );
    function your_bot_filter_function( $bots )
    {
        if( isset( $bots['yandexbot'] ) ) unset( $bots['yandexbot'] );
        return $bots;
    }

In the same way, you can add bots.  Use the regex that will match the bot's user agent string as the array key, and the proper name as the value. Let's add Alexa's bot to our list.

    <?php
    add_filter( 'cd_crt_bots', 'add_your_own_bots' );
    function add_your_own_bots( $bots )
    {
        $bots['ia_archiver'] = __( 'Alexa Bot', 'yourtextdomain' );
        return $bots;
    }

The plugin will take care of the rest.  [This](http://www.useragentstring.com/) is a good site to identify the variosu user agent strings.  This means, of course, that you could use this plugin to track things like browser usage.  That's probably not a great idea -- Google Analytics does that for you anyway.

License
-------

Crawl Rate Tracker 2 is licensed under the GPL version 2.  A full copy of the license can be found in [`license.txt`](https://github.com/chrisguitarguy/Crawl-Rate-Tracker-2/blob/master/license.txt).  
