<?php
/**
 * Wpbingo Tab Group
 * Plugin URI: http://www.wpbingo.com
 * Version: 1.0
 */
add_action( 'init', 'creat_tab_group_taxonomy', 0 );
function creat_tab_group_taxonomy() {
	$labels = array(
		'name'              => __( 'Tabs Group', 'wpbingo' ),
		'singular_name'     => __( 'Tabs Group', 'wpbingo' ),
		'search_items'      => __( 'Search Tabs Group', 'wpbingo' ),
		'all_items'         => __( 'All Tab Group', 'wpbingo' ),
		'parent_item'       => __( 'Parent Tab Group', 'wpbingo'),
		'parent_item_colon' => __( 'Parent Tab Group:', 'wpbingo' ),
		'edit_item'         => __( 'Edit Tab Group', 'wpbingo'),
		'update_item'       => __( 'Update Tab Group', 'wpbingo'),
		'add_new_item'      => __( 'Add New Tab Group', 'wpbingo'),
		'new_item_name'     => __( 'New Tab Group Name', 'wpbingo'),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy('tabs_group', 'bwp_tab', $args);
}
?>