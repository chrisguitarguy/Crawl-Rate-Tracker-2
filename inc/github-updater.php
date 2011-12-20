<?php
/**
 * Update Crawl Rate 2 from Github!
 * 
 * Props to Joachim Kudish for the ideas:
 * https://github.com/jkudish/WordPress-GitHub-Plugin-Updater
 */
class CD_Crawl_Rate_Github_Updater
{
	function __construct()
	{
		$this->config = array(
			'api_url' 	=> 'https://api.github.com/repos/chrisguitarguy/Crawl-Rate-Tracker-2/%s',
			'github'	=> 'https://github.com/chrisguitarguy/Crawl-Rate-Tracker-2/',
			'requires'	=> '3.3',
			'tested'	=> '3.3',
		);
		
		add_action( 'init', array( &$this, 'setup_data' ) );
		
		add_filter( 'site_transient_update_plugins', array( &$this, 'hijack_transient' ) );
		add_filter( 'plugins_api', array( &$this, 'hijack_api' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( &$this, 'post_install' ), 10, 3 );
	}
	
	
	/**
	 * Fetches tag data from github to find our new version.
	 * 
	 * @uses wp_remote_get
	 */
	function setup_data()
	{
		if( WP_DEBUG ) $this->delete_stuff();
		
		if( $tags = get_site_transient( 'cdcrt_github_tags' ) )
		{
			$this->compare_tags( $tags );
			return;
		}
		
		$tagresp = wp_remote_get( sprintf( $this->config['api_url'], 'tags' ) );
		
		if( is_wp_error( $tagresp ) || 200 != $tagresp['response']['code'] ) 
			return;
		
		$tags = json_decode( $tagresp['body'] );
		set_site_transient( 'cdcrt_github_tags', $tags, 60*60*24 );
		$this->compare_tags( $tags );
	}
	
	
	/**
	 * Filter the update_plugins transient to include our plugin if
	 * necessary.
	 */
	function hijack_transient( $value )
	{
		
		if( empty( $value->checked ) ) return $value;
		
		if( $this->new_version ) 
		{
			$r = new stdClass;
			$r->new_version = $this->new_version;
			$r->slug = CDCRT_NAME;
			$r->url = $this->config['github'];
			$r->package = $this->update_url;
			$value->response[CDCRT_NAME] = $r;
		}
		return $value;
	}
	
	/**
	 * Hijack the plugin api and return our own stuff
	 */
	function hijack_api( $false, $action, $args )
	{
		if( $args->slug != CDCRT_NAME ) return false;
		$plugins = get_plugins();
		$data = $plugins[CDCRT_NAME];
		
		$r = new stdClass();
		$r->slug = CDCRT_NAME;
		$r->tested = $this->config['tested'];
		$r->version = $this->new_version;
		$r->requires = $this->config['requires'];
		$r->downloaded = 0;
		$r->download_link = $this->update_url;
		$r->author = $data['Author'];
		$r->homepage = $data['PluginURI'];
		$r->last_updated = __( 'unknown', 'cdcrt' );
		$r->sections = array(
			'description' => $data['Description']
		);
		return $r;
	}
	
	
	/**
	 * Github sends a folder name with a super weird name.  We'll move it 
	 * to the real folder name
	 */
	function post_install( $true, $hook_extra, $result )
	{
		global $wp_filesystem;
		$proper_destination = trailingslashit( WP_PLUGIN_DIR ) . CDCRT_FOLDER;
		$wp_filesystem->move( $result['destination'], $proper_destination) ;
		$result['destination'] = $proper_destination;
		$activate = activate_plugin( trailingslashit( WP_PLUGIN_DIR ) . CDCRT_NAME );
		if (is_wp_error($activate)) 
		{
			_e( 'The plugin was updated, but could not be reactivated.', 'cdcrt' );
		} 
		else 
		{
			_e( 'Plugin reactivated successfully', 'cdcrt' );
		}
		return $result;
	}
	
	
	/**
	 * When WP_DEBUG is true, this function deletes our transients, and 
	 * the update_plugins site transient
	 */
	function delete_stuff()
	{
		delete_site_transient( 'cdcrt_github_tags' );
	}
	
	
	/**
	 * Utility Function, run through a list of tags from the github api
	 * and see if we have a new version.  Only works with sensible tag
	 * names.
	 * 
	 * @uses version_compare
	 */
	protected function compare_tags( $tags )
	{
		$this->new_version = false;
		foreach( (array) $tags as $tag )
		{
			if( version_compare( CDCRT_VERSION, $tag->name ) === -1 )
			{
				$this->new_version = $tag->name;
				$this->update_url = $tag->zipball_url;
			}
		}
	}
} // end class

new CD_Crawl_Rate_Github_Updater();
