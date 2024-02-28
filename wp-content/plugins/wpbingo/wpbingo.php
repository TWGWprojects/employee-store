<?php
/*
Plugin Name: Wpbingo Core (Do not update)
Plugin URI: https://themeforest.net/user/wpbingo
Description: Use For Wpbingo Theme.
Version: 1.0
Author: TungHV
Author URI: https://themeforest.net/user/wpbingo
*/

// don't load directly
if (!defined('ABSPATH'))
    die('-1');

require_once( dirname(__FILE__) . '/function.php');
require_once( dirname(__FILE__) . '/elementor.php');
define('WPBINGO_ELEMENTOR_PATH', dirname(__FILE__) . '/elementor/');
define('WPBINGO_ELEMENTOR_TEMPLATE_PATH', dirname(__FILE__) . '/elementor-template/');
define('WPBINGO_WIDGET_PATH', dirname(__FILE__) . '/widgets/');
define('WPBINGO_WIDGET_TEMPLATE_PATH', dirname(__FILE__) . '/widgets-template/');
define('WPBINGO_CONTENT_TYPES_LIB', dirname(__FILE__) . '/lib/');
require_once WPBINGO_CONTENT_TYPES_LIB . 'lookbook/includes/bwp_lookbook_class.php';
define ( 'LOOKBOOK_TABLE', 'bwp_lookbook');
class WpbingoShortcodesClass {
    function __construct() {
        // Init plugins
		$this->loadInit();
		add_filter( 'wp_calculate_image_srcset', array( $this, 'bwp_disable_srcset' ) );
		add_filter('upload_mimes', array( $this, 'wpbingo_mime_types' ) );
		remove_filter('pre_term_description', 'wp_filter_kses');
		load_plugin_textdomain('wpbingo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
	function loadInit() {
		global $woocommerce;
		if ( ! isset( $woocommerce ) || ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', array( $this, 'bwp_woocommerce_admin_notice' ) );
			return;
		}else{		
			add_action('wp_enqueue_scripts', array( $this, 'bwp_framework_script' ) );	
			require_once(WPBINGO_CONTENT_TYPES_LIB.'settings/save_settings.php');
			$this->bwp_load_file(WPBINGO_WIDGET_PATH);
			$this->bwp_load_file(WPBINGO_CONTENT_TYPES_LIB);
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );
			add_action( 'init',array( $this, 'wpbingo_remove_default_action'));
		}
    }
	function wpbingo_mime_types($mimes) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}
	function register_widgets(){
		register_widget( 'bwp_recent_post_widget');
		register_widget( 'bwp_ajax_filter_widget' );
	}	
	function wpbingo_remove_default_action(){
		if ( class_exists( 'YITH_Woocompare_Frontend' ) && get_option('yith_woocompare_compare_button_in_product_page') == 'yes' ) {
			global $yith_woocompare;
			if( ! is_admin() ) {
				remove_action('woocommerce_after_shop_loop_item', array($yith_woocompare->obj, 'add_compare_link'), 20);
				remove_action('woocommerce_single_product_summary', array($yith_woocompare->obj, 'add_compare_link'), 35);
			}
		}
	}	
	function bwp_load_file($path){
		$files = array_diff(scandir($path), array('..', '.'));
		if(count($files)>0){
			foreach ($files as  $file) {
				if (strpos($file, '.php') !== false)
					require_once($path . $file);
			}
		}		
	}
	function bwp_framework_script(){
		wp_enqueue_script( 'jquery-ui-slider', false, array('jquery'));
		wp_enqueue_script('bwp_wpbingo_js',plugins_url( '/wpbingo/assets/js/wpbingo.js' ),array("jquery"),false,true);
		wp_register_script( 'jquery-cookie', plugins_url( '/wpbingo/assets/js/jquery.cookie.min.js' ), array( 'jquery' ), null, true );
		wp_enqueue_script( 'jquery-cookie' );
		wp_register_script( 'wpbingo-newsletter', plugins_url( '/wpbingo/assets/js/newsletter.js' ), array('jquery','jquery-cookie'), null, true );
		wp_enqueue_script( 'wpbingo-newsletter' );
		wp_register_style( 'bwp_woocommerce_filter_products', plugins_url('/wpbingo/assets/css/bwp_ajax_filter.css') );
		if (!wp_style_is('bwp_woocommerce_filter_products')) {
			wp_enqueue_style('bwp_woocommerce_filter_products');
		}
		wp_register_script('bwp_woocommerce_filter', plugins_url( '/wpbingo/assets/js/filter.js' ), array('jquery'), null, true);	
		wp_localize_script( 'bwp_woocommerce_filter', 'filter_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script('bwp_woocommerce_filter');		
	}
	function bwp_woocommerce_admin_notice(){ ?>
		<div class="error">
			<p><?php echo esc_html__( 'Wpbingo is enabled but not effective. It requires WooCommerce in order to work.', 'wpbingo' ); ?></p>
		</div>
		<?php
	}
	function bwp_disable_srcset( $sources ) {		
		return false;	
	}
}

function lookbook_install () {
    global $wpdb;
	
    $table_name = $wpdb->prefix . LOOKBOOK_TABLE;
	include_once ABSPATH.'wp-admin/includes/upgrade.php';
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

        $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(255) NOT NULL,
				  `title` varchar(255),
				  `description` varchar(255),
                  `width` smallint(5) unsigned NOT NULL,
                  `height` smallint(5) unsigned NOT NULL,		  
                  `image` varchar(255) NOT NULL,
                  `pins` text NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		if(dbDelta($sql)){
			$sql_insert = "INSERT INTO `" . $table_name . "` (`id`, `name`, `title`, `description`, `width`, `height`, `image`, `pins`) VALUES
			(5, 'Lookbook 1', '', '', 380, 380, 'img1.jpg', '[{\"id\":\"1582617601592\",\"top\":153,\"left\":188,\"width\":30,\"height\":30,\"slug\":\"grey-thrasher-wine\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(6, 'Lookbook 2', '', '', 380, 380, 'img2.jpg', '[{\"id\":\"1582617737325\",\"top\":156,\"left\":206,\"width\":30,\"height\":30,\"slug\":\"feasants-wine\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(7, 'Lookbook 3', '', '', 380, 380, 'img3.jpg', '[{\"id\":\"1582617803503\",\"top\":193,\"left\":277,\"width\":30,\"height\":30,\"slug\":\"free-spirit-wine\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(8, 'Lookbook 4', '', '', 380, 380, 'img4.jpg', '[{\"id\":\"1582617818853\",\"top\":255,\"left\":232,\"width\":30,\"height\":30,\"slug\":\"wine-n-roses\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(9, 'Lookbook 5', '', '', 380, 380, 'img5.jpg', '[{\"id\":\"1582617833764\",\"top\":190,\"left\":135,\"width\":30,\"height\":30,\"slug\":\"d-wineneck\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(10, 'Lookbook 6', '', '', 380, 380, 'img6.jpg', '[{\"id\":\"1582617923068\",\"top\":166,\"left\":169,\"width\":30,\"height\":30,\"slug\":\"elite-gathering-wine\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(11, 'Lookbook 7', '', '', 380, 380, 'img7.jpg', '[{\"id\":\"1582617933868\",\"top\":155,\"left\":94,\"width\":30,\"height\":30,\"slug\":\"wayfaring-wine\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(12, 'Lookbook 8', '', '', 380, 380, 'img8.jpg', '[{\"id\":\"1582617946227\",\"top\":217,\"left\":199,\"width\":30,\"height\":30,\"slug\":\"wine-guzzlers\",\"img_height\":380,\"img_width\":380,\"editable\":true}]'),
			(13, 'Lookbook 9', '', '', 380, 380, 'img9.jpg', '[{\"id\":\"1583118783065\",\"top\":141,\"left\":178,\"width\":30,\"height\":30,\"slug\":\"glory-peach-wine\",\"img_height\":380,\"img_width\":380,\"editable\":true}]');";
			dbDelta($sql_insert);
		}
    }
    $file = new bwp_lookbook_class();
    $file->create_folder_recursive(LOOKBOOK_UPLOAD_PATH);
    $file->create_folder_recursive(LOOKBOOK_UPLOAD_PATH_THUMB);
	add_option('update2prof_notice', 0,0);
}

register_activation_hook(__FILE__, 'lookbook_install');

register_deactivation_hook(__FILE__, 'lookbook_deactivate');

function lookbook_deactivate() {
    if( !function_exists( 'the_field' )) {
        update_option( 'update2prof_notice', 0 );
    }
}

// Finally initialize code
new WpbingoShortcodesClass();

	
	