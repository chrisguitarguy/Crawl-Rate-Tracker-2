<?php
add_action( 'admin_menu', 'cd_crt_crawl_rate_page' );
add_action( 'network_admin_menu', 'cd_crt_crawl_rate_page' );
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
	add_action( 'admin_print_styles-' . $page, 'cd_crt_enqueue_styles' );
}

/**
 * Adds contextual help and hijacks WP if $_GET['data'] is true.  This is
 * for the graphd display
 * 
 * @since 0.2
 */
function cd_crt_load_admin_page()
{	
	$screen = get_current_screen();
	$screen->add_help_tab(
		array(
			'id'		=> 'crawl-rate-main-help',
			'title'		=> __( 'About Crawl Rate 2', 'cdcrt' ),
			'callback'	=> 'cd_crt_contextual_help_main'
		)
	);
	$screen->add_help_tab(
		array(
			'id'		=> 'crawl-rate-how-it-works', 
			'title'		=> __( 'How it Works', 'cdcrt' ),
			'callback'	=> 'cd_crt_contextual_help_how'
		)
	);
	$screen->set_help_sidebar( cd_crt_contextual_help_sidebar() );
}

/**
 * Enqueue's the JS files for our admin page
 * 
 * @since 0.2
 */
function cd_crt_enqueue_scripts()
{
    wp_enqueue_script(
        'raphaeljs',
        CDCRT_URL . 'js/raphael.js',
        array(),
        NULL,
        true
    );
    
    wp_enqueue_script(
        'icojs',
        CDCRT_URL . 'js/ico.js',
        array( 'raphaeljs' ),
        NULL,
        true
    );
    
    wp_enqueue_script(
		'crawlratejs',
		CDCRT_URL . 'js/crawlrate.js',
		array( 'jquery', 'jquery-ui-datepicker', 'icojs' ),
		CDCRT_VERSION,
		true
	);
    
    wp_localize_script(
        'crawlratejs',
        'crawlrate_data',
        array(
            'loader'    => CDCRT_URL . 'images/loader.gif',
            'nonce'     => wp_create_nonce( 'cd_crt_ajax_nonce' )
        )
    );
}

/**
 * Enqueue the CSS files for our admin page
 * 
 * @since 0.2
 */
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
	?>
	<div class="wrap">
	
		<?php screen_icon(); ?>
		
		<h2><?php _e( 'Crawl Rate Tracker', 'cdcrt' ); ?></h2>
		
		<div id="crt-chart-container" class="hide-if-no-js">
            <h3 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="#" rel="crt-totals">Total</a>
                <a class="nav-tab" href="#" rel="crt-google">Google</a>
                <a class="nav-tab" href="#" rel="crt-bing">Bing</a>
                <a class="nav-tab" href="#" rel="crt-yahoo">Yahoo</a>
                <a class="nav-tab" href="#" rel="crt-msn">MSN</a>
            </h3>
            <div class="cd-crt-tab-holder">
                <div class="cd-crt-loader">
                    <img src="<?php echo CDCRT_URL; ?>images/loader.gif" alt="loader" />
                </div>
                <div class="cd-crt-tab" id="crt-totals">
                
                </div>
                <div class="cd-crt-tab" id="crt-google">
                
                </div>
                <div class="cd-crt-tab" id="crt-bing">
                
                </div>
                <div class="cd-crt-tab" id="crt-yahoo">
                
                </div>
                <div class="cd-crt-tab" id="crt-msn">
                
                </div>
            </div>
		</div>
		
		<div id="cd-crt-chart-controller-container" class="hide-if-no-js">
			
			<div id="cd-crt-chart-controller">
				
				<h3><?php _e( 'Chart Date Filter', 'cdcrt' ); ?></h3>
				
				<label for="cd-crt-start-date"><?php _e( 'Start Date', 'cdcrt' ); ?></label>
				<input type="text" id="cd-crt-start-date" name="cd-crt-start-date" />
				
				<label for="cd-crt-end-date"><?php _e( 'End Date', 'cdcrt' ); ?></label>
				<input type="text" id="cd-crt-end-date" name="cd-crt-end-date" />
				
				<a href="javascript:void(null);" id="crt-reload-graph" class="button-secondary"><?php _e( 'Filter', 'cdcrt' ); ?></a>
				
			</div>
			
		
		</div>
		
		<br clear="both" />
		<?php
			
			$list = is_network_admin() ? new CD_Network_Crawl_Rate_List_Table() : new CD_Crawl_Rate_List_Table();
			$list->prepare_items();
			$list->display();
		?>
	</div>
	<?php
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
