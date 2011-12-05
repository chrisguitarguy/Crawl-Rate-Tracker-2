<?php
if( ! class_exists( 'WP_List_Table' ) )
{
	require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-list-table.php' );
}

class CD_Crawl_Rate_List_Table extends WP_List_Table
{
	protected $per_page = 100;
	
	function __construct()
	{
		$this->_column_headers = array( $this->get_columns(), array(), array() );
		
		$url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url = remove_query_arg( array( 'bot', 'uri', 'type' ), $url );
		$this->current_url = $url;
		
		parent::__construct();
	}
	
	function prepare_items()
	{
		global $wpdb;
		
		$q = $_REQUEST;
		
		$q['limit'] = 'all';
		$this->total = cd_crt_get_crawls( $q, true );
		
		$page = $this->get_pagenum();
		$q['limit'] = $this->per_page;
		if( 1 != $page )
		{
			$q['offset'] = ( $page -1 ) * $q['limit'];
		}
		
		$this->all_items = cd_crt_get_crawls( $q );
		
		$this->items = cd_crt_get_crawls( $q );
		
		$this->set_pagination_args(
			array(
				'total_items' 	=> $this->total,
				'per_page'		=> $this->per_page,
				'total_pages'	=> $this->total / $this->per_page
			)
		);
	}
	
	function extra_tablenav( $which )
	{
		?>
		<div class="alignleft">
			<a href="<?php echo admin_url( 'index.php?page=crawl-rate-tracker2' ); ?>" class="button-secondary">Reset</a>
		</div>
		<?php
	}
	
	function get_columns()
	{
		return array(
			'bot'	=> __( 'Bot', 'cdcrt' ),
			'uri'	=> __( 'URL', 'cdcrt' ),
			'type'	=> __( 'Type', 'cdcrt' ),
			'date'	=> __( 'Date', 'cdcrt' )
		);
	}
	
	function get_sortable_columns()
	{
		return array();
	}
	
	function no_items()
	{
		return __( 'No bots have visited since you installed this plugin', 'cdcrt' );
	}
	
	function column_uri( $item )
	{
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'uri', urlencode( $item->uri ), $this->current_url ) ),
			$item->uri
		);
		return $link;
	}
	
	function column_type( $item )
	{
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'type', $item->object_type, $this->current_url ) ),
			$item->object_type
		);
		return $link;
	}
	
	function column_bot( $item )
	{
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'bot', urlencode( $item->user_agent ), $this->current_url ) ),
			$item->user_agent
		);
		return $link;
	}
	
	function column_date( $item )
	{
		return date( get_option( 'date_format' ), strtotime( $item->crawl_date ) );
	}
}
