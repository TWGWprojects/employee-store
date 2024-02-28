<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_INIT' ) ) {

    class SN_WIE_INIT {

        /**
         * Constructor
         * @description Function to register and initialize WP actions for the plugin
         */
        function __construct() {

            register_activation_hook( SN_WIE_FILE, array( 'SN_WIE_INIT', 'install_plugin_data' ) );
            register_uninstall_hook( SN_WIE_FILE, array( 'SN_WIE_INIT', 'uninstall_plugin_data' ) );

            if ( class_exists( 'Woocommerce' ) ) {
                $this->init_plugin();
            } else {
                add_action( 'woocommerce_loaded', array( &$this, 'init_plugin' ) );
            }
        }

        /**
         * Initializer plugin
         * @description Function initialize action for the plugin
         */
        public function init_plugin() {

            add_filter( 'plugin_action_links_' . SN_WIE_FILE_NAME, array($this, 'plugin_action_links'));
            add_action( 'admin_enqueue_scripts', array( &$this, 'set_admin_css' ), 10 );
            add_action( 'admin_enqueue_scripts', array( &$this, 'set_admin_js' ), 10 );
            add_action( 'admin_head', array( &$this, 'add_head_js'), 10 );
            add_action( 'admin_menu', array( &$this, 'set_menu' ) );
        }

        /**
         * Add action links on plugin page
         * @description Function to add plugin action links
         *
         * @param $links
         * @return array
         */
        public function plugin_action_links( $links ) {
            $plugin_links = array(
                '<a target="_blank" href="'.SN_WIE_DOCUMENTATION_URL.'">' . __('Documentation', SN_WIE_SLUG) . '</a>',
                '<a target="_blank" href="https://wordpress.org/support/plugin/import-export-for-woocommerce/reviews?rate=5#new-post">' . __('Review', SN_WIE_SLUG) . '</a>',
                '<a target="_blank" href="'.SN_WIE_PLUGIN_URL.'" style="color:#4F8ECB;"> ' . __('Premium Upgrade', SN_WIE_SLUG) . '</a>',
            );
            return array_merge($plugin_links, $links);
        }

        /**
         * Set admin CSS
         * @description Function to include the admin CSS
         */
        public function set_admin_css() {
            wp_register_style( 'sn-wie-admin-css', SN_WIE_ASSET_URL . '/css/style.css', array(), SN_WIE_PLUGIN_VERSION );
            wp_enqueue_style( 'sn-wie-admin-css' );
        }

        /**
         * Set admin JS
         * @description Function to include the admin JS
         */
        public function set_admin_js() {
            wp_register_script( 'jquery-form-js', SN_WIE_ASSET_URL . '/js/jquery.form.js', array('jquery'), SN_WIE_PLUGIN_VERSION );
            wp_enqueue_script( 'jquery-form-js' );
        }

        /**
         * Add Head JS
         * @description Function to add global JS variables in adminhead
         */
        public function add_head_js() {
            ?>
            <script>
                var sn_wie_admin_url = "<?php echo( admin_url() ); ?>";
            </script>
            <?php
        }

        /**
         * Set menu
         * @description Function to set the menu for the plugin
         */
        public function set_menu() {
            global $current_user;

            if ( current_user_can( 'administrator' ) || is_super_admin() ) {
                $capabilities = $this->user_capabilities();
                foreach ( $capabilities as $capability => $cap_desc ) {
                    $current_user->add_cap( $capability );
                }
                unset ( $capabilities );
            }

            add_menu_page( __('Import Export for WooCommerce', SN_WIE_SLUG), __('WC Import Export', SN_WIE_SLUG), 'sn_wie_manage_dashboard', 'sn-wie-dashboard', array('SN_WIE_DASHBOARD', 'dashboard_page') , SN_WIE_ASSET_URL.'/images/icon.png' );
            add_submenu_page( 'sn-wie-dashboard', __('Dashboard', SN_WIE_SLUG), __('Dashboard', SN_WIE_SLUG), 'sn_wie_manage_dashboard', 'sn-wie-dashboard', array('SN_WIE_DASHBOARD', 'dashboard_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Products', SN_WIE_SLUG), __('Products', SN_WIE_SLUG), 'sn_wie_manage_products', 'sn-wie-products', array('SN_WIE_PRODUCT', 'product_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Product Categories', SN_WIE_SLUG), __('Product Categories', SN_WIE_SLUG), 'sn_wie_manage_product_categories', 'sn-wie-product-categories', array('SN_WIE_PRODUCT_CATEGORY', 'product_category_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Product Reviews', SN_WIE_SLUG), __('Product Reviews', SN_WIE_SLUG), 'sn_wie_manage_product_reviews', 'sn-wie-product-reviews', array('SN_WIE_PRODUCT_REVIEW', 'product_review_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Orders', SN_WIE_SLUG), __('Orders', SN_WIE_SLUG), 'sn_wie_manage_orders', 'sn-wie-orders', array('SN_WIE_ORDER', 'order_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Users', SN_WIE_SLUG), __('Users', SN_WIE_SLUG), 'sn_wie_manage_users', 'sn-wie-users', array('SN_WIE_USER', 'user_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Coupons', SN_WIE_SLUG), __('Coupons', SN_WIE_SLUG), 'sn_wie_manage_coupons', 'sn-wie-coupons', array('SN_WIE_COUPON', 'coupon_page' ) );
            add_submenu_page( 'sn-wie-dashboard', __('Settings', SN_WIE_SLUG), __('Settings', SN_WIE_SLUG), 'sn_wie_manage_settings', 'sn-wie-settings', array('SN_WIE_SETTING', 'setting_page' ) );
        }

        /**
         * Install plugin data
         * @description Function to install the data at installation
         */
        public function install_plugin_data() {
            update_option( 'sn_wie_import_record_pre_request', SN_WIE_IMPORT_RECORD_PER_REQUEST );
            update_option( 'sn_wie_export_record_pre_request', SN_WIE_EXPORT_RECORD_PER_REQUEST );
            update_option( 'sn_wie_field_separator', SN_WIE_FIELD_SEPARATOR );
        }

        /**
         * Uninstall plugin data
         * @description Function to uninstall the data at un-installation
         */
        public function uninstall_plugin_data() {
            delete_option( 'sn_wie_import_record_pre_request' );
            delete_option( 'sn_wie_export_record_pre_request' );
            delete_option( 'sn_wie_field_separator');

            delete_option( 'sn_wie_coupon_import_record_pre_request' );
            delete_option( 'sn_wie_coupon_export_record_pre_request' );

            delete_option( 'sn_wie_order_import_record_pre_request' );
            delete_option( 'sn_wie_order_export_record_pre_request' );

            delete_option( 'sn_wie_product_import_record_pre_request' );
            delete_option( 'sn_wie_product_export_record_pre_request' );

            delete_option( 'sn_wie_product_category_import_record_pre_request' );
            delete_option( 'sn_wie_product_category_export_record_pre_request' );

            delete_option( 'sn_wie_product_review_import_record_pre_request' );
            delete_option( 'sn_wie_product_review_export_record_pre_request' );

            delete_option( 'sn_wie_user_import_record_pre_request' );
            delete_option( 'sn_wie_user_export_record_pre_request' );
        }

        /**
         * Set message
         * @description Function to set the message in session
         * @param $message
         * @param $type
         */
        public static function set_message( $message, $type ) {
            $_SESSION['sn_wie_message'] = ['type' => $type, 'message' => $message];
        }

        /**
         * Show message
         * @description Function to show the message on the top of page
         */
        public static function show_message() {
            if( @$_SESSION['sn_wie_message'] ) {
                echo('<div id="message" class="sn-wie-message updated notice notice-success is-dismissible">');
                echo('<p>'. $_SESSION['sn_wie_message']['message'] .'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice', SN_WIE_SLUG).'</span></button>');
                echo('</div>');
            }
            unset($_SESSION['sn_wie_message']);
        }

        /**
         * User capabilities
         * @description Function to return plugin user capabilities
         * @return array
         */
        private function user_capabilities() {

            return array (
                'sn_wie_manage_dashboard'          => __( 'User can manage WC Import Export Dashboard', SN_WIE_SLUG ),
                'sn_wie_manage_products'           => __( 'User can manage Products', SN_WIE_SLUG ),
                'sn_wie_manage_product_categories' => __( 'User can manage Product Categories', SN_WIE_SLUG ),
                'sn_wie_manage_product_reviews'    => __( 'User can manage Product Reviews', SN_WIE_SLUG ),
                'sn_wie_manage_orders'             => __( 'User can manage Orders', SN_WIE_SLUG ),
                'sn_wie_manage_users'              => __( 'User can manage Users', SN_WIE_SLUG ),
                'sn_wie_manage_coupons'            => __( 'User can manage Coupons', SN_WIE_SLUG ),
                'sn_wie_manage_settings'           => __( 'User can manage Settings', SN_WIE_SLUG )
            );
        }
    }
}

$sn_wie_init = new SN_WIE_INIT();
