<?php
if( ! class_exists( 'WP_List_Table' ) )
{
	require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * The list table display class for the crawl rate page.
 */
class CD_Crawl_Rate_List_Table extends WP_List_Table
{
	protected $per_page = 100;
	
	/**
	 * Constructor
	 * 
	 * @since 0.1
	 * 
	 * @uses remove_query_arg to create a clean url for sorting
	 */
	function __construct()
	{
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		
		$url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url = remove_query_arg( array( 'bot', 'uri', 'type', 'order', 'orderby' ), $url );
		$this->current_url = $url;
		
		parent::__construct();
	}
	
	/**
	 * Get our display items ready to rock
	 * 
	 * @since 0.1
	 * 
	 * @uses cd_crt_get_crawls to fetch the crawls
	 */
	function prepare_items()
	{
		global $wpdb;
		
		$q = $_REQUEST;
		
		$q['limit'] = 1000;
		
		$page = $this->get_pagenum();
		if( 1 != $page )
		{
			$offset = $page * $this->per_page;
		}
		else
		{
			$offset = 0;
		}
		
		$this->all_items = cd_crt_get_crawls( $q );
		
		$this->total = count( $this->all_items );
		
		$this->items = array_slice( $this->all_items, $offset, $this->per_page );
		
		$this->set_pagination_args(
			array(
				'total_items' 	=> $this->total,
				'per_page'		=> $this->per_page,
				'total_pages'	=> $this->total / $this->per_page
			)
		);
	}
	
	/**
	 * Adds a reset link to the top left of the display table and a bit of
	 * feedback on what filters are currently in use.
	 * 
	 * @since 0.1
	 */
	function extra_tablenav( $which )
	{
		$current_filter = '';
		$orders = array( 
			'user_agent' 	=> 'bot', 
			'uri' 			=> 'uri', 
			'object_type'	=> 'type',
			'crawl_date'	=> 'date',
		);
		$cols = $this->get_columns();
		$current_order = '';
		if( isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], array_keys( $orders ) ) )
		{
			$current_order .= sprintf( __( 'Ordered by %s', 'cdcrt' ), $cols[$orders[$_GET['orderby']]] );
			if( isset( $_GET['order'] ) && 'asc' == $_GET['order'] )
			{
				$current_order .= __( ', Ascending', 'cdcrt' );
			}
			else
			{
				$current_order .= __( ', Descending', 'cdcrt' );
			}
		}
		?>
		<div class="alignleft">
			<a href="<?php echo admin_url( 'index.php?page=crawl-rate-tracker2' ); ?>" class="button-secondary" style="width:50px;text-align:center">Reset</a>
			<?php
				echo '<p class="description">';
				if( $current_filter )
				{
					_e( 'Current Filter:', 'cdcrt' );
					echo esc_html( $current_filter ) . '. ';
				}
				if( $current_order )
				{
					_e( 'Current Order: ', 'cdcrt' );
					echo esc_html( $current_order ) . '.';
				}
				echo '</p>';
			?>
		</div>
		<?php
	}
	
	/**
	 * Get the columns!
	 * 
	 * $column_key => $column_label
	 */
	function get_columns()
	{
		return array(
			'bot'	=> __( 'Bot', 'cdcrt' ),
			'uri'	=> __( 'URL', 'cdcrt' ),
			'type'	=> __( 'Type', 'cdcrt' ),
			'page'	=> __( 'Page', 'cdcrt' ),
			'date'	=> __( 'Date', 'cdcrt' ),
			'time'	=> __( 'Time', 'cdcrt' )
		);
	}
	
	/**
	 * Returns the list of columns allowed to be sortable
	 * 
	 * $column_key => array( $orderby_get_value, $order_by_desc_first )
	 * 
	 * @since 0.1
	 */
	function get_sortable_columns()
	{
		return array(
			'uri' 	=> array( 'uri', true ),
			'bot'	=> array( 'user_agent', true ),
			'type'	=> array( 'object_type', true ),
			'date'	=> array( 'crawl_date', true )
		);
	}
	
	/**
	 * If there are not crawls (:-( ) this gets called
	 * 
	 * @since 0.1
	 */
	function no_items()
	{
		return __( 'No bots have visited since you installed this plugin', 'cdcrt' );
	}
	
	/**
	 * Controls the output of the URI column.
	 *
	 * @since 0.1
	 */
	function column_uri( $item )
	{
		return $item->uri;
	}
	
	/**
	 * Controls the output of the type column
	 * 
	 * @since 0.1
	 */
	function column_type( $item )
	{
		return cd_crt_get_type_translated( $item->object_type );
	}
	
	/**
	 * Controls the output of the page column
	 * 
	 * @since 0.2;
	 */
	function column_page( $item )
	{
        $label = __( 'n/a', 'cdcrt' );
		if( in_array( $item->object_type, get_post_types() ) )
		{
			$label = get_the_title( $item->object_id );
		}
		elseif( in_array( $item->object_type, get_taxonomies() ) )
		{
			$term = get_term( $item->object_id, $item->object_type );
            if( $term )
            {
                $label =  $term->name;
            }
		}
		elseif( 'author' == $item->object_type )
		{
			$user = get_user_by( 'id', $item->object_id );
			$label = $user->display_name;
		}

        return $label;
	}
	
	/**
	 * Controls the output of the bot column
	 * 
	 * @since 0.1
	 */
	function column_bot( $item )
	{
        return cd_crt_get_bot_translated( $item->user_agent );
	}
	
	/**
	 * Controls the output of the date column
	 * 
	 * @since 0.1
	 */
	function column_date( $item )
	{
		return date( get_option( 'date_format' ), $this->item_time( $item ) );
	}
	
	/**
	 * Controls the output of the time column
	 * 
	 * @since 0.2
	 */
	function column_time( $item )
	{
		return date( get_option( 'time_format' ), $this->item_time( $item ) );
	}
    
    /**
     * Fetch the timestamp of an item from its crawl_date and crawl_time
     * 
     * @access protected
     * @since 0.5.1
     */
    protected function item_time( $item )
    {
        return strtotime( $item->crawl_date . ' ' . $item->crawl_time );
    }
}
