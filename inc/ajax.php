<?php
/**
 * The ajax functions for this plugin
 * 
 * @package WordPress
 * @subpackage Crawl Rate Tracker 2
 */

add_action( 'wp_ajax_cd_crt_fetch_data', 'cd_crt_ajax_fetch_data' );
/**
 * Ajax callback to fetch the data for admin page graphs
 * 
 * @since 0.2
 * @uses check_ajax_referer To verify the ajax nonce
 * @uses cd_crt_make_date_rate To fetch a range of data between a given start and end date
 * @uses cd_crt_get_count_for_bot To fetch the count for a given bot
 * @uses cd_crt_extract_crawls To ensure that the dates/values line up
 * @return null
 */
function cd_crt_ajax_fetch_data()
{
    check_ajax_referer( 'cd_crt_ajax_nonce', 'crt_nonce' );
    
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
    
	$range = cd_crt_make_date_rage( $start_date, $end_date, true );
    
    $rv = array(
        'dates'     => $range,
        'totals'    => cd_crt_get_count_for_bot( $start_date, $end_date )
    );
    
    $rv['totals'] = cd_crt_extract_crawls( $range, $rv['totals'] );
    
    foreach( cd_crt_get_bots( true ) as $bot ) 
    {
        $tmp = cd_crt_get_count_for_bot( $start_date, $end_date, $bot );
        $rv[$bot] = cd_crt_extract_crawls( $range, $tmp );
    }
    
    echo json_encode( $rv );
    die();
}
