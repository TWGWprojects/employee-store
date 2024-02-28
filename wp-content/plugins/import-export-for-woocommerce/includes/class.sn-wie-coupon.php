<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_COUPON' ) ) {

    class SN_WIE_COUPON {

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
            add_action( 'wp_ajax_sn_wie_create_coupon_csv_file', array( &$this, 'create_csv_file' ) );
            add_action( 'wp_ajax_sn_wie_export_coupon_csv_file', array( &$this, 'export_csv_file' ) );
            //-------

            // Log
            add_action( 'admin_post_sn_wie_delete_coupon_log', array( &$this, 'delete_all_logs' ) );
            //----

            // Setting
            add_action( 'admin_post_sn_wie_update_coupon_setting', array( &$this, 'update_setting' ) );
            //--------

            // Download Export File
            add_action( 'admin_post_sn_wie_download_coupon_export_file', array( &$this, 'download_export_file' ) );
            //---------------------
        }

        /**
         * Set page Assets
         * @description Function to include the JS for coupon page
         */
        public function set_page_assets() {
            $page = @sanitize_key( $_GET['page'] );
            if ( $page == 'sn-wie-coupons') {
                wp_enqueue_style('sn-wie-select2-css', SN_WIE_ASSET_URL . '/css/select2.min.css', SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-select2-js', SN_WIE_ASSET_URL . '/js/select2.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-validate-js', SN_WIE_ASSET_URL . '/js/jquery.validate.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-coupon-js', SN_WIE_ASSET_URL . '/js/coupon.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
            }
        }

        /**
         * Coupon page
         * @description Function to show the coupon page based upon the argument
         */
        public static function coupon_page() {
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
            $args['page_name'] = 'export';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['mapping_profile_list'] = $mapping_profile_list;
            $args['field_separator'] = self::get_option( 'field_separator', true );
            wc_get_template( 'export.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            wc_get_template( 'import.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            wc_get_template( 'mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            wc_get_template( 'add-mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            wc_get_template( 'schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            wc_get_template( 'add-schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            wc_get_template( 'log.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            $args['options']['coupon_import_record_per_request'] = get_option( 'sn_wie_coupon_import_record_per_request' );
            $args['options']['coupon_export_record_per_request'] = get_option( 'sn_wie_coupon_export_record_per_request' );
            $args['options']['import_record_per_request'] = self::get_option( 'import_record_per_request', true );
            $args['options']['export_record_per_request'] = self::get_option( 'export_record_per_request', true );
            wc_get_template( 'setting.php', $args, '', SN_WIE_TEMPLATE_PATH .'/coupon/' );
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
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;

            // Get request variables
            if($fn_status == true) {
                $export_columns = sanitize_text_field( $_POST['export_columns'] );
                $export_meta = array_map( 'sanitize_text_field', (array) $_POST['export_meta'] );
                $field_separator = sanitize_text_field( trim( $_POST['field_separator'] ) );
                $download_filename = sanitize_text_field( trim( $_POST['download_filename'] ) );
            }
            //----------------------

            // Get config variables
            $export_record_per_request = self::get_option( 'export_record_per_request' );
            $field_separator = $field_separator?$field_separator:self::get_option( 'field_separator' );
            //---------------------

            // Create if upload directory not exists
            if($fn_status == true) {
                if (!file_exists(SN_WIE_UPLOAD_PATH.'/coupon')) {
                    if(!mkdir(SN_WIE_UPLOAD_PATH.'/coupon', 0777, true)) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //---------------------------------------

            // Create empty CSV file
            if($fn_status == true) {
                $export_file_name = 'export_manual_'.current_time('timestamp').'.csv';
                if(file_put_contents(SN_WIE_UPLOAD_PATH.'/coupon/'.$export_file_name, null) === false) {
                    $fn_status = false;
                    $response = ['status' => false, 'code' => 300, 'message' => __('Unable to create file', SN_WIE_SLUG), 'data' => null];
                }
            }
            //----------------------

            // Count total records
            if($fn_status == true) {
                $query_args = ['post_type' => 'shop_coupon', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby'=> 'ID', 'order' => 'ASC'];
                $product_results = new WP_Query($query_args);
                $total_requests = ceil($product_results->found_posts/$export_record_per_request);
                $response = ['status' => true, 'code' => 104, 'message' => 'Success', 'data' => ['export_file_name' => $export_file_name, 'total_requests' => $total_requests, 'export_columns' => $export_columns, 'export_meta' => $export_meta, 'field_separator' => $field_separator, 'download_filename' => $download_filename]];
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
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;
            $columns = null;
            $csv_columns = null;
            $coupon_data_list = [];
            $mapping_profile = null;

            // Get config variables
            $export_record_per_request = self::get_option( 'export_record_per_request' );
            //---------------------

            // Get request variables
            if( $fn_status == true ) {
                $export_columns = sanitize_text_field( $_POST['export_columns'] );
                $export_meta = intval( $_POST['export_meta'] );
                $field_separator = sanitize_text_field( trim( $_POST['field_separator'] ) );
                $download_filename = sanitize_text_field( trim( $_POST['download_filename'] ) );
                $total_requests = intval( $_POST['total_requests'] );
                $request_number = intval( $_POST['request_number'] );
                $file_name = sanitize_text_field( $_POST['file_name'] );
                $csv_columns = array_map( 'sanitize_text_field', (array) $_POST['csv_columns'] );
            }
            //----------------------

            // Fetch mapping profile if required
            if( $fn_status == true ) {
                if(strpos($export_columns, 'use_mapping') !== false) {
                    $mapping_list = self::get_mapping_profile_list();
                    $mapping_profile = $mapping_list[str_replace('use_mapping_', '', $export_columns)];
                    $export_columns = 'use_mapping';
                }
            }
            //----------------------------------

            // Get all default columns
            if( $fn_status == true ) {
                $columns = self::get_column_names();
            }
            //------------------------

            // Set Query Parameters
            $query_args = ['post_type' => 'shop_coupon', 'post_status' => 'any', 'posts_per_page' => $export_record_per_request, 'paged' => $request_number, 'orderby'=> 'ID', 'order' => 'ASC'];
            //---------------------

            $coupon_results = new WP_Query($query_args);
            while( $coupon_results->have_posts() ) {

                $product_attributes = null;
                $coupon_results->the_post();
                $coupon_data = self::initialize_columns($csv_columns);

                $coupon = new WC_Coupon($coupon_results->post->ID);
                $coupon_data[$columns['id']]                    = @$coupon->get_id();
                $coupon_data[$columns['code']]                  = @$coupon->get_code();
                $coupon_data[$columns['published']]             = $coupon_results->post->post_status=='publish'?1:0;
                $coupon_data[$columns['description']]           = @$coupon->get_description();
                $coupon_data[$columns['discount_type']]         = @$coupon->get_discount_type();
                $coupon_data[$columns['discount_amount']]       = @$coupon->get_amount();
                $coupon_data[$columns['free_shipping']]         = @$coupon->get_free_shipping();
                $coupon_data[$columns['date_expires']]          = @$coupon->get_date_expires();
                $coupon_data[$columns['minimum_amount']]        = @$coupon->get_minimum_amount();
                $coupon_data[$columns['maximum_amount']]        = @$coupon->get_maximum_amount();
                $coupon_data[$columns['individual_use']]        = @$coupon->get_individual_use();
                $coupon_data[$columns['exclude_sale_items']]    = @$coupon->get_exclude_sale_items();
                $coupon_data[$columns['product_ids']]           = implode( '|', $coupon->get_product_ids() );
                $coupon_data[$columns['excluded_product_ids']]  = implode( '|', $coupon->get_excluded_product_ids() );
                $coupon_data[$columns['product_categories']]    = implode( '|', $coupon->get_product_categories() );
                $coupon_data[$columns['excluded_product_categories']]      = implode( ',', $coupon->get_excluded_product_categories() );
                $coupon_data[$columns['email_restrictions']]    = implode( ',', $coupon->get_email_restrictions() );
                $coupon_data[$columns['usage_limit']]           = @$coupon->get_usage_limit();
                $coupon_data[$columns['usage_limit_per_user']]  = @$coupon->get_usage_limit_per_user();
                $coupon_data[$columns['usage_count']]           = @$coupon->get_usage_count();

                // Export Meta
                if ( $export_meta == 1 || $export_columns == 'use_mapping' ) {
                    $meta_list = $coupon->get_meta_data();
                    if ( count( $meta_list ) > 0 ) {
                        foreach ( $meta_list as $meta ) {
                            $columns['meta:'.$meta->key] = 'Meta:'.$meta->key;
                            $coupon_data[$columns['meta:'.$meta->key]] = $meta->value;
                        }
                    }
                }
                //------------

                // Filter data as per mapping
                if ( $export_columns == 'use_mapping') {
                    $mapped_coupon_data = null;
                    foreach ( $mapping_profile['mapping_columns'] as $data_source => $column_name ) {
                        $mapped_coupon_data[$column_name] = $coupon_data[$data_source];
                    }
                    $coupon_data = $mapped_coupon_data;
                }
                //---------------------------

                // Create CSV headers columns
                if ( $coupon_data ) {
                    $coupon_columns = array_keys( $coupon_data );
                    foreach ( $coupon_columns as $column_name ) {
                        if ( !is_array($csv_columns) || !in_array( $column_name, $csv_columns ) ) {
                            $csv_columns[] = $column_name;
                        }
                    }
                }
                //---------------------------

                $coupon_data_list[] = $coupon_data;
            }

            // Create if upload directory not exists
            if ( $fn_status == true ) {
                if ( !file_exists(SN_WIE_UPLOAD_PATH.'/coupon' ) ) {
                    if ( !mkdir(SN_WIE_UPLOAD_PATH.'/coupon', 0777, true ) ) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //--------------------------------------


            // Save data to CSV file
            if ( $fn_status == true ) {

                $file = fopen(SN_WIE_UPLOAD_PATH.'/coupon/'.$file_name, 'a');
                foreach ( $coupon_data_list as $coupon ) {
                    fputcsv($file, $coupon, $field_separator);
                }
                fclose( $file );

                // Add CSV headers in the last request
                if ( $request_number == $total_requests ) {
                    $file_content = file_get_contents( SN_WIE_UPLOAD_PATH.'/coupon/'.$file_name );
                    $file_content = implode($field_separator, $csv_columns)."\n".$file_content;
                    file_put_contents( SN_WIE_UPLOAD_PATH.'/coupon/'.$file_name, $file_content );
                }
                //------------------------------------

                $response = ['status' => true, 'code' => 300, 'message' => null, 'data' => ['file_name' => $file_name, 'file_url' => SN_WIE_UPLOAD_URL.'/coupon', 'total_requests' => $total_requests, 'request_number' => $request_number+1, 'coupon_export_count' => count($coupon_data_list), 'csv_columns' => $csv_columns]];
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
            readfile(SN_WIE_UPLOAD_PATH.'/coupon/'.$file_name, null);
            exit();
        }

        /**
         * Delete all logs
         * @description Function to delete all logs
         */
        public function delete_all_logs() {

            $files_list = scandir(SN_WIE_UPLOAD_PATH.'/coupon');

            foreach($files_list as $file) {
                $extension = pathinfo( $file, PATHINFO_EXTENSION );
                if( strtolower( $extension ) == 'csv' ) {
                    $file_path = SN_WIE_UPLOAD_PATH.'/coupon/'.$file;
                    if ( is_file( $file_path ) ) {
                        unlink( $file_path );
                    }
                }
            }

            // Set message
            SN_WIE_INIT::set_message(__('Logs deleted', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-coupons&tab=log' ) );
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
                    update_option( 'sn_wie_coupon_import_record_per_request', $import_record_per_request );
                }

                $export_record_per_request = intval( $_POST['export_record_per_request'] );
                if( $export_record_per_request != 0 ) {
                    update_option( 'sn_wie_coupon_export_record_per_request', $export_record_per_request );
                }
            }
            //------------------------

            // Set message
            SN_WIE_INIT::set_message(__('Settings updated', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-coupons&tab=setting' ) );
        }

        /**
         * Initialize columns
         * @description Function to initialize columns array
         * @param $csv_columns
         * @return array
         */
        private static function initialize_columns( $csv_columns ) {
            $coupon_data = [];

            if( @count( $csv_columns ) > 0 ) {
                foreach( $csv_columns as $column ) {
                    $coupon_data[$column] = null;
                }
            }

            return( $coupon_data );
        }

        /**
         * Get column names
         * @description Function to return column names
         * @return array
         */
        private static function get_column_names() {
            return [
                'id'                            => __( 'ID', SN_WIE_SLUG ),
                'code'                          => __( 'Code', SN_WIE_SLUG ),
                'published'                     => __( 'Published', SN_WIE_SLUG ),
                'description'                   => __( 'Description', SN_WIE_SLUG ),
                'discount_type'                 => __( 'Discount Type', SN_WIE_SLUG ),
                'discount_amount'               => __( 'Discount Amount', SN_WIE_SLUG ),
                'free_shipping'                 => __( 'Free Shipping Allowed?', SN_WIE_SLUG ),
                'date_expires'                  => __( 'Coupon Expiry Date', SN_WIE_SLUG ),
                'minimum_amount'                => __( 'Minimum Spend Amount', SN_WIE_SLUG ),
                'maximum_amount'                => __( 'Maximum Spend Amount', SN_WIE_SLUG ),
                'individual_use'                => __( 'Individual Use', SN_WIE_SLUG ),
                'exclude_sale_items'            => __( 'Exclude Sale Items', SN_WIE_SLUG ),
                'product_ids'                   => __( 'Products', SN_WIE_SLUG ),
                'excluded_product_ids'          => __( 'Excluded Products', SN_WIE_SLUG ),
                'product_categories'            => __( 'Categories', SN_WIE_SLUG ),
                'excluded_product_categories'   => __( 'Excluded Categories', SN_WIE_SLUG ),
                'email_restrictions'            => __( 'Allowed Emails', SN_WIE_SLUG ),
                'usage_limit'                   => __( 'Usage Limit', SN_WIE_SLUG ),
                'usage_limit_per_user'          => __( 'Usage Limit Per User', SN_WIE_SLUG ),
                'usage_count'                   => __( 'Usage Count', SN_WIE_SLUG )
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
                $value = get_option('sn_wie_coupon_'.$key);
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
         * Get mapping profile list
         * @description Function to return mapping profiles
         * @return array
         */
        public static function get_mapping_profile_list() {
            $mapping_profile_list = [];

            $mapping_list = get_option( 'sn_wie_coupon_mapping_profile_list' );
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
                    if( $key == 'sn_wie_coupon_export_schedule' ) {
                        $schedule_detail = array_values($schedule);
                        $schedule_detail[0]['args']['schedule_type'] = 'export';
                        $schedule_detail[0]['args']['next_run_time'] = $time;
                        $schedule_detail[0]['args']['recurring_interval'] = $schedule_list[$schedule_detail[0]['args']['schedule_interval']]['display'];
                        $schedule_profile_list[] = $schedule_detail[0]['args'];
                    }
                    elseif( $key == 'sn_wie_coupon_import_schedule' ) {
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
            if( file_exists(SN_WIE_UPLOAD_PATH.'/coupon') ) {
                $old_files_list = scandir(SN_WIE_UPLOAD_PATH.'/coupon');
                foreach($old_files_list as $file) {
                    $extension = pathinfo( $file, PATHINFO_EXTENSION );
                    if( strtolower( $extension ) == 'csv' ) {
                        $file_path = SN_WIE_UPLOAD_PATH.'/coupon/'.$file;
                        //$file_url = SN_WIE_UPLOAD_URL.'/coupon/'.$file;
                        $file_url = admin_url( 'admin-post.php?action=sn_wie_download_coupon_export_file&file_name='.$file );
                        $file_list[filemtime($file_path)] = ['name' => $file, 'path' => $file_path, 'url' => $file_url];
                    }
                }
                ksort( $file_list );
            }
            return( $file_list );
        }
    }
}

$sn_wie_coupon = new SN_WIE_COUPON();