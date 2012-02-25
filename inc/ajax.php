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
        'totals'    => cd_crt_get_count_for_bot( $start_date, $end_date ),
        'bing'      => cd_crt_get_count_for_bot( $start_date, $end_date, 'bingbot' ),
        'google'    => cd_crt_get_count_for_bot( $start_date, $end_date, 'googlebot' ),
        'yahoo'     => cd_crt_get_count_for_bot( $start_date, $end_date, 'yahoo' ),
        'msn'       => cd_crt_get_count_for_bot( $start_date, $end_date, 'msnbot' )
    );
    
    foreach( array( 'totals', 'bing', 'google', 'yahoo', 'msn' ) as $bot ) 
    {
        while( count( $rv[$bot] ) < count( $range ) )
        {
            $rv[$bot][] = '0';
        }
    }
    
    echo json_encode( $rv );
    die();
}
