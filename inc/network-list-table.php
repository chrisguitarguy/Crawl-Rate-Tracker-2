<?php
if( ! class_exists( 'WP_List_Table' ) )
{
	require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The list table display class for the crawl rate page.
 */
class CD_Network_Crawl_Rate_List_Table extends CD_Crawl_Rate_List_Table
{
	protected $per_page = 100;
	
	/**
	 * Constructor
	 * 
	 * @since 0.2
	 */
	function __construct()
	{
		parent::__construct();
	}

	
	/**
	 * Get the columns!
	 * 
	 * $column_key => $column_label
	 */
	function get_columns()
	{
		return array(
			'bot'	=> __( 'Bot', 'cdcrt' ),
			'blog'	=> __( 'Blog', 'cdcrt' ),
			'uri'	=> __( 'URL', 'cdcrt' ),
			'type'	=> __( 'Type', 'cdcrt' ),
			'date'	=> __( 'Date', 'cdcrt' ),
			'time'	=> __( 'Time', 'cdcrt' )
		);
	}
	
	function column_blog( $item )
	{
		$blog = get_blog_details( $item->blog_id );
		return $blog->blogname;
	}
}
