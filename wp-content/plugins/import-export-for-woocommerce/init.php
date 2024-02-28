<?php
/*
Plugin Name: Import Export For WooCommerce (Basic)
Plugin URI: https://www.codeteam.in/product/woocommerce-import-export/
Description: Import Export functionality for WooCommerce
Version: 1.5
Tested up to 6.4.1
WC tested up to: 8.2.2
Author: Siddharth Nagar
Author URI: http://www.codeteam.in/
License: GPLv2
*/
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! defined( 'SN_WIE_AUTHOR_URL' ) ) {
    define( 'SN_WIE_AUTHOR_URL', 'https://www.codeteam.in/' );
}

if ( ! defined( 'SN_WIE_PLUGIN_URL' ) ) {
    define( 'SN_WIE_PLUGIN_URL', SN_WIE_AUTHOR_URL.'product/woocommerce-import-export/' );
}

if ( ! defined( 'SN_WIE_DOCUMENTATION_URL' ) ) {
    define( 'SN_WIE_DOCUMENTATION_URL', SN_WIE_AUTHOR_URL.'documentation/woocommerce-import-export/introduction/' );
}

if ( ! defined( 'SN_WIE_PLUGIN_VERSION' ) ) {
    define( 'SN_WIE_PLUGIN_VERSION', '1.5' );
}

if ( ! defined( 'SN_WIE_SLUG' ) ) {
    define( 'SN_WIE_SLUG', 'import-export-for-woocommerce' );
}

if ( ! defined( 'SN_WIE_DIR' ) ) {
    define( 'SN_WIE_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SN_WIE_URL' ) ) {
    define( 'SN_WIE_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SN_WIE_FILE' ) ) {
    define( 'SN_WIE_FILE', __FILE__ );
}

if ( ! defined( 'SN_WIE_FILE_NAME' ) ) {
    define( 'SN_WIE_FILE_NAME', plugin_basename(__FILE__) );
}

if ( ! defined( 'SN_WIE_TEMPLATE_PATH' ) ) {
    define( 'SN_WIE_TEMPLATE_PATH', SN_WIE_DIR . 'templates' );
}

if ( ! defined( 'SN_WIE_ASSET_URL' ) ) {
    define( 'SN_WIE_ASSET_URL', SN_WIE_URL . 'assets' );
}

if ( ! defined( 'SN_WIE_IMPORT_RECORD_PER_REQUEST' ) ) {
    define( 'SN_WIE_IMPORT_RECORD_PER_REQUEST', 5 );
}

if ( ! defined( 'SN_WIE_EXPORT_RECORD_PER_REQUEST' ) ) {
    define( 'SN_WIE_EXPORT_RECORD_PER_REQUEST', 50 );
}

if ( ! defined( 'SN_WIE_FIELD_SEPARATOR' ) ) {
    define( 'SN_WIE_FIELD_SEPARATOR', ',' );
}

if ( ! defined( 'SN_WIE_UPLOAD_PATH' ) ) {
    define( 'SN_WIE_UPLOAD_PATH', wp_get_upload_dir()['basedir'] . '/' . SN_WIE_SLUG );
}

if ( ! defined( 'SN_WIE_UPLOAD_URL' ) ) {
    define( 'SN_WIE_UPLOAD_URL', wp_get_upload_dir()['baseurl'] . '/' . SN_WIE_SLUG );
}

/**
 * Show woocommerce admin notice
 * @description Function to show woocommerce admin notice
 */
function sn_wie_install_woocommerce_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'Import Export For WooCommerce is enabled but not effective. It requires WooCommerce in order to work.', SN_WIE_SLUG ); ?></p>
    </div>
    <?php
}

/**
 * Initialize plugin
 * @description Function to initialize the plugin
 */
function sn_wie_init() {

    load_plugin_textdomain( SN_WIE_SLUG, false, dirname( plugin_basename( __FILE__ ) ). '/languages/' );

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-init.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-init.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-dashboard.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-dashboard.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-product.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-product.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-product-category.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-product-category.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-product-review.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-product-review.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-order.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-order.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-user.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-user.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-coupon.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-coupon.php');
    }

    if(file_exists(SN_WIE_DIR.'/includes/class.sn-wie-setting.php')) {
        require_once(SN_WIE_DIR.'/includes/class.sn-wie-setting.php');
    }

}
add_action( 'sn_wie_init', 'sn_wie_init' );

/**
 * Install plugin
 * @description Function to initiate the plugin installation
 */
function sn_wie_install() {

    if ( ! function_exists( 'WC' ) ) {
        add_action( 'admin_notices', 'sn_wie_install_woocommerce_admin_notice' );
    }
    else {
        do_action( 'sn_wie_init' );
    }
}
add_action( 'plugins_loaded', 'sn_wie_install', 10 );