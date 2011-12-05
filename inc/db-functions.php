<?php
/**
 * @todo make the functions in this file less shitty?
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
			'order'			=> 'DESC'
		)
	);
	
	$query = $wpdb->prepare( " FROM {$table} WHERE blog_id = %s", $blog_id );
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
		$date = $wpdb->prepare( " crawl_date BETWEEN %s AND %s", $q['start_date'], $q['end_date'] );
	}
	else
	{
		$date = '';
	}
	
	$where = '';
	if( $bot )
	{
		$where .= ' AND ' . $bot;
	}
	if( $type )
	{
		$where .= ' AND ' . $type;
	}
	if( $uri )
	{
		$where .= ' AND ' . $uri;
	}
	if( $date )
	{
		$where .= 'AND '. $date;
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
	$orderby .= 'ASC' == $q['order'] ? ' ASC ' : ' DESC ';
	
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
	return $count;
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
