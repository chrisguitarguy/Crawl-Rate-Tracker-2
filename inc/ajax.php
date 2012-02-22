<?php
/**
 * The ajax functions for this plugin
 * 
 * @package WordPress
 * @subpackage Crawl Rate Tracker 2
 */

add_action( 'wp_ajax_cd_crt_fetch_data', 'cd_crt_ajax_fetch_data' );
function cd_crt_ajax_fetch_data()
{
	if( isset( $_POST['start_date'] ) && $_POST['start_date'] )
	{
		$start_date = date('Y-m-d', strtotime( $_POST['start_date'] ) );
	}
	else
	{
		$start_date = date( 'Y-m-d', strtotime('-30 days' ) );
	}
    
	if( isset( $_POST['end_date'] ) && $_POST['end_date'] )
	{
		$end_date = date( 'Y-m-d', strtotime( $_POST['end_date'] ) );
	}
	else 
	{
		$end_date = date( 'Y-m-d');
	}
    
    $crawls = cd_crt_get_crawls(
		array(
			'start_date'	=> $start_date,
			'end_date'		=> $end_date,
			'limit'			=> 'all',
			'bot'			=> isset( $_POST['bot'] ) && $_POST['bot'] ? $_POST['bot'] : 'any',
			'uri'			=> isset( $_POST['uri'] ) && $_POST['uri'] ? $_POST['uri'] : false,
			'type'			=> isset( $_POST['type'] ) && $_POST['type'] ? $_POST['type'] : 'any',
			'object_id'		=> isset( $_POST['object_id'] ) && $_POST['object_id'] ? $_POST['object_id'] : false,
            'blog_id'       => isset( $_POST['blog_id'] ) && $_POST['blog_id'] ? $_POST['blog_id'] : false
		)
	);
	
	$range = cd_crt_make_date_rage( $start_date, $end_date, true );
    
    $data = array();
	$bing = array();
	$msn = array();
	$yahoo = array();
	$google = array();
	foreach( $range as $date )
	{
		$data[] = cd_crt_get_count_for_date( $date, $crawls );
		$temp = cd_crt_get_bots_for_date( $date, $crawls );
		$bing[] = $temp['bing'];
		$msn[] = $temp['msn'];
		$yahoo[] = $temp['yahoo'];
		$google[] = $temp['google'];
	}
    
    echo json_encode( array(
        'dates'     => $range,
        'totals'    => $data,
        'bing'      => $bing,
        'google'    => $google,
        'yahoo'     => $yahoo,
        'msn'       => $msn
    ) );
    die();
}
