<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_SETTING' ) ) {
    class SN_WIE_SETTING {

        /**
         * Constructor
         * @description Function to initialize WP actions for the class
         */
        function __construct()
        {
            add_action( 'admin_enqueue_scripts', array( &$this, 'set_page_js' ), 10 );

            add_action( 'admin_post_sn_wie_update_setting', array( &$this, 'update_setting' ) );
        }

        /**
         * Set page JS
         * @description Function to include the JS for product page
         */
        public function set_page_js() {
            wp_register_script('sn-wie-setting-js', SN_WIE_ASSET_URL . '/js/setting.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
            wp_enqueue_script('sn-wie-setting-js');
        }

        /**
         * Setting page
         * @description Function to show the product page based upon the argument
         */
        public static function setting_page() {

            // Products
            $query_args = ['post_status' => 'any', 'posts_per_page' => -1, 'post_type' => ['product']];
            $product_results = new WP_Query($query_args);

            $args['products'] = ['total_count' => $product_results->found_posts];
            //---------

            // Product Categories
            $product_category_results = get_terms( 'product_cat' );
            $args['product_categories'] = ['total_count' => count($product_category_results)];
            //-------------------

            // Product Reviews
            $query_args = ['status' => 'all', 'parent' => 0];
            $product_review_results = new WP_Comment_Query($query_args);
            $args['product_reviews'] = ['total_count' => count($product_review_results->comments)];
            //-------------------

            // Orders
            $query_args = ['post_type' => ['shop_order'], 'post_status' => array_keys(wc_get_order_statuses()), 'posts_per_page' => -1];
            $order_results = new WP_Query($query_args);
            $args['orders'] = ['total_count' => $order_results->found_posts];
            //-------

            // Users
            $query_args = ['posts_per_page' => -1, 'orderby'=> 'ID', 'order' => 'ASC'];
            $user_results = new WP_User_Query($query_args);
            $args['users'] = ['total_count' => $user_results->get_total()];
            //------

            // Coupons
            $query_args = ['post_type' => 'shop_coupon', 'post_status' => 'any', 'posts_per_page' => -1];
            $coupon_results = new WP_Query($query_args);
            $args['coupons'] = ['total_count' => $coupon_results->found_posts];
            //--------

            $args['options']['import_record_per_request'] = self::get_option( 'import_record_per_request' );
            $args['options']['export_record_per_request'] = self::get_option( 'export_record_per_request' );
            $args['options']['field_separator'] = self::get_option( 'field_separator' );
            wc_get_template( 'setting.php', $args, '', SN_WIE_TEMPLATE_PATH .'/' );
        }

        /**
         * Update setting
         * @description Function to update settings
         */
        public function update_setting() {

            $fn_status = true;

            // Update option variables
            if($fn_status == true) {

                $import_record_per_request = intval( $_POST['import_record_per_request'] );
                if( $import_record_per_request != 0 ) {
                    update_option( 'sn_wie_import_record_per_request', $import_record_per_request );
                }
                
                $export_record_per_request = intval( $_POST['export_record_per_request'] );
                if( $export_record_per_request != 0 ) {
                    update_option( 'sn_wie_export_record_per_request', $export_record_per_request );
                }
                
                update_option( 'sn_wie_field_separator', sanitize_text_field( trim( $_POST['field_separator'] ) ) );
            }
            //------------------------

            // Set message
            SN_WIE_INIT::set_message('Settings updated', 'success');
            //------------

            wp_redirect( 'admin.php?page=sn-wie-settings' );
        }

        /**
         * Get option
         * @description Function to return option value
         * @param $key
         * @return mixed
         */
        private static function get_option( $key ) {
            $value = null;

            /*if( !$value ) {
                $value = get_option( 'sn_wie_'.$key );
            }*/

            if( !$value ) {
                $value = constant(  'SN_WIE_'.strtoupper( $key ) );
            }

            return ( $value );
        }
    }
}

$sn_wie_setting = new SN_WIE_SETTING();
