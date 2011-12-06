Crawl Rate Tracker 2
====================

An updated version of [Crawl Rate Tracker](http://www.blogstorm.co.uk/wordpress-crawl-rate-tracker/) with support for Multisite and a bit better filtering.

This is a work in progress.

TODO
----

1. Add a network admin page
2. Clean up database functions
3. ~~Support sortable columns in the WP_List_Table~~
4. ~~Figure out possible issues with using a bunch of `time()` and `strtotime()` calls.  Eg. time zone?~~
5. ~~Add plugin action link in plugins list table~~
6. ~~Add crawl rate link to wp-admin bar.~~
7. Better presentation in list table
	* ~~Bot should be a translatable string~~
	* ~~Type shoud also be translateable~~
	* add column with specific object (view crawls for a single post, etc)
	* ~~add time column~~
8. Filter graph separately from table.
9. Allow date range selection on graph.
