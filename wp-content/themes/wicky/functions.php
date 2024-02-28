<?php
define('wicky_version','1.0'); 
if (!isset($content_width)) { $content_width = 940; }
require_once( get_template_directory().'/inc/class-tgm-plugin-activation.php' );
require_once( get_template_directory().'/inc/plugin-requirement.php' );
require_once( get_template_directory().'/inc/megamenu/megamenu.php' );
include_once( get_template_directory().'/inc/megamenu/mega_menu_custom_walker.php' );
require_once( get_template_directory().'/inc/function.php' );
require_once( get_template_directory().'/inc/loader.php' );
include_once( get_template_directory().'/inc/menus.php' );
include_once( get_template_directory().'/inc/template-tags.php' );
require_once( get_template_directory().'/inc/woocommerce.php' );
require_once( get_template_directory().'/inc/admin/functions.php' );
require_once( get_template_directory().'/inc/admin/theme-options.php' );
function wicky_custom_css() {
	$wicky_settings = wicky_global_settings();
	if (!is_admin()) {
		wp_enqueue_style( 'wicky-style-template', get_template_directory_uri().'/css/template.css'); 
		ob_start(); 
		include( get_template_directory().'/inc/custom-css.php' ); 
		$content = ob_get_clean();
		$content = str_replace(array("\r\n", "\r"), "\n", $content);
		$csss = explode("\n", $content);
		$custom_css = array();
		foreach ($csss as $i => $css) { if(!empty($css)) $custom_css[] = trim($css); }
		wp_add_inline_style( 'wicky-style-template', implode($custom_css) );  
	}
}
add_action('wp_enqueue_scripts', 'wicky_custom_css' );
function wicky_custom_js() {
	if (!is_admin()) {
		wp_enqueue_script( 'wicky-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery'), null, true );
		wp_localize_script( 'wicky-script', 'wicky_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
}
add_action('wp_enqueue_scripts', 'wicky_custom_js' );