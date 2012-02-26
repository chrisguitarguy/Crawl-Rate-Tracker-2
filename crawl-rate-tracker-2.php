<?php
/*
Plugin Name: Crawl Rater Tracker 2
Plugin URI: http://www.christopherguitar.net/
Description: An updated, enhanced version of Crawl Rate Tracker.
Version: 0.4.1
Author: Christopher Davis
Author URI: http://pmg.co/people/chris
License: GPL2

	Copyright 2011 Christopher Davis  (email: chris@classicalguitar.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'CDCRT_VERSION', '0.4.1' );
define( 'CDCRT_PATH', plugin_dir_path( __FILE__ ) );
define( 'CDCRT_URL', plugin_dir_url( __FILE__ ) );
define( 'CDCRT_NAME', plugin_basename( __FILE__ ) );
define( 'CDCRT_FOLDER', basename( CDCRT_PATH ) );

require_once( CDCRT_PATH . 'inc/functions.php' );
if( is_admin() )
{
	require_once( CDCRT_PATH . 'inc/github-updater.php' );
	require_once( CDCRT_PATH . 'inc/list-table.php' );
	require_once( CDCRT_PATH . 'inc/network-list-table.php' );
	require_once( CDCRT_PATH . 'inc/admin.php' );
	require_once( CDCRT_PATH . 'inc/help.php' );
	if( defined( 'DOING_AJAX' ) && DOING_AJAX )
	{
		require_once( CDCRT_PATH . 'inc/ajax.php' );
	}
}

add_action( 'shutdown', 'cd_crt_count_bot' );
/**
 * Logs the bot visit
 * 
 * @since 0.1
 * 
 * @uses $wpdb->insert to insert the data
 * @uses cd_crt_get_table to fetch the table name
 */
function cd_crt_count_bot()
{
	$agent = $_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : false;
	
	if( ! $agent ) return;
	if( preg_match( '/(googlebot|bingbot|yahoo|msnbot)/i', $agent, $matches ) )
	{
		$obj = get_queried_object();
		
		$data = array();
		
		global $blog_id;

		$data['blog_id'] = absint( $blog_id );
		
		$data['object_id'] = get_queried_object_id() ? get_queried_object_id() : 0;
		
		$data['uri'] = isset( $_SERVER['REQUEST_URI'] ) ? esc_attr( $_SERVER['REQUEST_URI'] ) : '';
		
		if( is_front_page() )
		{
			$data['object_type'] = 'front';	
		}
		elseif( is_home() )
		{
			$data['object_type'] = 'blog';	
		}
		elseif( is_singular() )
		{
			$data['object_type'] = $obj->post_type;	
		}
		elseif( is_category() || is_tag() || is_tax() )
		{
			$data['object_type'] = $obj->taxonomy;	
		}
		elseif( is_author() )
		{
			$data['object_type'] = 'author';	
		}
		elseif( is_date() || is_time() )
		{
			$data['object_type'] = 'archive';	
		}
		elseif( function_exists( 'is_post_type_archive' ) && is_post_type_archive() )
		{
			$data['object_type'] = 'post_type_archive';	
		}
		elseif( is_404() )
		{
			$data['object_type'] = 'error';	
		}
		elseif( is_search() )
		{
			$data['object_type'] = 'search';	
		}
		else
		{
			$data['object_type'] = 'undefined';
		}
		
		$data['user_agent'] = strtolower( $matches[1] );
		$data['crawl_date'] = date( 'Y-m-d' );
		$data['crawl_time'] = date( 'H:i:s' );
		
		global $wpdb;
		$table = cd_crt_get_table();
		$wpdb->insert( $table, $data );
	}
}

add_action( 'admin_bar_menu', 'cd_crt_add_admin_bar_item', 99 );
/**
 * Add a crawl rate link to the WP admin bar
 * 
 * @since 0.2
 */
function cd_crt_add_admin_bar_item( $wp_admin_bar )
{
	if( ! current_user_can( 'manage_options' ) ) return;
	
	$wp_admin_bar->add_menu(
		array(
			'id' 		=> 'cd-crt2',
			'title'		=> __( 'Crawl Rate', 'cdcrt' ),
			'href'		=> admin_url( 'index.php?page=crawl-rate-tracker2' )
		)
	);
}

register_activation_hook( __FILE__, 'pmg_crt2_activation' );
/**
 * Activation hook.  Create the table and updates a version option.
 * 
 * @since 0.1
 */
function pmg_crt2_activation()
{
	global $wpdb;
	
	$table = cd_crt_get_table();
	$charset = $wpdb->charset ? $wpdb->charset : 'utf8';
	$collate = $wpdb->collate ? $wpdb->collate : 'utf8_general_ci';
	
	$sql = "CREATE TABLE IF NOT EXISTS " . $table . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		object_id bigint(20) DEFAULT '0' NOT NULL,
		blog_id bigint(20) DEFAULT '0' NOT NULL,
		object_type VARCHAR(20) DEFAULT 'undefined' NOT NULL,
		crawl_date DATE DEFAULT '0000-00-00' NOT NULL,
		crawl_time TIME DEFAULT '00:00:00' NOT NULL,
		user_agent VARCHAR(100) DEFAULT '' NOT NULL,
		uri VARCHAR(255) DEFAULT '' NOT NULL,
		UNIQUE KEY id (id)
	) DEFAULT CHARACTER SET " . $charset . " COLLATE " . $collate . ";";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	update_option( 'pmg_crt2_version', CDCRT_VERSION );
}
