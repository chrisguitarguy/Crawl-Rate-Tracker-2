<?php

function cd_crt_contextual_help_main()
{
	?>
		<p>
			<?php 
				_e( 'Crawl Rate Tracker 2 is a way for you to view how often the various search engine bots visit your site. ', 'cdcrt' );
				_e( 'Unlike it\'s predecessor, Crawl Rate Tracker, this plugin is compatable with WordPress multsite, and provides a host of unique views and options.', 'cdcrt' );
			?>
		</p>
		<p>
			<?php
				_e( 'Once Crawl Rate 2 is activated, it begins tracking data.  For more information on this, see the "How it Works" tab to the right. ', 'cdcrt' );
				_e( 'How soon you get crawl data will depend on your site.  Larger, more popular sites, may see data as soon as a few minutes after plugin activation. For some smaller sites, it may take a while.', 'cdcrt' );
			?>
		</p>
	<?php
}

function cd_crt_contextual_help_how()
{
	?>
	<p>
		<?php 
			_e( 'Whenever you visit a web page, your browser makes a request to that pages server. ', 'cdcrt' );
			_e( 'With that request, your browser sends several piece of data called "headers". ', 'cdcrt' );
			_e( 'The first header a your browser sends defines the request type and tells the server which URI (web address) it wants, for instances', 'cdcrt' );
		?>
	</p>
	<p>
		<?php
			_e( 'One of the other headers your browser will send is a User Agent string that tells the server which browser is being used', 'cdcrt' );
			_e( 'Just like browsers, search bot sends this same user agent header. ', 'cdcrt' );
			_e( 'That\'s how Crawl Rate 2 works: whenever a request is made, Crawl Rate checks the user agent string to see if that request is coming from a search bot. ', 'cdcrt' );
			_e( 'If it is, the request is logged in the database and show to you here.', 'cdcrt' );
		?>
	</p>
	<p>
		<?php
			_e( 'Unfortunately this is not fool proof. It\'s fairly easy to spoof user age strings. ', 'cdcrt' );
			_e( 'As such, you should take this data, like any other analytics data, as a guide. It should not be the sole way you make decisions about your content.', 'cdcrt' );
		?>
	</p>
	<?php
}

function cd_crt_contextual_help_sidebar()
{

	$out = '<ul>
		<li><a href="https://github.com/chrisguitarguy/Crawl-Rate-Tracker-2/issues">Report Bugs</a></li>
		<li><a href="http://www.christopherguitar.net/">About the Author</a></li>
	</ul>';
	return $out;
}
