<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_USER' ) ) {
    class SN_WIE_USER {

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
            add_action( 'wp_ajax_sn_wie_create_user_csv_file', array( &$this, 'create_csv_file' ) );
            add_action( 'wp_ajax_sn_wie_export_user_csv_file', array( &$this, 'export_csv_file' ) );
            //-------

            // Log
            add_action( 'admin_post_sn_wie_delete_user_log', array( &$this, 'delete_all_logs' ) );
            //----

            // Setting
            add_action( 'admin_post_sn_wie_update_user_setting', array( &$this, 'update_setting' ) );
            //--------

            // Download Export File
            add_action( 'admin_post_sn_wie_download_user_export_file', array( &$this, 'download_export_file' ) );
            //---------------------
        }

        /**
         * Set page Assets
         * @description Function to include the JS for user page
         */
        public function set_page_assets() {
            $page = @sanitize_key( $_GET['page'] );
            if ( $page == 'sn-wie-users') {
                wp_enqueue_style('sn-wie-select2-css', SN_WIE_ASSET_URL . '/css/select2.min.css', SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-select2-js', SN_WIE_ASSET_URL . '/js/select2.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-validate-js', SN_WIE_ASSET_URL . '/js/jquery.validate.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-user-js', SN_WIE_ASSET_URL . '/js/user.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
            }
        }

        /**
         * Product page
         * @description Function to show the user page based upon the argument
         */
        public static function user_page() {
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
            global $wp_roles;

            $mapping_profile_list = self::get_mapping_profile_list();
            $role_list = $wp_roles->get_names();
            $args['page_name'] = 'export';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['mapping_profile_list'] = $mapping_profile_list;
            $args['role_list'] = $role_list;
            $args['field_separator'] = self::get_option( 'field_separator', true );
            wc_get_template( 'export.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
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
            wc_get_template( 'import.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
        }

        /**
         * Mapping page
         * @description Function to show the mapping page
         */
        private static function mapping_page() {
            $args['page_name'] = 'mapping';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            wc_get_template( 'mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
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
            wc_get_template( 'add-mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
        }

        /**
         * Scheduling page
         * @description Function to show the scheduling profile page
         */
        private static function schedule_page() {
            $args['page_name'] = 'schedule';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            wc_get_template( 'schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
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
            wc_get_template( 'add-schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
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
            wc_get_template( 'log.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
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
            $args['options']['user_import_record_per_request'] = get_option( 'sn_wie_user_import_record_per_request' );
            $args['options']['user_export_record_per_request'] = get_option( 'sn_wie_user_export_record_per_request' );
            $args['options']['import_record_per_request'] = self::get_option( 'import_record_per_request', true );
            $args['options']['export_record_per_request'] = self::get_option( 'export_record_per_request', true );
            wc_get_template( 'setting.php', $args, '', SN_WIE_TEMPLATE_PATH .'/user/' );
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
            $export_user_roles = null;
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;

            // Get request variables
            if($fn_status == true) {
                $export_columns = $_POST['export_columns'];
                $export_user_roles = $_POST['export_user_roles'];
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
                if (!file_exists(SN_WIE_UPLOAD_PATH.'/user')) {
                    if(!mkdir(SN_WIE_UPLOAD_PATH.'/user', 0777, true)) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //---------------------------------------

            // Create empty CSV file
            if($fn_status == true) {
                $export_file_name = 'export_manual_'.current_time('timestamp').'.csv';
                if(file_put_contents(SN_WIE_UPLOAD_PATH.'/user/'.$export_file_name, null) === false) {
                    $fn_status = false;
                    $response = ['status' => false, 'code' => 300, 'message' => __('Unable to create file', SN_WIE_SLUG), 'data' => null];
                }
            }
            //----------------------

            // Count total records
            if($fn_status == true) {

                $query_args = ['posts_per_page' => -1, 'orderby'=> 'ID', 'order' => 'ASC'];
                if($export_user_roles) {
                    $query_args['role__in'] = $export_user_roles;
                }
                $user_results = new WP_User_Query($query_args);
                $total_requests = ceil($user_results->get_total()/$export_record_per_request);
                $response = ['status' => true, 'code' => 104, 'message' => 'Success', 'data' => ['export_file_name' => $export_file_name, 'total_requests' => $total_requests, 'export_columns' => $export_columns, 'export_user_roles' => $export_user_roles, 'export_meta' => $export_meta, 'field_separator' => $field_separator, 'download_filename' => $download_filename]];
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
            $export_user_roles = null;
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;
            $columns = null;
            $csv_columns = null;
            $user_data_list = [];
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
                $export_user_roles = $_POST['export_user_roles'];
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
            $query_args = ['posts_per_page' => $export_record_per_request, 'paged' => $request_number, 'orderby'=> 'ID', 'order' => 'ASC'];
            if($export_user_roles) {
                $query_args['role__in'] = $export_user_roles;
            }
            //---------------------

            $user_results = new WP_User_Query($query_args);
            foreach($user_results->get_results() as $user) {

                $user_data = null;
                $user_meta = get_user_meta($user->ID);

                $user_data = self::initialize_columns($csv_columns);

                $user_data[$columns['roles']]                 = implode(', ', $user->roles);
                $user_data[$columns['username']]              = @$user->user_login;
                $user_data[$columns['user_email']]            = @$user->user_email;
                $user_data[$columns['display_name']]          = @$user->display_name;
                $user_data[$columns['first_name']]            = @$user->first_name;
                $user_data[$columns['last_name']]             = @$user->last_name;
                $user_data[$columns['nickname']]              = @$user_meta['nickname'][0];

                $user_data[$columns['billing_first_name']]    = @$user_meta['billing_first_name'][0];
                $user_data[$columns['billing_last_name']]     = @$user_meta['billing_last_name'][0];
                $user_data[$columns['billing_company']]       = @$user_meta['billing_company'][0];
                $user_data[$columns['billing_email']]         = @$user_meta['billing_email'][0];
                $user_data[$columns['billing_phone']]         = @$user_meta['billing_phone'][0];
                $user_data[$columns['billing_address_1']]     = @$user_meta['billing_address_1'][0];
                $user_data[$columns['billing_address_2']]     = @$user_meta['billing_address_2'][0];
                $user_data[$columns['billing_postcode']]      = @$user_meta['billing_postcode'][0];
                $user_data[$columns['billing_city']]          = @$user_meta['billing_city'][0];
                $user_data[$columns['billing_state']]         = @$user_meta['billing_state'][0];
                $user_data[$columns['billing_country']]       = @$user_meta['billing_country'][0];

                $user_data[$columns['shipping_first_name']]   = @$user_meta['shipping_first_name'][0];
                $user_data[$columns['shipping_last_name']]    = @$user_meta['shipping_last_name'][0];
                $user_data[$columns['shipping_company']]      = @$user_meta['shipping_company'][0];
                $user_data[$columns['shipping_address_1']]    = @$user_meta['shipping_address_1'][0];
                $user_data[$columns['shipping_address_2']]    = @$user_meta['shipping_address_2'][0];
                $user_data[$columns['shipping_postcode']]     = @$user_meta['shipping_postcode'][0];
                $user_data[$columns['shipping_city']]         = @$user_meta['shipping_city'][0];
                $user_data[$columns['shipping_state']]        = @$user_meta['shipping_state'][0];
                $user_data[$columns['shipping_country']]      = @$user_meta['shipping_country'][0];

                $user_data[$columns['register_date']]         = @$user->user_registered;

                // Export Meta
                if ( $export_meta == 1 || $export_columns == 'use_mapping' ) {
                    if ( count( $user_meta ) > 0 ) {
                        foreach ( $user_meta as $key => $meta ) {
                            if ( ! array_key_exists( $key, $columns ) ) {
                                $columns['meta:'.$key] = 'Meta:'.$key;
                                $user_data[$columns['meta:'.$key]] = $meta[0];
                            }
                        }
                    }
                }
                //------------

                // Filter data as per mapping
                if ( $export_columns == 'use_mapping') {
                    $mapped_user_data = null;
                    foreach ( $mapping_profile['mapping_columns'] as $data_source => $column_name ) {
                        $mapped_user_data[$column_name] = $user_data[$data_source];
                    }
                    $user_data = $mapped_user_data;
                }
                //---------------------------

                // Create CSV headers columns
                if ( $user_data ) {
                    $user_columns = array_keys( $user_data );
                    foreach ( $user_columns as $column_name ) {
                        if ( !is_array($csv_columns) || !in_array( $column_name, $csv_columns ) ) {
                            $csv_columns[] = $column_name;
                        }
                    }
                }
                //---------------------------

                $user_data_list[] = $user_data;
            }

            // Create if upload directory not exists
            if ( $fn_status == true ) {
                if ( !file_exists(SN_WIE_UPLOAD_PATH.'/user' ) ) {
                    if ( !mkdir(SN_WIE_UPLOAD_PATH.'/user', 0777, true ) ) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //--------------------------------------


            // Save data to CSV file
            if ( $fn_status == true ) {

                $file = fopen(SN_WIE_UPLOAD_PATH.'/user/'.$file_name, 'a');
                foreach ( $user_data_list as $user ) {
                    fputcsv($file, $user, $field_separator);
                }
                fclose( $file );

                // Add CSV headers in the last request
                if ( $request_number == $total_requests ) {
                    $file_content = file_get_contents( SN_WIE_UPLOAD_PATH.'/user/'.$file_name );
                    $file_content = implode($field_separator, $csv_columns)."\n".$file_content;
                    file_put_contents( SN_WIE_UPLOAD_PATH.'/user/'.$file_name, $file_content );
                }
                //------------------------------------

                $response = ['status' => true, 'code' => 300, 'message' => null, 'data' => ['file_name' => $file_name, 'file_url' => SN_WIE_UPLOAD_URL.'/user', 'total_requests' => $total_requests, 'request_number' => $request_number+1, 'user_export_count' => count($user_data_list), 'csv_columns' => $csv_columns]];
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
            readfile(SN_WIE_UPLOAD_PATH.'/user/'.$file_name, null);
            exit();
        }

        /**
         * Delete all logs
         * @description Function to delete all logs
         */
        public function delete_all_logs() {

            $files_list = scandir(SN_WIE_UPLOAD_PATH.'/user');

            foreach($files_list as $file) {
                $extension = pathinfo( $file, PATHINFO_EXTENSION );
                if( strtolower( $extension ) == 'csv' ) {
                    $file_path = SN_WIE_UPLOAD_PATH.'/user/'.$file;
                    if ( is_file( $file_path ) ) {
                        unlink( $file_path );
                    }
                }
            }

            // Set message
            SN_WIE_INIT::set_message(__('Logs deleted', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-users&tab=log' ));
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
                    update_option( 'sn_wie_user_import_record_per_request', $import_record_per_request );
                }

                $export_record_per_request = intval( $_POST['export_record_per_request'] );
                if( $export_record_per_request != 0 ) {
                    update_option( 'sn_wie_user_export_record_per_request', $export_record_per_request );
                }
            }
            //------------------------

            // Set message
            SN_WIE_INIT::set_message(__('Settings updated', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-users&tab=setting' ) );
        }

        /**
         * Initialize columns
         * @description Function to initialize columns array
         * @param $csv_columns
         * @return array
         */
        private static function initialize_columns( $csv_columns ) {
            $product_data = [];

            foreach( $csv_columns as $column ) {
                $product_data[$column] = null;
            }

            return( $product_data );
        }

        /**
         * Get column names
         * @description Function to return column names
         * @return array
         */
        private static function get_column_names() {
            return [
                'roles'                 => __( 'Roles', SN_WIE_SLUG ),
                'username'              => __( 'Username', SN_WIE_SLUG ),
                'user_email'            => __( 'Email', SN_WIE_SLUG ),
                'display_name'          => __( 'Display Name', SN_WIE_SLUG ),
                'nickname'              => __( 'Nickname', SN_WIE_SLUG ),
                'first_name'            => __( 'First Name', SN_WIE_SLUG ),
                'last_name'             => __( 'Last Name', SN_WIE_SLUG ),
                'admin_color'           => __( 'Admin Color', SN_WIE_SLUG ),

                'billing_first_name'    => __( 'Billing First Name', SN_WIE_SLUG ),
                'billing_last_name'     => __( 'Billing Last Name', SN_WIE_SLUG ),
                'billing_company'       => __( 'Billing Company', SN_WIE_SLUG ),
                'billing_email'         => __( 'Billing Email', SN_WIE_SLUG ),
                'billing_phone'         => __( 'Billing Phone', SN_WIE_SLUG ),
                'billing_address_1'     => __( 'Billing Address 1', SN_WIE_SLUG ),
                'billing_address_2'     => __( 'Billing Address 2', SN_WIE_SLUG ),
                'billing_postcode'      => __( 'Billing Postcode', SN_WIE_SLUG ),
                'billing_city'          => __( 'Billing City', SN_WIE_SLUG ),
                'billing_state'         => __( 'Billing State', SN_WIE_SLUG ),
                'billing_country'       => __( 'Billing Country', SN_WIE_SLUG ),

                'shipping_first_name'   => __( 'Shipping First Name', SN_WIE_SLUG ),
                'shipping_last_name'    => __( 'Shipping Last Name', SN_WIE_SLUG ),
                'shipping_company'      => __( 'Shipping Company', SN_WIE_SLUG ),
                'shipping_address_1'    => __( 'Shipping Address 1', SN_WIE_SLUG ),
                'shipping_address_2'    => __( 'Shipping Address 2', SN_WIE_SLUG ),
                'shipping_postcode'     => __( 'Shipping Postcode', SN_WIE_SLUG ),
                'shipping_city'         => __( 'Shipping City', SN_WIE_SLUG ),
                'shipping_state'        => __( 'Shipping State', SN_WIE_SLUG ),
                'shipping_country'      => __( 'Shipping Country', SN_WIE_SLUG ),

                'register_date'         => __( 'Register Date', SN_WIE_SLUG ),
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
                $value = get_option('sn_wie_user_'.$key);
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

            $mapping_list = get_option( 'sn_wie_user_mapping_profile_list' );
            if($mapping_list) {
                $mapping_profile_list = $mapping_list;
            }

            return ( $mapping_profile_list );
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
                    if( $key == 'sn_wie_user_export_schedule' ) {
                        $schedule_detail = array_values($schedule);
                        $schedule_detail[0]['args']['schedule_type'] = 'export';
                        $schedule_detail[0]['args']['next_run_time'] = $time;
                        $schedule_detail[0]['args']['recurring_interval'] = $schedule_list[$schedule_detail[0]['args']['schedule_interval']]['display'];
                        $schedule_profile_list[] = $schedule_detail[0]['args'];
                    }
                    elseif( $key == 'sn_wie_user_import_schedule' ) {
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
            if( file_exists( SN_WIE_UPLOAD_PATH.'/user' ) ) {
                $old_files_list = scandir(SN_WIE_UPLOAD_PATH.'/user');
                foreach($old_files_list as $file) {
                    $extension = pathinfo( $file, PATHINFO_EXTENSION );
                    if( strtolower( $extension ) == 'csv' ) {
                        $file_path = SN_WIE_UPLOAD_PATH.'/user/'.$file;
                        $file_url = admin_url( 'admin-post.php?action=sn_wie_download_user_export_file&file_name='.$file );
                        $file_list[filemtime($file_path)] = ['name' => $file, 'path' => $file_path, 'url' => $file_url];
                    }
                }
                ksort( $file_list );
            }
            return( $file_list );
        }
    }
}

$sn_wie_user = new SN_WIE_USER();