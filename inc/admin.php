<?php
add_action( 'admin_menu', 'cd_crt_crawl_rate_page' );
function cd_crt_crawl_rate_page()
{
	$page = add_dashboard_page(
		__( 'Crawl Rate Tracker', 'cdcrt' ),
		__( 'Crawl Rate', 'cdcrt' ),
		'manage_options',
		'crawl-rate-tracker2',
		'cd_crt_crawl_rate_page_cb'
	);
}

function cd_crt_crawl_rate_page_cb()
{
	if( isset( $_GET['data'] ) && 'true' == $_GET['data'] )
	{
		cd_crt_hijack_page_for_data();
	}
	require_once( CDCRT_PATH . 'inc/open-flash-chart-display.php' );
	?>
	<div class="wrap">
	
		<?php screen_icon(); ?>
		
		<h2><?php _e( 'Crawl Rate Tracker', 'cdcrt' ); ?></h2>
		
		<div class="chart" style="width:800px; margin: 0 auto;">
		
			<?php 
				$charturl = admin_url( 'index.php?page=crawl-rate-tracker2&noheader=true&data=true' );
				if( isset( $_GET['bot'] ) && $_GET['bot'] )
				{
					$charturl = add_query_arg( 'bot', $_GET['bot'], $charturl );
				}
				elseif( isset( $_GET['uri'] ) && $_GET['uri'] )
				{
					$charturl = add_query_arg( 'uri', $_GET['uri'], $charturl );
				}
				elseif( isset( $_GET['type'] ) && $_GET['type'] )
				{
					$charturl = add_query_arg( 'type', $_GET['type'], $charturl );
				}
				open_flash_chart_object( 800, 500, $charturl, true, CDCRT_URL ); 
			?>
			
		</div>
		
		<br clear="both" />
		<?php
			$list = new CD_Crawl_Rate_List_Table();
			$list->prepare_items();
			$list->display();
		?>
	</div>
	<?php
}

function cd_crt_hijack_page_for_data()
{
	require_once( CDCRT_PATH . 'inc/open-flash-chart.php' );
	
	$crawls = cd_crt_get_crawls(
		array(
			'start_date'	=> date( 'Y-m-d', strtotime('-30 days' ) ),
			'end_date'		=> date( 'Y-m-d', strtotime('+1 day' ) ),
			'limit'			=> 'all',
			'bot'			=> isset( $_GET['bot'] ) && $_GET['bot'] ? $_GET['bot'] : 'any',
			'uri'			=> isset( $_GET['uri'] ) && $_GET['uri'] ? $_GET['uri'] : false,
			'type'			=> isset( $_GET['type'] ) && $_GET['type'] ? $_GET['type'] : 'any'
		)
	);
	
	$range = cd_crt_make_date_rage( strtotime('-30 days' ), time(), false );
	
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
	
	$max = 0;
	foreach( $data as $d )
	{
		if( $d > $max ) $max = $d;
	}
	$max += 50;
	
	$c = new CD_Crawl_Rate_Graph();
	
	$c->set_bg_colour( 'FFFFFF' );
	
	//$c->title( __( 'The Last Thirty Days', 'cdcrt' ), '{font-size: 26px, font-weight: normal}' );
	
	$c->set_data( $data );
	$c->line_dot( 2, 3, 'FF0000', __( 'Overall', 'cdcrt' ), 10 );
	
	$c->set_data( $google );
	$c->line_dot( 2, 3, '1111CC', __( 'Google', 'cdcrt' ), 10 );
	
	$c->set_data( $bing );
	$c->line_dot( 2, 3, 'F76120', __( 'Bing', 'cdcrt' ), 10 );
	
	$c->set_data( $yahoo );
	$c->line_dot( 2, 3, '7B0099', __( 'Yahoo', 'cdcrt' ), 10 );
	
	$c->set_data( $msn );
	$c->line_dot( 2, 3, '009AD9', __( 'MSN', 'cdcrt' ), 10 );
	
	$c->set_x_labels( $range );
	
	$c->set_x_label_style( 12, '333333', 1 );
	
	$c->x_axis_colour( '222222', 'dfdfdf' );
	
	$c->set_y_max( $max );
	
	$c->y_label_steps( absint( $max / 50 ) );
	
	$c->set_y_label_style( 12, '333333' );
	
	$c->y_axis_colour( '222222', 'dfdfdf' );
	
	echo $c->render();

	exit();
}

add_filter( 'plugin_action_links_' . CDCRT_NAME, 'cd_crt_plugin_actions' );
/**
 * Add a link to the plugin list table.
 * 
 * @since 0.2
 */
function cd_crt_plugin_actions( $actions )
{
	$actions['crawls'] = '<a href="' . admin_url( 'index.php?page=crawl-rate-tracker2' ) . '">' . __( 'View Crawls', 'cdcrt' ) . '</a>';
	return $actions;
}
