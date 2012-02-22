<?php
/**
 * Functions that make various things easier.
 * 
 * @package WordPress
 * @subpackage Crawl Rate Tracker 2
 */

function cd_crt_get_crawls( $args, $count = false )
{
	global $wpdb, $blog_id;
	$table = cd_crt_get_table();
	$columns = array( 'id', 'object_id', 'blog_id', 'object_type', 'crawl_date', 'user_agent', 'uri' );
	
	$q = wp_parse_args(
		$args,
		array(
			'bot'			=> 'any',
			'start_date'	=> false,
			'end_date'		=> date( 'Y-m-d' ),
			'limit'			=> 40,
			'offset'		=> 0,
			'type'			=> 'any',
			'uri'			=> false,
			'orderby'		=> 'crawl_date',
			'order'			=> 'DESC',
			'blog_id'		=> false,
			'object_id'		=> false
		)
	);
	
	$query = " FROM {$table}";
	if( in_array( $q['bot'], array( 'msnbot', 'bingbot', 'googlebot', 'yahoo' ) ) )
	{
		$bot = $wpdb->prepare( "user_agent = %s", $q['bot'] );
	}
	else
	{
		$bot = '';
	}
	
	if( $q['type'] != 'any' )
	{
		$type = $wpdb->prepare( "object_type = %s", $q['type'] );
	}
	else
	{
		$type = '';
	}
	
	if( $q['uri'] )
	{
		$uri = $wpdb->prepare( "uri = %s", $q['uri'] );
	}
	else
	{
		$uri = '';
	}
	
	if( $q['start_date'] )
	{
		$date = $wpdb->prepare( "crawl_date BETWEEN %s AND %s", $q['start_date'], $q['end_date'] );
	}
	else
	{
		$date = '';
	}
	
	if( isset( $q['object_id'] ) && $q['object_id'] )
	{
		$oid = $wpdb->prepare( "object_id = %d", $q['object_id'] );
	}
	else
	{
		$oid = '';
	}
	
	if( function_exists( 'is_multisite' ) && is_multisite() && is_network_admin() )
	{
		if( isset( $q['blog_id'] ) && $q['blog_id'] )
		{
			$where = $wpdb->prepare( " WHERE blog_id = %d", $q['blog_id'] );
		}
		else
		{
			$where = "";
		}
	}
	else
	{
		$where = $wpdb->prepare( " WHERE blog_id = %d", absint( $blog_id ) );
	}

	if( $bot )
	{
		if( $where )
		{
			$where .= ' AND ' . $bot;
		}
		else
		{
			$where = ' WHERE ' . $bot;
		}
	}
	if( $type )
	{
		if( $where )
		{
			$where .= ' AND ' . $type;
		}
		else
		{
			$where .= ' WHERE ' . $type;
		}
	}
	if( $uri )
	{
		if( $where )
		{
			$where .= ' AND ' . $uri;
		}
		else
		{
			$where .= ' WHERE ' . $uri;
		}
	}
	if( $date )
	{
		$where .= ' AND ' . $date;
	}
	if( $oid )
	{
		if( $where )
		{
			$where .= ' AND ' . $oid;
		}
		else
		{
			$where .= ' WHERE ' . $oid;
		}
	}
	
	if( 'all' == $q['limit'] )
	{
		$limit = '';
	}
	elseif( absint( $q['limit'] ) )
	{
		$limit = $wpdb->prepare( " LIMIT %d", $q['limit'] );
	}
	else
	{
		$limit = '';
	}
	
	if( $q['offset'] && $limit )
	{
		$offset = $wpdb->prepare( " OFFSET %d", $q['offset'] );
	}
	else 
	{
		$offset = '';
	}
	
	if( in_array( $q['orderby'], $columns ) )
	{
		$orderby = " ORDER BY {$q['orderby']}";
	}
	else
	{
		$orderby = " ORDER BY crawl_date";
	}
	$orderby .= 'asc' == strtolower( $q['order'] ) ? ' ASC ' : ' DESC ';
	
	// hack, secondary order by for time
	$orderby .= ', crawl_time DESC ';
	
	$query = $query . $where . $orderby . $limit . $offset . ';';
	
	if( $count )
	{
		return $wpdb->get_var( "SELECT count(*)" . $query ); 
	}
	return $wpdb->get_results( "Select *" . $query, OBJECT );
}

function cd_crt_get_table()
{
	global $wpdb;
	return sprintf( '%scd_crawl_rate', $wpdb->base_prefix );
}

function cd_crt_make_date_rage( $start, $end, $convert = true )
{
	if( $convert )
	{
		$start = strtotime( $start );
		$end = strtotime( $end );
	}
	
	$out = array( date( 'Y-m-d', $start ) );
	
	$t = $start + 86400;
	while( $t <= $end )
	{
		$out[] = date( 'Y-m-d', $t );
		$t += 86400;
	}
	return $out;
}

function cd_crt_get_count_for_date( $date, $data = array(), $to_db = false )
{
	if( $to_db )
	{
		global $wpdb;
		return $wpdb->get_var(
			$wpdb->pepare(
				"SELECT count(*) WHERE crawl_date = %s", 
				$date
			)
		);
	}
	$count = 0;
	foreach( $data as $item )
	{
		if( $date == $item->crawl_date )
		{
			$count += 1;
		}
	}
	return absint( $count );
}

function cd_crt_get_bots_for_date( $date, $items, $to_db = false )
{
	if( $to_db )
	{
		global $wpdb;
		$bing = $wpdb->get_var(
			$wpdb->pepare(
				"SELECT count(*) WHERE crawl_date = %s AND user_agent = %s", 
				$date,
				'bingbot'
			)
		);
		$goog = $wpdb->get_var(
			$wpdb->pepare(
				"SELECT count(*) WHERE crawl_date = %s AND user_agent = %s", 
				$date,
				'googlebot'
			)
		);
		$yahoo = $wpdb->get_var(
			$wpdb->pepare(
				"SELECT count(*) WHERE crawl_date = %s AND user_agent = %s", 
				$date,
				'yahoo'
			)
		);
		$msn = $wpdb->get_var(
			$wpdb->pepare(
				"SELECT count(*) WHERE crawl_date = %s AND user_agent = %s", 
				$date,
				'msnbot'
			)
		);
		
		return array( 
			'google'	=> $goog, 
			'yahoo'		=> $yahoo, 
			'msn'		=> $msn, 
			'bing'		=> $bing 
		);
	}
	
	$msn = 0;
	$goog = 0;
	$yahoo = 0;
	$bing = 0;
	foreach( $items as $item )
	{
		if( $item->crawl_date != $date ) continue;
		switch( $item->user_agent )
		{
			case 'googlebot':
				$goog += 1;
				break;
			case 'msnbot':
				$msn += 1;
				break;
			case 'bingbot':
				$bing += 1;
				break;
			case 'yahoo':
				$yahoo += 1;
				break;
		}
	}
	return array( 
		'google'	=> $goog, 
		'yahoo'		=> $yahoo, 
		'msn'		=> $msn, 
		'bing'		=> $bing 
	);
}

/**
 * Returns a translated string version of the object_type database field
 * 
 * @since 0.2
 */
function cd_crt_get_type_translated( $item )
{
	$labels = array(
		'front'					=> __( 'Front Page', 'cdcrt' ),
		'blog'					=> __( 'Blog Page', 'cdcrt' ),
		'author'				=> __( 'Author Archive', 'cdcrt' ),
		'post_type_archive'		=> __( 'Post Type Archive', 'cdcrt' ),
		'archive'				=> __( 'Date Archive', 'cdcrt' ),
		'error'					=> __( 'Error Page (404)', 'cdcrt' ),
		'search'				=> __( 'Search Page', 'cdcrt' ),
		'undefined'				=> __( 'Who Knows?', 'cdcrt' )
	);
	
	if( isset( $labels[$item] ) )
	{
		return $labels[$item];
	}
	elseif( in_array( $item, get_post_types() ) )
	{
		$type = get_post_type_object( $item );
		$label = isset( $type->label ) && $type->label ? $type->label : $item;
		return sprintf( __( 'Singular: %s', 'cdcrt' ), $label );
	}
	elseif( in_array( $item, get_taxonomies() ) )
	{
		$tax = get_taxonomy( $item );
		$label = isset( $tax->name ) ? $tax->label : $item;
		return sprintf( __( 'Taxonomy Archive: %s', 'cdcrt' ), $label );
	}
	return '';
}

/**
 * Return a translated string version of user_agent from the database
 * 
 * @since 0.2
 */
function cd_crt_get_bot_translated( $bot )
{
	switch( $bot )
	{
		case 'googlebot':
			return __( 'Google', 'cdcrt' );
			break;
		case 'msnbot':
			return __( 'MSN', 'cdcrt' );
			break;
		case 'yahoo':
			return __( 'Yahoo!', 'cdcrt' );
			break;
		case 'bingbot':
			return __( 'Bing', 'cdcrt' );
			break;
	}
	return '';
}
