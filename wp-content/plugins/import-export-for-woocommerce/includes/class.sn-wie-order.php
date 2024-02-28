<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_ORDER' ) ) {
    class SN_WIE_ORDER {

        /**
         * Constructor
         * @description Function to initialize WP actions for the class
         */
        function __construct()
        {
            // Assets
            add_action( 'admin_enqueue_scripts', array( &$this, 'set_page_assets' ), 10 );
            //-------

            // Export
            add_action( 'wp_ajax_sn_wie_create_order_csv_file', array( &$this, 'create_csv_file' ) );
            add_action( 'wp_ajax_sn_wie_export_order_csv_file', array( &$this, 'export_csv_file' ) );
            //-------

            // Log
            add_action( 'admin_post_sn_wie_delete_order_log', array( &$this, 'delete_all_logs' ) );
            //----

            // Setting
            add_action( 'admin_post_sn_wie_update_order_setting', array( &$this, 'update_setting' ) );
            //--------

            // Download Export File
            add_action( 'admin_post_sn_wie_download_order_export_file', array( &$this, 'download_export_file' ) );
            //---------------------
        }

        /**
         * Set page Assets
         * @description Function to include the JS for coupon page
         */
        public function set_page_assets() {
            $page = @sanitize_key( $_GET['page'] );
            if ( $page == 'sn-wie-orders') {
                wp_enqueue_style('sn-wie-select2-css', SN_WIE_ASSET_URL . '/css/select2.min.css', SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-select2-js', SN_WIE_ASSET_URL . '/js/select2.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-validate-js', SN_WIE_ASSET_URL . '/js/jquery.validate.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-order-js', SN_WIE_ASSET_URL . '/js/order.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
            }
        }

        /**
         * Order page
         * @description Function to show the order page based upon the argument
         */
        public static function order_page() {
            $tab = @sanitize_key( $_GET['tab'] );

            switch ( $tab ) {
                case 'export': self::export_page(); break;
                case 'import': self::import_page(); break;
                case 'mapping': self::mapping_page(); break;
                case 'add-mapping': self::add_mapping_page(); break;
                case 'schedule': self::schedule_page(); break;
                case 'add-schedule': self::add_schedule_page(); break;
                case 'log': self::log_page(); break;
                case 'setting': self::setting_page(); break;
                default: self::export_page(); break;
            }
        }

        /**
         * Export page
         * @description Function to show the export page
         */
        private static function export_page() {
            $mapping_profile_list = self::get_mapping_profile_list();
            $order_status_list = wc_get_order_statuses();
            $args['page_name'] = 'export';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['mapping_profile_list'] = $mapping_profile_list;
            $args['order_status_list'] = $order_status_list;
            $args['field_separator'] = self::get_option( 'field_separator', true );
            wc_get_template( 'export.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Import page
         * @description Function to show the import page
         */
        private static function import_page() {

            $args['page_name'] = 'import';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            wc_get_template( 'import.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Mapping page
         * @description Function to show the mapping page
         */
        private static function mapping_page() {
            $mapping_profile_list = self::get_mapping_profile_list();
            $args['page_name'] = 'mapping';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['mapping_profile_list'] = $mapping_profile_list;
            wc_get_template( 'mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Add mapping page
         * @description Function to show the add mapping profile page
         */
        private static function add_mapping_page() {

            $args['page_name'] = 'mapping';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            wc_get_template( 'add-mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Scheduling page
         * @description Function to show the scheduling profile page
         */
        private static function schedule_page() {

            $schedule_profile_list = self::get_schedule_profile_list();
            $args['page_name'] = 'schedule';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['schedule_profile_list'] = $schedule_profile_list;
            wc_get_template( 'schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Add scheduling page
         * @description Function to show the add scheduling profile page
         */
        private static function add_schedule_page() {

            $args['page_name'] = 'schedule';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            wc_get_template( 'add-schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Log page
         * @description Function to show the log page
         */
        private static function log_page() {

            $log_list = self::get_log_list();
            $args['page_name'] = 'log';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['file_list'] = $log_list;
            wc_get_template( 'log.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Setting page
         * @description Function to show the setting page
         */
        private static function setting_page() {

            $args['page_name'] = 'setting';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['options']['order_import_record_per_request'] = get_option( 'sn_wie_order_import_record_per_request' );
            $args['options']['order_export_record_per_request'] = get_option( 'sn_wie_order_export_record_per_request' );
            $args['options']['import_record_per_request'] = self::get_option( 'import_record_per_request', true );
            $args['options']['export_record_per_request'] = self::get_option( 'export_record_per_request', true );
            wc_get_template( 'setting.php', $args, '', SN_WIE_TEMPLATE_PATH .'/order/' );
        }

        /**
         * Create CSV file
         * @description Function to create the CSV file for export process
         * @return string/json
         */
        public function create_csv_file() {
            $fn_status = true;
            $response = [];
            $export_record_per_request = null;
            $export_file_name = null;
            $export_columns = null;
            $export_order_statuses = null;
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;

            // Get request variables
            if($fn_status == true) {
                $export_columns = $_POST['export_columns'];
                $export_order_statuses = $_POST['export_order_statuses'];
                $export_meta = $_POST['export_meta'];
                $field_separator = $_POST['field_separator'];
                $download_filename = $_POST['download_filename'];
            }
            //----------------------

            // Get config variables
            $export_record_per_request = self::get_option( 'export_record_per_request' );
            $field_separator = $field_separator?$field_separator:self::get_option( 'field_separator' );
            //---------------------

            // Create if upload directory not exists
            if($fn_status == true) {
                if (!file_exists(SN_WIE_UPLOAD_PATH.'/order')) {
                    if(!mkdir(SN_WIE_UPLOAD_PATH.'/order', 0777, true)) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //---------------------------------------


            // Create empty CSV file
            if($fn_status == true) {
                $export_file_name = 'export_manual_'.current_time('timestamp').'.csv';
                if(file_put_contents(SN_WIE_UPLOAD_PATH.'/order/'.$export_file_name, null) === false) {
                    $fn_status = false;
                    $response = ['status' => false, 'code' => 300, 'message' => __('Unable to create file', SN_WIE_SLUG), 'data' => null];
                }
            }
            //----------------------

            // Count total records
            if($fn_status == true) {

                if(!$export_order_statuses) {
                    $export_order_statuses = array_keys(wc_get_order_statuses());
                }

                $query_args = ['post_type' => ['shop_order'], 'post_status' => $export_order_statuses, 'posts_per_page' => -1, 'orderby'=> 'ID', 'order' => 'ASC'];
                $query_args['tax_query']['relation'] = 'AND';

                $product_results = new WP_Query($query_args);
                $total_requests = ceil($product_results->found_posts/$export_record_per_request);
                $response = ['status' => true, 'code' => 104, 'message' => 'Success', 'data' => ['export_file_name' => $export_file_name, 'total_requests' => $total_requests, 'export_columns' => $export_columns, 'export_order_statuses' => $export_order_statuses, 'export_meta' => $export_meta, 'field_separator' => $field_separator, 'download_filename' => $download_filename]];
            }
            //--------------------

            wp_send_json( $response );
        }

        /**
         * Export CSV file
         * @description Function to export the data to CSV file for export process
         * @return string/json
         */
        public function export_csv_file() {
            set_time_limit(0);
            $fn_status = true;
            $response = [];
            $export_record_per_request = null;
            $file_name = null;
            $file_path = null;
            $total_requests = null;
            $request_number = null;
            $export_columns = null;
            $export_order_statuses = null;
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;
            $columns = null;
            $csv_columns = null;
            $order_data_list = [];
            $mapping_profile = null;

            // Get config variables
            $export_record_per_request = self::get_option( 'export_record_per_request' );
            //---------------------

            // Get request variables
            if($fn_status == true) {
                $export_columns = $_POST['export_columns'];
                $export_meta = $_POST['export_meta'];
                $field_separator = $_POST['field_separator'];
                $download_filename = $_POST['download_filename'];
                $export_order_statuses = $_POST['export_order_statuses'];
                $total_requests = intval($_POST['total_requests']);
                $request_number = intval($_POST['request_number']);
                $file_name = $_POST['file_name'];
                $csv_columns = $_POST['csv_columns'];
            }
            //----------------------

            // Fetch mapping profile if required
            if($fn_status == true) {
                if(strpos($export_columns, 'use_mapping') !== false) {
                    $mapping_list = self::get_mapping_profile_list();
                    $mapping_profile = $mapping_list[str_replace('use_mapping_', '', $export_columns)];
                    $export_columns = 'use_mapping';
                }
            }
            //----------------------------------

            // Get all default columns
            if ( $fn_status == true ) {
                $columns = self::get_column_names();
            }
            //------------------------

            // Set Query Parameters
            if(!$export_order_statuses) {
                $export_order_statuses = array_keys(wc_get_order_statuses());
            }

            $query_args = ['post_type' => ['shop_order'], 'post_status' => $export_order_statuses, 'posts_per_page' => $export_record_per_request, 'paged' => $request_number, 'orderby'=> 'ID', 'order' => 'ASC'];
            $query_args['tax_query']['relation'] = 'AND';
            //---------------------

            $order_results = new WP_Query($query_args);
            while($order_results->have_posts()) {

                $order_results->the_post();
                $order = new WC_Order($order_results->post->ID);

                $order_data = self::initialize_columns($csv_columns);

                $order_data[$columns['id']]                     = @$order->get_id();
                $order_data[$columns['status']]                 = @$order->get_status();
                $order_data[$columns['order_date']]             = @$order->get_date_created();

                $order_data[$columns['billing_first_name']]     = @$order->get_billing_first_name();
                $order_data[$columns['billing_last_name']]      = @$order->get_billing_last_name();
                $order_data[$columns['billing_company']]        = @$order->get_billing_company();
                $order_data[$columns['billing_address_1']]      = @$order->get_billing_address_1();
                $order_data[$columns['billing_address_2']]      = @$order->get_billing_address_2();
                $order_data[$columns['billing_city']]           = @$order->get_billing_city();
                $order_data[$columns['billing_state']]          = @$order->get_billing_state();
                $order_data[$columns['billing_postcode']]       = @$order->get_billing_postcode();
                $order_data[$columns['billing_country']]        = @$order->get_billing_country();
                $order_data[$columns['billing_email']]          = @$order->get_billing_email();
                $order_data[$columns['billing_phone']]          = @$order->get_billing_phone();

                $order_data[$columns['shipping_first_name']]    = @$order->get_shipping_first_name();
                $order_data[$columns['shipping_last_name']]     = @$order->get_shipping_last_name();
                $order_data[$columns['shipping_company']]       = @$order->get_shipping_company();
                $order_data[$columns['shipping_address_1']]     = @$order->get_shipping_address_1();
                $order_data[$columns['shipping_address_2']]     = @$order->get_shipping_address_2();
                $order_data[$columns['shipping_city']]          = @$order->get_shipping_city();
                $order_data[$columns['shipping_state']]         = @$order->get_shipping_state();
                $order_data[$columns['shipping_postcode']]      = @$order->get_shipping_postcode();
                $order_data[$columns['shipping_country']]       = @$order->get_shipping_country();

                $order_data[$columns['customer_note']]          = @$order->get_customer_note();
                $order_data[$columns['payment_method']]         = @$order->get_payment_method_title();
                $order_data[$columns['cart_discount']]          = @$order->get_total_discount();
                $order_data[$columns['order_tax']]              = @$order->get_total_tax();
                $order_data[$columns['order_total']]            = @$order->get_total();

                $order_data[$columns['products']]               = self::get_order_items( $order );

                if($order->get_coupon_codes()) {
                    $order_data[$columns['coupons']]            = json_encode($order->get_coupon_codes());
                }

                // Export Meta
                if ( $export_meta == 1 || $export_columns == 'use_mapping' ) {
                    $meta_list = $order->get_meta_data();
                    if ( count( $meta_list ) > 0 ) {
                        foreach ( $meta_list as $meta ) {
                            $columns['meta:'.$meta->key] = 'Meta:'.$meta->key;
                            $order_data[$columns['meta:'.$meta->key]] = $meta->value;
                        }
                    }
                }
                //------------

                // Filter data as per mapping
                if ( $export_columns == 'use_mapping') {
                    $mapped_order_data = null;
                    foreach ( $mapping_profile['mapping_columns'] as $data_source => $column_name ) {
                        $mapped_order_data[$column_name] = $order_data[$data_source];
                    }
                    $order_data = $mapped_order_data;
                }
                //---------------------------

                // Create CSV headers columns
                if ( $order_data ) {
                    $order_columns = array_keys( $order_data );
                    foreach ( $order_columns as $column_name ) {
                        if ( !is_array($csv_columns) || !in_array( $column_name, $csv_columns ) ) {
                            $csv_columns[] = $column_name;
                        }
                    }
                }
                //---------------------------

                $order_data_list[] = $order_data;
            }

            // Create if upload directory not exists
            if ( $fn_status == true ) {
                if ( !file_exists(SN_WIE_UPLOAD_PATH.'/order' ) ) {
                    if ( !mkdir(SN_WIE_UPLOAD_PATH.'/order', 0777, true ) ) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //--------------------------------------


            // Save data to CSV file
            if ( $fn_status == true ) {

                $file = fopen(SN_WIE_UPLOAD_PATH.'/order/'.$file_name, 'a');
                foreach ( $order_data_list as $order ) {
                    fputcsv($file, $order, $field_separator);
                }
                fclose( $file );

                // Add CSV headers in the last request
                if ( $request_number == $total_requests ) {
                    $file_content = file_get_contents( SN_WIE_UPLOAD_PATH.'/order/'.$file_name );
                    $file_content = implode($field_separator, $csv_columns)."\n".$file_content;
                    file_put_contents( SN_WIE_UPLOAD_PATH.'/order/'.$file_name, $file_content );
                }
                //------------------------------------

                $response = ['status' => true, 'code' => 300, 'message' => null, 'data' => ['file_name' => $file_name, 'file_url' => SN_WIE_UPLOAD_URL.'/order', 'total_requests' => $total_requests, 'request_number' => $request_number+1, 'order_export_count' => count($order_data_list), 'csv_columns' => $csv_columns]];
            }
            //----------------------

            wp_send_json( $response );
        }

        /**
         * Download exported file
         * @description Function to download exported file
         */
        public function download_export_file() {

            $file_name = @sanitize_file_name( trim( $_GET['file_name'] ) );
            $download_filename = @sanitize_file_name( trim( $_GET['download_filename'] ) );

            if(!$download_filename) {
                $download_filename = $file_name;
            }

            header('Content-Type: text/csv');
            header('Content-Transfer-Encoding: Binary');
            header('Content-disposition: attachment; filename="'.$download_filename.'"');
            readfile(SN_WIE_UPLOAD_PATH.'/order/'.$file_name, null);
            exit();
        }

        /**
         * Delete all logs
         * @description Function to delete all logs
         */
        public function delete_all_logs() {

            $files_list = scandir(SN_WIE_UPLOAD_PATH.'/order');

            foreach($files_list as $file) {
                $extension = pathinfo( $file, PATHINFO_EXTENSION );
                if( strtolower( $extension ) == 'csv' ) {
                    $file_path = SN_WIE_UPLOAD_PATH.'/order/'.$file;
                    if ( is_file( $file_path ) ) {
                        unlink( $file_path );
                    }
                }
            }

            // Set message
            SN_WIE_INIT::set_message(__('Logs deleted', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-orders&tab=log' ) );
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
                    update_option( 'sn_wie_order_import_record_per_request', $import_record_per_request );
                }

                $export_record_per_request = intval( $_POST['export_record_per_request'] );
                if( $export_record_per_request != 0 ) {
                    update_option( 'sn_wie_order_export_record_per_request', $export_record_per_request );
                }
            }
            //------------------------

            // Set message
            SN_WIE_INIT::set_message(__('Settings updated', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-orders&tab=setting' ) );
        }

        /**
         * Initialize columns
         * @description Function to initialize columns array
         * @param $csv_columns
         * @return array
         */
        private static function initialize_columns( $csv_columns ) {
            $order_data = [];

            foreach ( $csv_columns as $column ) {
                $order_data[$column] = null;
            }

            return ( $order_data );
        }

        /**
         * Get column names
         * @description Function to return column names
         * @return array
         */
        private static function get_column_names() {
            return [
                'id'                    => __( 'ID', SN_WIE_SLUG ),
                'status'                => __( 'Status', SN_WIE_SLUG ),
                'order_date'            => __( 'Order Date', SN_WIE_SLUG ),

                'billing_first_name'    => __( 'Billing First Name', SN_WIE_SLUG ),
                'billing_last_name'     => __( 'Billing Last Name', SN_WIE_SLUG ),
                'billing_company'       => __( 'Billing Company', SN_WIE_SLUG ),
                'billing_address_1'     => __( 'Billing Address 1', SN_WIE_SLUG ),
                'billing_address_2'     => __( 'Billing Address 2', SN_WIE_SLUG ),
                'billing_city'          => __( 'Billing City', SN_WIE_SLUG ),
                'billing_state'         => __( 'Billing State', SN_WIE_SLUG ),
                'billing_postcode'      => __( 'Billing Postcode', SN_WIE_SLUG ),
                'billing_country'       => __( 'Billing Country', SN_WIE_SLUG ),
                'billing_email'         => __( 'Billing Email', SN_WIE_SLUG ),
                'billing_phone'         => __( 'Billing Phone', SN_WIE_SLUG ),

                'shipping_first_name'   => __( 'Shipping First Name', SN_WIE_SLUG ),
                'shipping_last_name'    => __( 'Shipping Last Name', SN_WIE_SLUG ),
                'shipping_company'      => __( 'Shipping Company', SN_WIE_SLUG ),
                'shipping_address_1'    => __( 'Shipping Address 1', SN_WIE_SLUG ),
                'shipping_address_2'    => __( 'Shipping Address 2', SN_WIE_SLUG ),
                'shipping_city'         => __( 'Shipping City', SN_WIE_SLUG ),
                'shipping_state'        => __( 'Shipping State', SN_WIE_SLUG ),
                'shipping_postcode'     => __( 'Shipping Postcode', SN_WIE_SLUG ),
                'shipping_country'      => __( 'Shipping Country', SN_WIE_SLUG ),

                'customer_note'         => __( 'Customer Note', SN_WIE_SLUG ),
                'payment_method'        => __( 'Payment Method', SN_WIE_SLUG ),
                'cart_discount'         => __( 'Cart Discount', SN_WIE_SLUG ),
                'order_tax'             => __( 'Order Tax', SN_WIE_SLUG ),
                'order_total'           => __( 'Order Total', SN_WIE_SLUG ),

                'products'              => __( 'Products', SN_WIE_SLUG ),
                'coupons'               => __( 'Coupons', SN_WIE_SLUG )
            ];
        }

        /**
         * Get option
         * @description Function to return option value
         * @param $key
         * @param $global
         * @return mixed
         */
        private static function get_option( $key, $global = false ) {
            $value = null;

            if( $global == false ) {
                $value = get_option('sn_wie_order_'.$key);
            }

            if( !$value ) {
                $value = get_option( 'sn_wie_'.$key );
            }

            if( !$value ) {
                $value = constant(  'SN_WIE_'.strtoupper( $key ) );
            }

            return ( $value );
        }

        /**
         * Get order items
         * @description Function to return order items from order obejct
         * @param $order
         * @return string
         */
        private static function get_order_items( $order ) {
            $order_items = $order->get_items();
            $line_items = [];
            //$item_no = 1;
            foreach($order_items as $item_id) {
                $item = $order->get_item($item_id);
                $item_data = $item->get_data();
                $line_item = null;
                $product = null;
                if(isset($item_data['product_id'])) {
                    $product = wc_get_product($item_data['product_id']);
                }
                // Set Product Name
                //$line_items .= 'Name:'.$item->get_name();
                $line_item['name'] = $item->get_name();
                //-----------------

                // Set Product SKU
                if($product) {
                    if(@$product->get_sku()) {
                        //$line_items .= '|SKU:'.$product->get_sku();
                        $line_item['sku'] = $product->get_sku();
                    }
                }
                //----------------

                // Set Product Meta Details
                //$line_items .= '|Qty:'.$item->get_quantity().'|Cost:'.$item_data['subtotal'].'|Total:'.$item_data['total'];
                $line_item['quantity'] = $item->get_quantity();
                $line_item['price'] = $item_data['subtotal'];
                $line_item['total'] = $item_data['total'];
                //-------------------------

                $line_items[] = $line_item;

                //if($item_no < count($order_items)) {
                //    $line_items .= '#';
                //}
                //$item_no++;
            }
            return ( json_encode( $line_items ) );
        }

        /**
         * Get mapping profile list
         * @description Function to return mapping profiles
         * @return array
         */
        public static function get_mapping_profile_list() {
            $mapping_profile_list = [];

            $mapping_list = get_option( 'sn_wie_order_mapping_profile_list' );
            if($mapping_list) {
                $mapping_profile_list = $mapping_list;
            }

            return( $mapping_profile_list );
        }

        /**
         * Get scheduling profile list
         * @description Function to return scheduling profiles
         * @return array
         */
        public static function get_schedule_profile_list() {
            $schedule_profile_list = [];
            $schedule_list = wp_get_schedules();
            $wp_cron_list = _get_cron_array();

            foreach($wp_cron_list as $time => $cron) {
                foreach($cron as $key => $schedule) {
                    if( $key == 'sn_wie_order_export_schedule' ) {
                        $schedule_detail = array_values($schedule);
                        $schedule_detail[0]['args']['schedule_type'] = 'export';
                        $schedule_detail[0]['args']['next_run_time'] = $time;
                        $schedule_detail[0]['args']['recurring_interval'] = $schedule_list[$schedule_detail[0]['args']['schedule_interval']]['display'];
                        $schedule_profile_list[] = $schedule_detail[0]['args'];
                    }
                    elseif( $key == 'sn_wie_order_import_schedule' ) {
                        $schedule_detail = array_values($schedule);
                        $schedule_detail[0]['args']['schedule_type'] = 'import';
                        $schedule_detail[0]['args']['next_run_time'] = $time;
                        $schedule_detail[0]['args']['recurring_interval'] = $schedule_list[$schedule_detail[0]['args']['schedule_interval']]['display'];
                        $schedule_profile_list[] = $schedule_detail[0]['args'];
                    }
                }
            }
            return( $schedule_profile_list );
        }

        /**
         * Get logs
         * @description Function to return log list
         * @return array
         */
        public static function get_log_list() {
            $file_list = [];
            if( file_exists(SN_WIE_UPLOAD_PATH.'/order') ) {
                $old_files_list = scandir(SN_WIE_UPLOAD_PATH.'/order');
                foreach($old_files_list as $file) {
                    $extension = pathinfo( $file, PATHINFO_EXTENSION );
                    if( strtolower( $extension ) == 'csv' ) {
                        $file_path = SN_WIE_UPLOAD_PATH.'/order/'.$file;
                        $file_url = admin_url( 'admin-post.php?action=sn_wie_download_order_export_file&file_name='.$file );
                        $file_list[filemtime($file_path)] = ['name' => $file, 'path' => $file_path, 'url' => $file_url];
                    }
                }
                ksort( $file_list );
            }
            return( $file_list );
        }
    }
}

$sn_wie_order = new SN_WIE_ORDER();