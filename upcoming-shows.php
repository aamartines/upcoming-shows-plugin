<?php
/*
Plugin Name: Upcoming Shows
Plugin URL:  https://github.com/aamartines/upcoming-shows-plugin
Description: A CPT for upcoming events
Version:     1.0.0
Author:      Alexandra Martines & Megan Valcour
Author URI:  alexandramartines.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: upcoming-shows
*/

/**
 * Register a shows post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function upcomingshows_shows_init() {
	$labels = array(
		'name'               => _x( 'Shows', 'post type general name', 'upcoming-shows' ),
		'singular_name'      => _x( 'Show', 'post type singular name', 'upcoming-shows' ),
		'menu_name'          => _x( 'Shows', 'admin menu', 'upcoming-shows' ),
		'name_admin_bar'     => _x( 'Show', 'add new on admin bar', 'upcoming-shows' ),
		'add_new'            => _x( 'Add New', 'show', 'upcoming-shows' ),
		'add_new_item'       => __( 'Add New Show', 'upcoming-shows' ),
		'new_item'           => __( 'New Show', 'upcoming-shows' ),
		'edit_item'          => __( 'Edit Show', 'upcoming-shows' ),
		'view_item'          => __( 'View Show', 'upcoming-shows' ),
		'all_items'          => __( 'All Shows', 'upcoming-shows' ),
		'search_items'       => __( 'Search Shows', 'upcoming-shows' ),
		'parent_item_colon'  => __( 'Parent Shows:', 'upcoming-shows' ),
		'not_found'          => __( 'No shows found.', 'upcoming-shows' ),
		'not_found_in_trash' => __( 'No shows found in Trash.', 'upcoming-shows' )

	);
	$args = array(
		'labels'             => $labels,
    'description'        => __( 'Description.', 'upcoming-shows' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'shows' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    'taxonomies'         => array('category', 'post_tag' ),
		'menu_icon'					 => 'dashicons-calendar-alt'
	);
	register_post_type( 'show', $args );
}
add_action( 'init', 'upcomingshows_shows_init' );
/**
 * Show update messages.
 *
 * See /wp-admin/edit-form-advanced.php
 *
 * @param array $messages Existing post update messages.
 *
 * @return array Amended post update messages with new CPT update messages.
 */
function upcomingshows_show_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );
	$messages['Show'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Show updated.', 'upcoming-shows' ),
		2  => __( 'Custom field updated.', 'upcoming-shows' ),
		3  => __( 'Custom field deleted.', 'upcoming-shows' ),
		4  => __( 'Show updated.', 'upcoming-shows' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Show restored to revision from %s', 'upcoming-shows' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Show published.', 'upcoming-shows' ),
		7  => __( 'Show saved.', 'upcoming-shows' ),
		8  => __( 'Show submitted.', 'upcoming-shows' ),
		9  => sprintf(
			__( 'Show scheduled for: <strong>%1$s</strong>.', 'upcoming-shows' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'upcoming-shows' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Show draft updated.', 'upcoming-shows' )
	);
	if ( $post_type_object->publicly_queryable ) {
		$permalink = get_permalink( $post->ID );
		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View Show', 'upcoming-shows' ) );
		$messages[ $post_type ][1] .= $view_link;
		$messages[ $post_type ][6] .= $view_link;
		$messages[ $post_type ][9] .= $view_link;
		$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview Show', 'upcoming-shows' ) );
		$messages[ $post_type ][8]  .= $preview_link;
		$messages[ $post_type ][10] .= $preview_link;
	}
	return $messages;
}
add_filter( 'post_updated_messages', 'upcomingshows_show_updated_messages' );
/**
 * Flush rewrite rules to make custom ULRs active
 */
function upcomingshows_rewrite_flush() {
    upcomingshows_shows_init(); //
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'upcomingshows_rewrite_flush' );

/**
 * Include Shows post type in the main and search queries
 */
function upcomingshows_query_filter( $query ) {
    if ( !is_admin() && $query->is_main_query() ) {
		if ( $query->is_search() || $query->is_home() ) {
			$query->set('post_type', array( 'post', 'show' ) );
	    }
    }
}
add_action( 'pre_get_posts', 'upcomingshows_query_filter' );
