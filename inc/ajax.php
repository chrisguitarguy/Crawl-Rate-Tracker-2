<?php
/**
 * The ajax functions for this plugin
 * 
 * @package WordPress
 * @subpackage Crawl Rate Tracker 2
 */

add_action( 'wp_ajax_cd_crt_build_new_graph', 'cd_crt_ajax_build_graph' );
function cd_crt_ajax_build_graph()
{
	require_once( CDCRT_PATH . 'inc/open-flash-chart-display.php' );
	$charturl = admin_url( 'index.php?page=crawl-rate-tracker2&data=true' );
	if( isset( $_REQUEST['bot'] ) && $_REQUEST['bot'] )
	{
		$charturl = add_query_arg( 'bot', $_REQUEST['bot'], $charturl );
	}
	if( isset( $_REQUEST['uri'] ) && $_REQUEST['uri'] )
	{
		$charturl = add_query_arg( 'uri', $_REQUEST['uri'], $charturl );
	}
	if( isset( $_REQUEST['type'] ) && $_REQUEST['type'] )
	{
		$charturl = add_query_arg( 'type', $_REQUEST['type'], $charturl );
	}
	if( isset( $_REQUEST['start_date'] ) && $_REQUEST['start_date'] )
	{
		$charturl = add_query_arg( 'start_date', $_REQUEST['start_date'], $charturl );
	}
	if( isset( $_REQUEST['end_date'] ) && $_REQUEST['send_date'] )
	{
		$charturl = add_query_arg( 'end_date', $_REQUEST['end_date'], $charturl );
	}
	open_flash_chart_object( 800, 500, $charturl, true, CDCRT_URL ); 
	die();
}
