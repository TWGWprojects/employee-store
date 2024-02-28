<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_DASHBOARD' ) ) {
    class SN_WIE_DASHBOARD {

        /**
         * Constructor
         * @description Function to initialize WP actions for the class
         */
        function __construct()
        {

        }

        /**
         * Dashboard page
         * @description Function to show the dashboard of the plugin
         */
        public static function dashboard_page() {

            // Products
            $query_args = ['post_status' => 'any', 'posts_per_page' => -1, 'post_type' => ['product']];
            $product_results = new WP_Query($query_args);

            $args['products'] = ['total_count' => $product_results->found_posts,
                                 'mapping_profile_count' => count(SN_WIE_PRODUCT::get_mapping_profile_list()),
                                 'schedule_profile_count' => count(SN_WIE_PRODUCT::get_schedule_profile_list()),
                                 'log_count' => count(SN_WIE_PRODUCT::get_log_list())];
            //---------

            // Product Categories
            $query_args = ['hide_empty' => false];
            $product_category_results = get_terms( 'product_cat', $query_args );
            $args['product_categories'] = ['total_count' => count($product_category_results),
                                           'mapping_profile_count' => count(SN_WIE_PRODUCT_CATEGORY::get_mapping_profile_list()),
                                           'schedule_profile_count' => count(SN_WIE_PRODUCT_CATEGORY::get_schedule_profile_list()),
                                           'log_count' => count(SN_WIE_PRODUCT_CATEGORY::get_log_list())];
            //-------------------

            // Product Reviews
            $query_args = ['status' => 'all', 'parent' => 0];
            $product_review_results = new WP_Comment_Query($query_args);
            $args['product_reviews'] = ['total_count' => count($product_review_results->comments),
                                        'mapping_profile_count' => count(SN_WIE_PRODUCT_REVIEW::get_mapping_profile_list()),
                                        'schedule_profile_count' => count(SN_WIE_PRODUCT_REVIEW::get_schedule_profile_list()),
                                        'log_count' => count(SN_WIE_PRODUCT_REVIEW::get_log_list())];
            //----------------

            // Orders
            $query_args = ['post_type' => ['shop_order'], 'post_status' => array_keys(wc_get_order_statuses()), 'posts_per_page' => -1];
            $order_results = new WP_Query($query_args);

            $args['orders'] = ['total_count' => $order_results->found_posts,
                               'mapping_profile_count' => count(SN_WIE_ORDER::get_mapping_profile_list()),
                               'schedule_profile_count' => count(SN_WIE_ORDER::get_schedule_profile_list()),
                               'log_count' => count(SN_WIE_ORDER::get_log_list())];
            //-------

            // Users
            $query_args = ['posts_per_page' => -1, 'orderby'=> 'ID', 'order' => 'ASC'];
            $user_results = new WP_User_Query($query_args);
            $args['users'] = ['total_count' => $user_results->get_total(),
                              'mapping_profile_count' => count(SN_WIE_USER::get_mapping_profile_list()),
                              'schedule_profile_count' => count(SN_WIE_USER::get_schedule_profile_list()),
                              'log_count' => count(SN_WIE_USER::get_log_list())];
            //------

            // Coupons
            $query_args = ['post_type' => 'shop_coupon', 'post_status' => 'any', 'posts_per_page' => -1];
            $coupon_results = new WP_Query($query_args);
            $args['coupons'] = ['total_count' => $coupon_results->found_posts,
                                'mapping_profile_count' => count(SN_WIE_COUPON::get_mapping_profile_list()),
                                'schedule_profile_count' => count(SN_WIE_COUPON::get_schedule_profile_list()),
                                'log_count' => count(SN_WIE_COUPON::get_log_list())];
            //--------

            wc_get_template( 'dashboard.php', $args, '', SN_WIE_TEMPLATE_PATH .'/' );
        }
    }
}

$sn_wie_dashboard = new SN_WIE_DASHBOARD();
