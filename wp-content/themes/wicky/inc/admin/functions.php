<?php
function wicky_check_theme_options() {
    // check default options
    $wicky_settings = wicky_global_settings();
    ob_start();
    $options = ob_get_clean();
    $wicky_default_settings = json_decode($options, true);
    foreach ($wicky_default_settings as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $key1 => $value1) {
                if ($key1 != 'google' && (!isset($wicky_settings[$key][$key1]) || !$wicky_settings[$key][$key1])) {
                    $wicky_settings[$key][$key1] = $wicky_default_settings[$key][$key1];
                }
            }
        } else {
            if (!isset($wicky_settings[$key])) {
                $wicky_settings[$key] = $wicky_default_settings[$key];
            }
        }
    }
    return $wicky_settings;
}
function wicky_options_sidebars() {
    return array(
        'wide-left-sidebar',
        'wide-right-sidebar',
        'left-sidebar',
        'right-sidebar'
    );
}
function wicky_options_body_wrapper() {
    return array(
        'full' 		=> array('alt' => esc_html__('Full', 'wicky'), 'img' => get_template_directory_uri().'/inc/admin/theme_options/layouts/body_full.jpg'),
        'boxed' 	=> array('alt' => esc_html__('Boxed', 'wicky'), 'img' => get_template_directory_uri().'/inc/admin/theme_options/layouts/body_boxed.jpg'),
    );
}
function wicky_options_layouts() {
    return array(
        "full" => array('alt' => esc_html__("Without Sidebar", 'wicky'), 'img' => get_template_directory_uri().'/inc/admin/theme_options/layouts/page_full.jpg'),
        "left" => array('alt' => esc_html__("Left Sidebar", 'wicky'), 'img' => get_template_directory_uri().'/inc/admin/theme_options/layouts/page_full_left.jpg'),
        "right" => array('alt' => esc_html__("Right Sidebar", 'wicky'), 'img' => get_template_directory_uri().'/inc/admin/theme_options/layouts/page_full_right.jpg')
    );
}
if(!function_exists('wicky_options_header_types')) :
	function wicky_options_header_types() {
		return array(
			"1" => array('alt' => esc_html__("Header 1", 'wicky'), 'img' => esc_url(get_template_directory_uri().'/inc/admin/theme_options/headers/header-1.jpg')),
			"2" => array('alt' => esc_html__("Header 2", 'wicky'), 'img' => esc_url(get_template_directory_uri().'/inc/admin/theme_options/headers/header-2.jpg')),
			"3" => array('alt' => esc_html__("Header 3", 'wicky'), 'img' => esc_url(get_template_directory_uri().'/inc/admin/theme_options/headers/header-3.jpg')),
			"4" => array('alt' => esc_html__("Header 4", 'wicky'), 'img' => esc_url(get_template_directory_uri().'/inc/admin/theme_options/headers/header-4.jpg')),
			"5" => array('alt' => esc_html__("Header 5", 'wicky'), 'img' => esc_url(get_template_directory_uri().'/inc/admin/theme_options/headers/header-5.jpg')),
			"6" => array('alt' => esc_html__("Header 6", 'wicky'), 'img' => esc_url(get_template_directory_uri().'/inc/admin/theme_options/headers/header-6.jpg')),
		);
	}
endif;
function wicky_options_banners_effect() {
	$banners_effects = array();
	for ($i = 1; $i <= 12; $i++) {
		$banners_effects['banners-effect-'.$i] =  array('alt' => esc_html__("Banner Effect", 'wicky'), 'img' => get_template_directory_uri().'/inc/admin/theme_options/effects/banner-effect.png');
	}
    return $banners_effects;
}
if(!function_exists('wicky_get_footers')) :
	function wicky_get_footers() {
		$footer = array();
		$footers = get_posts( array('posts_per_page'=>-1,
							'post_type'=>'bwp_footer',
							'orderby'          => 'name',
							'order'            => 'ASC'
					) );
		foreach ($footers as  $key=>$value) {
			$footer[$value->ID] = array('title' => $value->post_title, 'img' => get_template_directory_uri().'/inc/admin/theme_options/footers/'.$value->post_name.'.jpg');
		}
		return $footer;
	}
endif;
// Function for Content Type, ReducxFramework
function wicky_ct_related_product_columns() {
    return array(
        "2" => "2",
        "3" => "3",
        "4" => "4",
        "5" => "5",
        "6" => "6"
    );
}
function wicky_ct_category_view_mode() {
    return array(
        "grid" => esc_html__("Grid", 'wicky'),
        "list" => esc_html__("List", 'wicky')
    );
}