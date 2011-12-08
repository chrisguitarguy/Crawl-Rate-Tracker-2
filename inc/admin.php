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
	
	add_action( 'load-' . $page, 'cd_crt_load_admin_page' );
	add_action( 'admin_print_scripts-' . $page, 'cd_crt_enqueue_scripts' );
	add_action( 'admin_print_scripts-' . $page, 'cd_crt_enqueue_styles' );
}

/**
 * Adds contextual help and hijacks WP if $_GET['data'] is true.  This is
 * for the graphd display
 * 
 * @since 0.2
 */
function cd_crt_load_admin_page()
{
	if( isset( $_GET['data'] ) && 'true' == $_GET['data'] )
	{
		cd_crt_hijack_page_for_data();
	}
}

/**
 * Enqueue's the JS files for our admin page
 * 
 * @since 0.2
 */
function cd_crt_enqueue_scripts()
{
	wp_enqueue_script(
		'crawlratejs',
		CDCRT_URL . 'js/crawlrate.js',
		array( 'jquery', 'jquery-ui-datepicker' ),
		CDCRT_VERSION,
		true
	);
}

function cd_crt_enqueue_styles()
{
	wp_enqueue_style(
		'crawlratecss',
		CDCRT_URL . 'css/crawlrate.css',
		array(),
		CDCRT_VERSION,
		'all'
	);
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

/**
 * The call back function for our page.  This spits out all the output
 * for the admin page.
 * 
 * @since 0.1
 */
function cd_crt_crawl_rate_page_cb()
{
	global $wpdb;
	require_once( CDCRT_PATH . 'inc/open-flash-chart-display.php' );
	?>
	<div class="wrap">
	
		<?php screen_icon(); ?>
		
		<h2><?php _e( 'Crawl Rate Tracker', 'cdcrt' ); ?></h2>
		
		<div id="crt-chart-container">
		
			<?php 
				$charturl = admin_url( 'index.php?page=crawl-rate-tracker2&data=true' );
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
				if( isset( $_GET['start_date'] ) && $_GET['start_date'] )
				{
					$charturl = add_query_arg( 'start_date', $_GET['start_date'], $charturl );
				}
				open_flash_chart_object( 800, 500, $charturl, true, CDCRT_URL ); 
			?>
			
		</div>
		
		<div id="cd-crt-chart-controller-container" class="hide-if-no-js">
			
			<div id="cd-crt-chart-controller">
				
				<h3><?php _e( 'Chart Filter', 'cdcrt' ); ?></h3>
				
				<select name="cd-crt-select-bot" id="cd-crt-select-bot">
					<option value=""><?php _e( 'Select a Bot', 'cdcrt' ); ?></option>
					<?php foreach( array( 'googlebot', 'msnbot', 'yahoo', 'bingbot' ) as $b ): ?>
						<option value="<?php echo $b; ?>"><?php echo cd_crt_get_bot_translated( $b ); ?></option>
					<?php endforeach; ?>
				</select>
				
				<?php
				$table = cd_crt_get_table();
				$types = $wpdb->get_results( "SELECT DISTINCT object_type FROM {$table};", OBJECT_K);
				if( $types ):
				?>
					<select id="cd-crt-select-type" name="cd-crt-select-type">
						<option value=""><?php _e( 'Select a Type', 'cdcrt' ); ?></option>
						<?php foreach( array_keys( $types ) as $type ): ?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo cd_crt_get_type_translated( $type ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				
				<label for="cd-crt-start-date"><?php _e( 'Start Date', 'cdcrt' ); ?></label>
				<input type="text" id="cd-crt-start-date" name="cd-crt-start-date" />
				
				<label for="cd-crt-end-date"><?php _e( 'Start Date', 'cdcrt' ); ?></label>
				<input type="text" id="cd-crt-end-date" name="cd-crt-end-date" />
				
				<a href="javascript:void(null);" id="crt-reload-graph" class="button-secondary"><?php _e( 'Filter', 'cdcrt' ); ?></a>
				
			</div>
			
		
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
	
	if( isset( $_GET['start_date'] ) && $_GET['start_date'] )
	{
		$start_date = date('Y-m-d', strtotime( $_GET['start_date'] ) );
	}
	else
	{
		$start_date = date( 'Y-m-d', strtotime('-30 days' ) );
	}
	if( isset( $_GET['end_date'] ) && $_GET['end_date'] )
	{
		$end_date = date( 'Y-m-d', strtotime( $_GET['end_date'] ) );
	}
	else 
	{
		$end_date = date( 'Y-m-d', strtotime('+1 day' ) );
	}
	
	$crawls = cd_crt_get_crawls(
		array(
			'start_date'	=> $start_date,
			'end_date'		=> $end_date,
			'limit'			=> 'all',
			'bot'			=> isset( $_GET['bot'] ) && $_GET['bot'] ? $_GET['bot'] : 'any',
			'uri'			=> isset( $_GET['uri'] ) && $_GET['uri'] ? $_GET['uri'] : false,
			'type'			=> isset( $_GET['type'] ) && $_GET['type'] ? $_GET['type'] : 'any'
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
	
	$c->set_x_axis_steps( 10 );
	$c->set_x_labels( $range );
	$c->set_x_label_style( 10, '333333', 1, 10 );
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
