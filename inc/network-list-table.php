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
			'page'	=> __( 'Page', 'cdcrt' ),
			'date'	=> __( 'Date', 'cdcrt' ),
			'time'	=> __( 'Time', 'cdcrt' )
		);
	}
	
	function column_blog( $item )
	{
		$link = sprintf( 
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'blog_id', $item->blog_id, $this->current_url ) ),
			'blah'
		);
		return $link;
	}
	
	function column_page( $item )
	{
		$link = false;
		$label = '';
		if( in_array( $item->object_type, get_post_types() ) )
		{
			$link = add_query_arg( 'type', $item->object_type, $this->current_url );
			$link = add_query_arg( 'object_id', $item->object_id, $link );
			$label = get_the_title( $item->object_id );
		}
		elseif( in_array( $item->object_type, get_taxonomies() ) )
		{
			$link = add_query_arg( 'type', $item->object_type, $this->current_url );
			$link = add_query_arg( 'object_id', $item->object_id, $link );
			$term = get_term( $item->object_id, $item->object_type );
			$label =  $term->name;
		}
		elseif( 'author' == $item->object_type )
		{
			$user = get_user_by( 'id', $item->object_id );
			$link = add_query_arg( 'object_id', $item->object_id, $link );
			$label = $user->display_name;
		}
		else
		{
			$label = __( 'n/a', 'cdcrt' );
		}
		
		if( $link )
		{
			return sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $label ) );
		}
		else
		{
			return $label;
		}
	}
	
}
