<?php
if(!function_exists('bwp_create_type_tab')  ){
	function bwp_create_type_tab(){
	register_post_type( 'bwp_tab',
		array(
			'labels' => array(
				'name' => __( 'Tabs','wpbingo' ),
				'singular_name' => __( 'Tab','wpbingo' ),
				'search_items'      => __( 'Search Tab', 'wpbingo' ),
				'all_items'         => __( 'All Tab', 'wpbingo' ),
				'parent_item'       => __( 'Parent Tab', 'wpbingo'),
				'parent_item_colon' => __( 'Parent Tab:', 'wpbingo' ),
				'edit_item'         => __( 'Edit Tab', 'wpbingo'),
				'update_item'       => __( 'Update Tab', 'wpbingo'),
				'add_new_item'      => __( 'Add New Tab', 'wpbingo'),
				'new_item_name'     => __( 'New Tab Name', 'wpbingo'),				
			),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'tabs'),
			'menu_position' => 8,
			'show_in_menu' => false,
		)
	);
	if($bwp_js_content_types = get_option('bwp_js_content_types')){
		if(!in_array('bwp_tab',$bwp_js_content_types)){
		  $bwp_js_content_types[] = 'bwp_tab';
		}
		$options[] = 'bwp_tab';
			update_option( 'bwp_js_content_types',$bwp_js_content_types );
		}else{
			$options = array('page','bwp_tab');
		}
	}
	add_action( 'init','bwp_create_type_tab',2 );
}