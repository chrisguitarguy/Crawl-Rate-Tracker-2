<?php
/**
 * Functions that make various things easier.
 * 
 * @package WordPress
 * @subpackage Crawl Rate Tracker 2
 */


/**
 * Query the crawl rate table and fetch some bots
 * 
 * @since 0.1
 * @return array The result of the Query
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
	if( in_array( $q['bot'], cd_crt_get_bots( true ) ) )
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


/**
 * Return the name of the database table
 * 
 * @since 0.1
 * @return string
 */
function cd_crt_get_table()
{
	global $wpdb;
	return sprintf( '%scd_crawl_rate', $wpdb->base_prefix );
}


/**
 * Make a range of dates going from $start to $end
 * 
 * @since 0.1
 * @return array A list of dates
 */
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


/**
 * Loops through an array of datas and finds the value of 
 * 
 * @since 0.4
 * @return string|int The crawl count
 */
function cd_crt_extract_crawls( $dates, $data )
{
    $rv = array();
    foreach( $dates as $date )
    {
        $rv[] = isset( $data[$date] ) ? $data[$date]->crawl_count : '0';
    }
    return $rv;
}


/**
 * Fetch the count of visits from a given bot grouped by day.
 * 
 * @since 0.4
 * @uses $wpdb->prepare to sanitize all the things
 * @uses $wpdb->get_result to fetch the database
 * @return array
 */
function cd_crt_get_count_for_bot( $start_date, $end_date, $bot = False )
{
    global $wpdb;
    $table = cd_crt_get_table();
    $query = "SELECT crawl_date, count(*) as crawl_count from {$table}";
    $query .= $wpdb->prepare( ' WHERE crawl_date BETWEEN %s AND %s', $start_date, $end_date );
    if( $bot ) 
    {
        $query .= $wpdb->prepare( ' AND user_agent = %s', $bot );
    }
    $query .= ' GROUP BY crawl_date ORDER BY crawl_date ASC;';
    $result = $wpdb->get_results( $query, OBJECT_K );
    return $result;
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
 * @return string The translated bot string
 */
function cd_crt_get_bot_translated( $bot )
{
	$bots = cd_crt_get_bots();
    if( isset( $bots[$bot] ) )
    {
        return $bots[$bot];
    }
	return '';
}


/**
 * Get the list of allowed bots.  This is used anywhere we're going to 
 * loop through bots or figure out which ones to track.
 * 
 *      'botregex' => __( 'Translated Bot Label', 'cdcrt' )
 * 
 * @since 0.5
 * @uses apply_filters Calls cd_crt_bots before returning the bot list
 * @return array List of the bots
 */
function cd_crt_get_bots( $keys_only = false )
{
    $rv = array(
        'googlebot' => __( 'Google', 'cdcrt' ),
        'bingbot'   => __( 'Bing', 'cdcrt' ),
        'yahoo'     => __( 'Yahoo!', 'cdcrt' ),
        'msnbot'    => __( 'MSN', 'cdcrt' )
    );
    
    $rv = apply_filters( 'cd_crt_bots', $rv );
    
    if( $keys_only )
    {
        return array_keys( $rv );
    }
    else
    {
        return $rv;
    }
}
