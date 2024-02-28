<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'SN_WIE_PRODUCT' ) ) {
    class SN_WIE_PRODUCT {

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
            add_action( 'wp_ajax_sn_wie_create_product_csv_file', array( &$this, 'create_csv_file' ) );
            add_action( 'wp_ajax_sn_wie_export_product_csv_file', array( &$this, 'export_csv_file' ) );
            //-------

            // Import
            add_action( 'wp_ajax_sn_wie_upload_product_csv_file', array( &$this, 'upload_csv_file' ) );
            add_action( 'wp_ajax_sn_wie_import_product_csv_file', array( &$this, 'import_csv_file' ) );
            //-------

            // Log
            add_action( 'admin_post_sn_wie_delete_product_log', array( &$this, 'delete_all_logs' ) );
            //----

            // Setting
            add_action( 'admin_post_sn_wie_update_product_setting', array( &$this, 'update_setting' ) );
            //--------

            // Download Export File
            add_action( 'admin_post_sn_wie_download_product_export_file', array( &$this, 'download_export_file' ) );
            //---------------------
        }

        /**
         * Set page assets
         * @description Function to include the JS for product page
         */
        public function set_page_assets() {
            $page = @sanitize_key( $_GET['page'] );
            if ( $page == 'sn-wie-products') {
                wp_enqueue_style('sn-wie-select2-css', SN_WIE_ASSET_URL . '/css/select2.min.css', SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-select2-js', SN_WIE_ASSET_URL . '/js/select2.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-validate-js', SN_WIE_ASSET_URL . '/js/jquery.validate.min.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
                wp_enqueue_script('sn-wie-product-js', SN_WIE_ASSET_URL . '/js/product.js', array('jquery'), SN_WIE_PLUGIN_VERSION);
            }
        }

        /**
         * Product page
         * @description Function to show the product page based upon the argument
         */
        public static function product_page() {
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
            $product_category_list = get_terms( 'product_cat', ['hide_empty' => false] );
            $mapping_profile_list = self::get_mapping_profile_list();
            $args['page_name'] = 'export';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['mapping_profile_list'] = $mapping_profile_list;
            $args['product_category_list'] = $product_category_list;
            $args['field_separator'] = self::get_option( 'field_separator', true );
            wc_get_template( 'export.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
        }

        /**
         * Import page
         * @description Function to show the import page
         */
        private static function import_page() {
            $mapping_profile_list = self::get_mapping_profile_list();
            $args['page_name'] = 'import';
            $args['mapping_profile_count'] = count(self::get_mapping_profile_list());
            $args['schedule_profile_count'] = count(self::get_schedule_profile_list());
            $args['log_count'] = count(self::get_log_list());
            $args['mapping_profile_list'] = $mapping_profile_list;
            $args['field_separator'] = self::get_option( 'field_separator', true );
            wc_get_template( 'import.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            wc_get_template( 'mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            wc_get_template( 'add-mapping.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            wc_get_template( 'schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            wc_get_template( 'add-schedule.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            wc_get_template( 'log.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            $args['options']['product_import_record_per_request'] = get_option( 'sn_wie_product_import_record_per_request' );
            $args['options']['product_export_record_per_request'] = get_option( 'sn_wie_product_export_record_per_request' );
            $args['options']['import_record_per_request'] = self::get_option( 'import_record_per_request', true );
            $args['options']['export_record_per_request'] = self::get_option( 'export_record_per_request', true );
            wc_get_template( 'setting.php', $args, '', SN_WIE_TEMPLATE_PATH .'/product/' );
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
            $export_product_types = null;
            $export_categories = null;
            $export_attributes = null;
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;

            // Get request variables
            if($fn_status == true) {
                $export_columns = sanitize_text_field( $_POST['export_columns'] );
                $export_product_types = array_map( 'sanitize_text_field', (array) $_POST['export_product_types'] );
                $export_categories = array_map( 'sanitize_text_field', (array) $_POST['export_categories'] );
                $export_attributes = intval( $_POST['export_attributes'] );
                $export_meta = intval( $_POST['export_meta'] );
                $field_separator = sanitize_text_field( trim( $_POST['field_separator'] ) );
                $download_filename = sanitize_text_field( trim($_POST['download_filename'] ) );
            }
            //----------------------

            // Get config variables
            $export_record_per_request = self::get_option( 'export_record_per_request' );
            $field_separator = $field_separator?$field_separator:self::get_option( 'field_separator' );
            //---------------------

            // Create if upload directory not exists
            if($fn_status == true) {
                if (!file_exists(SN_WIE_UPLOAD_PATH.'/product')) {
                    if(!mkdir(SN_WIE_UPLOAD_PATH.'/product', 0777, true)) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //---------------------------------------

            // Create empty CSV file
            if($fn_status == true) {
                $export_file_name = 'export_manual_'.current_time('timestamp').'.csv';
                if(file_put_contents(SN_WIE_UPLOAD_PATH.'/product/'.$export_file_name, null) === false) {
                    $fn_status = false;
                    $response = ['status' => false, 'code' => 300, 'message' => __('Unable to create file', SN_WIE_SLUG), 'data' => null];
                }
            }
            //----------------------

            // Count total records
            if($fn_status == true) {
                if($export_product_types) {
                    $product_types_to_export = $export_product_types;
                }
                else {
                    $product_types_to_export = ['simple', 'grouped', 'external', 'variable', 'variation'];
                }
                $query_args = array(
                    'status'   => array( 'private', 'publish', 'draft', 'future', 'pending' ),
                    'type'     => $product_types_to_export,
                    'limit'    => $export_record_per_request,
                    'page'     => 1,
                    'orderby'  => array(
                        'ID' => 'ASC',
                    ),
                    'return'   => 'objects',
                    'paginate' => true,
                );
                $products = wc_get_products( $query_args );
                $response = ['status' => true, 'code' => 104, 'message' => 'Success', 'data' => ['export_file_name' => $export_file_name, 'total_requests' => $products->max_num_pages, 'export_columns' => $export_columns, 'export_product_types' => $export_product_types, 'export_categories' => $export_categories, 'export_attributes' => $export_attributes, 'export_meta' => $export_meta, 'field_separator' => $field_separator, 'download_filename' => $download_filename]];
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
            $export_product_types = null;
            $export_categories = null;
            $export_attributes = null;
            $export_meta = null;
            $field_separator = null;
            $download_filename = null;
            $columns = null;
            $csv_columns = null;
            $product_data_list = [];
            $attribute_list = [];
            $mapping_profile = null;

            // Get config variables
            $export_record_per_request = self::get_option( 'export_record_per_request' );
            //---------------------

            // Get request variables
            if($fn_status == true) {
                $export_columns = sanitize_text_field( $_POST['export_columns'] );
                $export_attributes = intval( $_POST['export_attributes'] );
                $export_meta = intval( $_POST['export_meta'] );
                $export_product_types = array_map( 'sanitize_text_field', (array) $_POST['export_product_types'] );
                $export_categories = array_map( 'sanitize_text_field', (array) $_POST['export_categories'] );
                $field_separator = sanitize_text_field( trim( $_POST['field_separator'] ) );
                $download_filename = sanitize_text_field( trim( $_POST['download_filename'] ) );
                $total_requests = intval($_POST['total_requests']);
                $request_number = intval($_POST['request_number']);
                $file_name = sanitize_text_field( $_POST['file_name'] );
                $csv_columns = array_map( 'sanitize_text_field', (array) $_POST['csv_columns'] );
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

            // Get all attributes
            if($fn_status == true) {
                $attribute_list = wc_get_attribute_taxonomies();
            }
            //-------------------

            // Get all default columns
            if ( $fn_status == true ) {
                $columns = self::get_column_names();
            }
            //------------------------

            // Set Query Parameters
            $post_types = $export_product_types;
            $query_args = ['post_status' => 'any', 'posts_per_page' => $export_record_per_request, 'paged' => $request_number, 'orderby'=> 'ID', 'order' => 'ASC'];
            $query_args['tax_query']['relation'] = 'AND';
            if($post_types) {
                if(in_array('simple', $post_types) || in_array('grouped', $post_types) || in_array('external', $post_types) || in_array('variable', $post_types)) {
                    $query_args['post_type'][] = 'product';
                }
                if(in_array('variation', $post_types)) {
                    $query_args['post_type'][] = 'product_variation';
                    unset($post_types[array_search('variation', $post_types)]);
                }
                if(count($post_types) > 0) {
                    $query_args['tax_query'][] = ['taxonomy' => 'product_type', 'field' => 'slug', 'terms' => $post_types, 'operator' => 'IN'];
                }
            }
            else {
                $query_args['post_type'] = ['product', 'product_variation'];
            }
            if($export_categories) {
                $query_args['tax_query'][] = ['taxonomy' => 'product_cat', 'field' => 'id', 'terms' => $export_categories, 'operator' => 'IN'];
            }
            //---------------------

            $product_results = new WP_Query($query_args);
            while($product_results->have_posts()) {

                $product_attributes = null;
                $product_category_list = [];
                $product_gallery_image_list = [];
                $product_upsell_list = [];
                $product_cross_sell_list = [];
                $product_results->the_post();
                $product = wc_get_product($product_results->post);

                $product_data = self::initialize_columns($csv_columns);
                $product_attributes = $product->get_attributes();
                $default_attributes = $product->get_default_attributes();

                $product_category_ids = $product->get_category_ids();
                foreach($product_category_ids as $category_id) {
                    $product_category_list[] = get_term_by('id', $category_id, 'product_cat')->name;
                }

                $product_gallery_image_ids = $product->get_gallery_image_ids();
                foreach ( $product_gallery_image_ids as $image_id ) {
                    $image_url = wp_get_attachment_url( $image_id );
                    if ( $image_url ) {
                        $product_gallery_image_list[] = $image_url;
                    }
                }

                $product_upsell_ids = $product->get_upsell_ids();
                foreach($product_upsell_ids as $product_id) {
                    $product_upsell_list[] = get_post_meta($product_id, '_sku', true);
                }
                $product_cross_sell_ids = $product->get_cross_sell_ids();
                foreach($product_cross_sell_ids as $product_id) {
                    $product_cross_sell_list[] = get_post_meta($product_id, '_sku', true);
                }

                $product_data[$columns['id']]                   = @$product->get_id();
                $product_data[$columns['type']]                 = @$product->get_type();
                $product_data[$columns['sku']]                  = @$product->get_sku();
                $product_data[$columns['name']]                 = @$product->get_title();
                $product_data[$columns['parent_id']]            = $product->is_type('variation') ? get_post_meta($product->get_parent_id(), '_sku', true) : '';
                $product_data[$columns['published']]            = $product->get_status()=='publish'?1:0;
                $product_data[$columns['featured']]             = @$product->is_featured();
                $product_data[$columns['catalog_visibility']]   = @$product->get_catalog_visibility();
                $product_data[$columns['description']]          = $this->filter_description_field(@$product->get_description());
                $product_data[$columns['short_description']]    = $this->filter_description_field(@$product->get_short_description());
                $product_data[$columns['tax_status']]           = @$product->get_tax_status();
                $product_data[$columns['tax_class']]            = @$product->get_tax_class();
                $product_data[$columns['stock_status']]         = $product->get_stock_status()=='instock'?1:0;
                $product_data[$columns['stock']]                = @$product->get_stock_quantity();
                $product_data[$columns['low_stock_amount']]     = $product->get_low_stock_amount();
                $product_data[$columns['sold_individually']]    = $product->get_sold_individually()?1:0;
                $product_data[$columns['weight']]               = @$product->get_weight();
                $product_data[$columns['length']]               = @$product->get_length();
                $product_data[$columns['width']]                = @$product->get_width();
                $product_data[$columns['height']]               = @$product->get_height();
                $product_data[$columns['reviews_allowed']]      = $product->get_reviews_allowed()==true?1:0;
                $product_data[$columns['purchase_note']]        = @$product->get_purchase_note();
                $product_data[$columns['sale_price']]           = @$product->get_sale_price();
                $product_data[$columns['regular_price']]        = @$product->get_regular_price();
                $product_data[$columns['price']]                = @$product->get_price();
                $product_data[$columns['category_ids']]         = implode('|', $product_category_list);
                $product_data[$columns['tag_ids']]              = $this->get_tags($product->get_id());
                $product_data[$columns['image']]                = $product->is_type('variation') ? wp_get_attachment_url(get_post_meta($product->get_id(), '_thumbnail_id', true)) : wp_get_attachment_url($product->get_image_id());
                $product_data[$columns['gallery_images']]       = implode('|', $product_gallery_image_list);
                $product_data[$columns['download_limit']]       = $product->get_download_limit();
                $product_data[$columns['download_expiry']]      = $product->get_download_expiry();
                $product_data[$columns['upsell_ids']]           = implode('|', $product_upsell_list);
                $product_data[$columns['cross_sell_ids']]       = implode('|', $product_cross_sell_list);
                $product_data[$columns['product_url']]          = get_post_meta($product->get_id(), '_product_url', true);
                $product_data[$columns['button_text']]          = get_post_meta($product->get_id(), '_button_text', true);
                $product_data[$columns['menu_order']]           = @$product->get_menu_order();

                // Export Attributes
                if ( $export_attributes == 1 || $export_columns == 'use_mapping' ) {
                    foreach ( $attribute_list as $attribute ) {
                        $attribute_value = $product->get_attribute($attribute->attribute_name);
                        if($product->is_type('variable') && $attribute_value && $product_attributes['pa_'.$attribute->attribute_name]['variation']==true) {
                            $attribute_value .= '#'.get_term_by('slug', $default_attributes['pa_'.$attribute->attribute_name], 'pa_'.$attribute->attribute_name)->name;
                        }
                        $columns['attribute:'.$attribute->attribute_label] = 'Attribute:'.$attribute->attribute_label;
                        $product_data[$columns['attribute:'.$attribute->attribute_label]] = $attribute_value;
                    }
                }
                //------------------

                // Export Meta
                if ( $export_meta == 1 || $export_columns == 'use_mapping' ) {
                    $meta_list = get_post_meta($product->get_id());
                    if ( count( $meta_list ) > 0 ) {
                        foreach ( $meta_list as $meta_key => $meta_value ) {
                            $columns['meta:'.$meta_key] = 'Meta:'.$meta_key;
                            $product_data[$columns['meta:'.$meta_key]] = $meta_value[0];
                        }
                    }
                }
                //------------

                // Filter data as per mapping
                if ( $export_columns == 'use_mapping') {
                    $mapped_product_data = null;
                    foreach ( $mapping_profile['mapping_columns'] as $data_source => $column_name ) {
                        $mapped_product_data[$column_name] = $product_data[$data_source];
                    }
                    $product_data = $mapped_product_data;
                }
                //---------------------------

                // Create CSV headers columns
                if ( $product_data ) {
                    $product_columns = array_keys( $product_data );
                    foreach ( $product_columns as $column_name ) {
                        if ( !is_array($csv_columns) || !in_array( $column_name, $csv_columns ) ) {
                            $csv_columns[] = $column_name;
                        }
                    }
                }
                //---------------------------

                $product_data_list[] = $product_data;
            }

            // Create if upload directory not exists
            if ( $fn_status == true ) {
                if ( !file_exists(SN_WIE_UPLOAD_PATH.'/product' ) ) {
                    if ( !mkdir(SN_WIE_UPLOAD_PATH.'/product', 0777, true ) ) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 301, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //--------------------------------------


            // Save data to CSV file
            if ( $fn_status == true ) {

                $file = fopen(SN_WIE_UPLOAD_PATH.'/product/'.$file_name, 'a');
                foreach ( $product_data_list as $product ) {
                    fputcsv($file, $product, $field_separator);
                }
                fclose( $file );

                // Add CSV headers in the last request
                if ( $request_number == $total_requests ) {
                    $file_content = file_get_contents( SN_WIE_UPLOAD_PATH.'/product/'.$file_name );
                    $file_content = implode($field_separator, $csv_columns)."\n".$file_content;
                    file_put_contents( SN_WIE_UPLOAD_PATH.'/product/'.$file_name, $file_content );
                }
                //------------------------------------

                $response = ['status' => true, 'code' => 300, 'message' => null, 'data' => ['file_name' => $file_name, 'file_url' => SN_WIE_UPLOAD_URL.'/product', 'total_requests' => $total_requests, 'request_number' => $request_number+1, 'product_export_count' => count($product_data_list), 'csv_columns' => $csv_columns]];
            }
            //----------------------

            wp_send_json( $response );
        }

        /**
         * Upload CSV file
         * @description Function to upload the CSV file for import process
         * @return string/json
         */
        public function upload_csv_file() {
            $fn_status = true;
            $response = [];
            $import_record_per_request = null;
            $file_name = null;
            $file_path = null;
            $import_data_list = null;
            $total_requests = null;
            $upload_type = null;
            $upload_file = null;
            $upload_url = null;
            $import_columns = null;
            $product_search_type = null;
            $product_operation = null;
            $field_separator = null;

            // Get request variables
            if($fn_status == true) {
                $upload_type = sanitize_text_field( $_POST['upload_type'] );
                $upload_file = $_FILES['upload_file'];
                $upload_url = sanitize_text_field( $_POST['upload_url'] );
                $import_columns = sanitize_text_field( $_POST['import_columns'] );
                $product_search_type = sanitize_text_field( $_POST['product_search_type'] );
                $product_operation = sanitize_text_field( $_POST['product_operation'] );
                $field_separator = sanitize_text_field( trim( $_POST['field_separator'] ) );
            }
            //----------------------

            // Get config variables
            $import_record_per_request = self::get_option('import_record_per_request');
            $field_separator = $field_separator?$field_separator:self::get_option( 'field_separator' );
            //---------------------

            // Check if temp file available or not
            if($fn_status == true) {
                if($upload_type == 'file_upload') {
                    if(!$upload_file) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 101, 'message' => __('Please select a file to upload', SN_WIE_SLUG), 'data' => null];
                    }
                }
                elseif($upload_type == 'from_url') {
                    if(!$upload_url) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 101, 'message' => __('Please provide a file URL to upload', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //------------------------------------

            // Create if upload directory not exists
            if($fn_status == true) {
                if (!file_exists(SN_WIE_UPLOAD_PATH.'/product')) {
                    if(!mkdir(SN_WIE_UPLOAD_PATH.'/product', 0777, true)) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 102, 'message' => __('Unable to create upload directory', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //--------------------------------------

            // Upload CSV file
            if( $fn_status == true ) {
                if( $upload_type == 'file_upload' ) {
                    $upload_file_extension = pathinfo($upload_file['name'], PATHINFO_EXTENSION);
                    $file_name = 'import_manual_'.current_time('timestamp').'.'.$upload_file_extension;
                    $file_path = SN_WIE_UPLOAD_PATH.'/product/'.$file_name;
                    if(!move_uploaded_file($upload_file['tmp_name'], $file_path)) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 103, 'message' => __('Unable to upload file', SN_WIE_SLUG), 'data' => null];
                    }
                }
                elseif( $upload_type == 'from_url' ) {
                    $upload_file_extension = pathinfo($upload_url, PATHINFO_EXTENSION);
                    $file_name = 'import_manual_'.current_time( 'timestamp' ).'.'.$upload_file_extension;
                    $file_path = SN_WIE_UPLOAD_PATH.'/product/'.$file_name;
                    if ( !file_put_contents( $file_path, file_get_contents( $upload_url )) ) {
                        $fn_status = false;
                        $response = ['status' => false, 'code' => 103, 'message' => __('Unable to upload file from URL', SN_WIE_SLUG), 'data' => null];
                    }
                }
            }
            //----------------

            // Count total records
            if( $fn_status == true ) {
                $file = fopen($file_path, 'r');
                while ( ( $get_data = fgetcsv( $file, 0, $field_separator ) ) !== false ) {
                    $import_data_list[] = $get_data;
                }
                fclose( $file );
                $total_requests = ceil( ( count( $import_data_list )-1 )/$import_record_per_request );
                $response = ['status' => true, 'code' => 104, 'message' => 'Success', 'data' => ['file_name' => $file_name, 'total_requests' => $total_requests, 'import_columns' => $import_columns, 'product_search_type' => $product_search_type, 'product_operation' => $product_operation, 'field_separator' => $field_separator]];
            }
            //--------------------

            wp_send_json( $response , 200);
        }

        /**
         * Import CSV file
         * @description Function to import CSV file data to database for import process
         */
        public function import_csv_file() {

            set_time_limit(0);
            $fn_status = true;
            $response = [];
            $import_record_per_request = null;
            $file_name = null;
            $file_path = null;
            $total_requests = null;
            $request_number = null;
            $import_columns = null;
            $import_data_header = null;
            $import_data_list = null;
            $product_search_type = null;
            $product_operation = null;
            $field_separator = null;
            $mapping_profile = null;
            $product_insert_count = 0;
            $product_update_count = 0;
            $product_skip_count = 0;
            $product_fail_count = 0;
            $product_status = [];

            // Get request variables
            if($fn_status == true) {
                $total_requests = intval( $_POST['total_requests'] );
                $request_number = intval( $_POST['request_number'] );
                $import_columns = sanitize_text_field( $_POST['import_columns'] );
                $file_name = sanitize_text_field( $_POST['file_name'] );
                $product_search_type = sanitize_text_field( $_POST['product_search_type'] );
                $product_operation = sanitize_text_field( $_POST['product_operation'] );
                $field_separator = sanitize_text_field( trim( $_POST['field_separator'] ) );
            }
            //----------------------

            // Get config variables
            $import_record_per_request = self::get_option( 'import_record_per_request' );
            $field_separator = $field_separator?$field_separator:self::get_option( 'field_separator' );
            //---------------------

            // Check request number against total requests
            if( $fn_status == true ) {
                if( $request_number > $total_requests ) {
                    $fn_status = false;
                }
            }
            //--------------------------------------------

            // Validate file name
            if( $fn_status == true ) {
                $file_path = SN_WIE_UPLOAD_PATH.'/product/'.$file_name;
                if ( !file_exists( $file_path ) ) {
                    $fn_status = false;
                    $response = ['status' => false, 'code' => 201, 'message' => __('No file available for import', SN_WIE_SLUG), 'data' => ['file_name' => $file_name, 'total_requests' => $total_requests, 'request_number' => $request_number]];
                }
            }
            //-------------------

            // Get CSV file data
            if( $fn_status == true ) {
                $file = fopen( $file_path, 'r' );
                if( $file ) {
                    $row = 0;
                    while ( ( $row_data = fgetcsv( $file, 0, $field_separator ) ) !== false ) {
                        if($row == 0) {
                            $import_data_header = $row_data;
                        }
                        else {
                            $row_data_new = null;
                            for( $c=0;$c<count( $row_data );$c++ ) {
                                $row_data_new[trim( $import_data_header[$c] )] = trim( $row_data[$c] );
                            }
                            $import_data_list[] = $row_data_new;
                        }
                        $row++;
                    }
                    fclose( $file );
                }
                else {
                    $fn_status = false;
                    $response = ['status' => false, 'code' => 202, 'message' => __('Unable to open uploaded file', SN_WIE_SLUG), 'data' => ['file_name' => $file_name, 'total_requests' => $total_requests, 'request_number' => $request_number]];
                }
            }
            //------------------

            // Get product mapping
            if( $fn_status == true ) {
                if( strpos( $import_columns, 'use_mapping' ) !== false ) {
                    $mapping_list = self::get_mapping_profile_list();
                    $mapping_profile = $mapping_list[str_replace('use_mapping_', '', $import_columns)];
                    $import_columns = 'use_mapping';
                }
            }
            //--------------------

            // Import data
            if( $fn_status == true ) {
                $start_index = $request_number * $import_record_per_request;
                $end_index = $start_index + $import_record_per_request;
                $end_index = ($end_index>count( $import_data_list )?count( $import_data_list ):$end_index);
                for($i=$start_index;$i<$end_index;$i++) {

                    // Filter data as per mapping
                    if($import_columns == 'use_mapping') {
                        $mapped_product_data = [];
                        foreach($mapping_profile['mapping_columns'] as $data_source => $column_name) {
                            if(array_key_exists(trim($column_name), $import_data_list[$i])) {
                                $mapped_product_data[$data_source] = $import_data_list[$i][trim( $column_name )];
                            }
                        }
                        $import_data_list[$i] = $mapped_product_data;
                    }
                    //---------------------------

                    // Update or Insert product
                    if($product_search_type == 'SKU') {
                        $product_id = wc_get_product_id_by_sku( $import_data_list[$i]['SKU'] );
                    }
                    elseif($product_search_type == 'ID') {
                        $product_id = @$import_data_list[$i]['ID'];
                    }
                    else {
                        $product_id = null;
                    }

                    if($product_id) {
                        if($product_operation == 'create_new_update_existing' || $product_operation == 'skip_new_update_existing') {
                            $update_status = $this->update_product( $import_data_list[$i], $product_search_type );
                            $product_status[] = $update_status;
                            if($update_status['status'] == true) {
                                $product_update_count++;
                            }
                            else {
                                $product_fail_count++;
                            }
                        }
                        else {
                            $product_status[] = ['status' => true, 'id' => $product_id, 'sku' => $import_data_list[$i]['SKU'], 'message' => __('Product Skipped', SN_WIE_SLUG)];
                            $product_skip_count++;
                        }
                    }
                    else {
                        if($product_operation == 'create_new_update_existing' || $product_operation == 'create_new_skip_existing') {
                            $insert_status = $this->insert_product( $import_data_list[$i] );
                            $product_status[] = $insert_status;
                            if($insert_status['status'] == true) {
                                $product_insert_count++;
                            }
                            else {
                                $product_fail_count++;
                            }
                        }
                        else {
                            $product_status[] = ['status' => true, 'id' => null, 'sku' => $import_data_list[$i]['SKU'], 'message' => __('Product Skipped', SN_WIE_SLUG)];
                        }
                    }
                    //--------------------------
                }
                $response = ['status' => true, 'code' => 200, 'message' => 'Success', 'data' => ['file_name' => $file_name, 'total_requests' => $total_requests, 'request_number' => $request_number+1, 'product_operation' => $product_operation, 'product_update_count' => $product_update_count, 'product_insert_count' => $product_insert_count, 'product_skip_count' => $product_skip_count, 'product_fail_count' => $product_fail_count, 'product_status' => $product_status]];
            }
            //------------

            wp_send_json( $response, 200 );
        }

        /**
         * Update product
         * @description Function to update the product data
         * @param $product_data
         * @param $product_search_type
         * @return array $return
         */
        private function update_product( $product_data, $product_search_type ) {
            $fn_status = true;
            $return = [];
            $columns = null;

            // Get all default columns
            if ( $fn_status == true ) {
                $columns = self::get_column_names();
            }
            //------------------------

            try {
                $product = null;
                $existing_attributes = null;
                $new_attributes = null;
                $default_attributes = null;
                if($product_search_type == 'SKU') {
                    $product_id = wc_get_product_id_by_sku( $product_data[$columns['sku']] ) ;
                }
                else {
                    $product_id = $product_data[$columns['id']];
                }

                if( !$product_id ) {
                    $fn_status = false;
                }

                if( $fn_status == true ) {
                    $product = wc_get_product( $product_id );
                    if( !$product ) {
                        $fn_status = false;
                    }
                }

                if( $fn_status == true ) {

                    // Set default values
                    if( isset($product_data[$columns['published']]) && trim($product_data[$columns['published']]) == '1' ) {
                        $product_data[$columns['published']] = 'publish';
                    }
                    if( isset($product_data[$columns['stock_status']]) && trim($product_data[$columns['stock_status']]) == '1' ) {
                        $product_data[$columns['stock_status']] = 'instock';
                    }
                    if( isset($product_data[$columns['backorders']]) && trim($product_data[$columns['backorders']]) == '0' ) {
                        $product_data[$columns['backorders']] = 'no';
                    }
                    //-------------------

                    if( isset( $product_data[$columns['parent_id']] ) ) {
                        $product->set_parent_id( wc_get_product_id_by_sku( $product_data[$columns['parent_id']] ) );
                    }

                    if( isset( $product_data[$columns['name']] )) {
                        $product_name = $this->set_parsed_data( $product_data[$columns['name']] );
                        $product->set_name($product_name);
                    }

                    if( isset($product_data[$columns['published']]) ) {
                        if( $product->is_type('variation') ) {
                            $status = $product_data[$columns['published']]=='publish'?'publish':'private';
                            $product->set_status( $status );
                        }
                        else {
                            $status = $product_data[$columns['published']]=='publish'?'publish':'draft';
                            $product->set_status( $status );
                        }
                    }

                    if( isset($product_data[$columns['featured']]) ) {
                        $product->set_featured($product_data[$columns['featured']]==1);
                    }
                    if( isset($product_data[$columns['catalog_visibility']]) ) {
                        $product->set_catalog_visibility($product_data[$columns['catalog_visibility']]);
                    }
                    if( isset($product_data[$columns['description']]) ) {
                        $product->set_description($this->set_description_parsed_data($product_data[$columns['description']]));
                    }
                    if( isset($product_data[$columns['short_description']]) ) {
                        $product->set_short_description($this->set_description_parsed_data($product_data[$columns['short_description']]));
                    }
                    if( isset($product_data[$columns['sku']]) ) {
                        $product->set_sku(sanitize_text_field($product_data[$columns['sku']]));
                    }
                    if( isset($product_data[$columns['price']]) ) {
                        $product->set_price($product_data[$columns['price']]);
                    }
                    if( isset($product_data[$columns['regular_price']]) ) {
                        $product->set_regular_price($product_data[$columns['regular_price']]);
                    }
                    if( isset($product_data[$columns['sale_price']]) ) {
                        $product->set_sale_price($product_data[$columns['sale_price']]);
                    }
                    if( isset($product_data[$columns['date_on_sale_from']]) ) {
                        $product->set_date_on_sale_from($product_data[$columns['date_on_sale_from']]);
                    }
                    if( isset($product_data[$columns['date_on_sale_to']]) ) {
                        $product->set_date_on_sale_to($product_data[$columns['date_on_sale_to']]);
                    }
                    if( isset($product_data[$columns['stock']]) && trim( $product_data[$columns['stock']] ) != '' ) {
                        $product->set_stock_quantity( $product_data[$columns['stock']] );
                        if($product->is_type('variable')) {
                            $product->set_manage_stock( false );
                        }
                        else {
                            $product->set_manage_stock( true );
                        }
                    }
                    if( isset($product_data[$columns['stock_status']]) ) {
                        $product->set_stock_status($product_data[$columns['stock_status']]=='instock'?'instock':'outofstock');
                    }
                    if( isset($product_data[$columns['backorders']]) ) {
                        $product->set_backorders($product_data[$columns['backorders']]);
                    }
                    if( isset($product_data[$columns['low_stock_amount']]) ) {
                        $product->set_low_stock_amount($product_data[$columns['low_stock_amount']]);
                    }
                    if( isset( $product_data[$columns['sold_individually']] ) ) {
                        $product->set_sold_individually($product_data[$columns['sold_individually']] == 1);
                    }
                    if( isset($product_data[$columns['weight']]) ) {
                        $product->set_weight($product_data[$columns['weight']]);
                    }
                    if( isset($product_data[$columns['length']]) ) {
                        $product->set_length($product_data[$columns['length']]);
                    }
                    if( isset($product_data[$columns['width']]) ) {
                        $product->set_width($product_data[$columns['width']]);
                    }
                    if( isset($product_data[$columns['height']]) ) {
                        $product->set_height($product_data[$columns['height']]);
                    }
                    if( isset($product_data[$columns['tag_ids']]) ) {
                        $product->set_tag_ids($this->get_tag_id_by_name($product_data[$columns['tag_ids']]));
                    }
                    if( isset($product_data[$columns['download_limit']]) ) {
                        $product->set_download_limit($product_data[$columns['download_limit']]);
                    }
                    if( isset($product_data[$columns['download_expiry']]) ) {
                        $product->set_download_expiry($product_data[$columns['download_expiry']]);
                    }
                    if( isset($product_data[$columns['upsell_ids']]) ) {
                        $product->set_upsell_ids($this->get_product_ids_by_skus($product_data[$columns['upsell_ids']]));
                    }
                    if( isset($product_data[$columns['cross_sell_ids']]) ) {
                        $product->set_cross_sell_ids($this->get_product_ids_by_skus($product_data[$columns['cross_sell_ids']]));
                    }
                    if( isset($product_data[$columns['product_url']]) ) {
                        $product->update_meta_data('_product_url', $product_data[$columns['product_url']]);
                    }
                    if( isset($product_data[$columns['button_text']]) ) {
                        $product->update_meta_data('_button_text', $product_data[$columns['_button_text']]);
                    }
                    if( isset($product_data[$columns['reviews_allowed']]) ) {
                        $product->set_reviews_allowed($product_data[$columns['reviews_allowed']] == 1);
                    }
                    if( isset($product_data[$columns['purchase_note']]) ) {
                        $product->set_purchase_note( $product_data[$columns['purchase_note']] );
                    }

                    // Update Attributes
                    $existing_attributes = $product->get_attributes();
                    foreach( $product_data as $key => $column_data ) {

                        if( substr( strtolower( $key ), 0, 10 ) == 'attribute:' ) {

                            $attribute_options = explode('#', $column_data);
                            $attributes = $attribute_options[0];
                            if( $attributes != '' ) {

                                if( !$product->is_type('variation') ) {
                                    $attribute_values = explode(',', $attributes);
                                    $attribute_name = 'pa_'.wc_sanitize_taxonomy_name(trim(str_replace('attribute:', '', strtolower($key))));
                                    $attribute_variation = strpos($column_data, '#')!==false?1:0;

                                    if($attribute_options[1]) {
                                        $default_attributes[$attribute_name] = wc_sanitize_taxonomy_name($attribute_options[1]);
                                    }

                                    $attribute = new WC_Product_Attribute();
                                    $attribute->set_id(wc_attribute_taxonomy_id_by_name($attribute_name));
                                    $attribute->set_name($attribute_name);
                                    $attribute->set_options($attribute_values);
                                    $attribute->set_position(0);
                                    $attribute->set_visible(0);
                                    $attribute->set_variation($attribute_variation);
                                    $new_attributes[$attribute_name] = $attribute;
                                }
                                else {
                                    $attribute_name = 'pa_'.wc_sanitize_taxonomy_name(trim(str_replace('attribute:', '', strtolower($key))));
                                    $new_attributes[$attribute_name] = wc_sanitize_taxonomy_name($attributes);
                                }
                            }
                            else {
                                if(!$product->is_type('variation')) {
                                    $attribute_name = 'pa_'.wc_sanitize_taxonomy_name(trim(str_replace('attribute:', '', strtolower($key))));
                                    unset($existing_attributes[$attribute_name]);
                                }
                            }
                        }
                    }
                    if( $new_attributes ) {
                        $new_attributes = array_merge( $existing_attributes, $new_attributes );
                        $product->set_attributes( $new_attributes );
                        if(!$product->is_type( 'variation' )) {
                            $product->set_default_attributes( $default_attributes );
                        }
                    }
                    //------------------

                    // Update Meta Data
                    foreach( $product_data as $key => $column_data ) {
                        if(substr(strtolower($key), 0, 5) == 'meta:') {
                            $meta_name = trim(str_replace('meta:', '', strtolower($key)));
                            if(trim($column_data) != '') {
                                $product->update_meta_data($meta_name, $column_data);
                            }
                            else {
                                $product->delete_meta_data($meta_name);
                            }
                        }
                    }
                    //-----------------

                    // Update Categories
                    if( isset($product_data[$columns['category_ids']]) ) {
                        $product->set_category_ids( $this->get_category_ids_by_names( $product_data[$columns['category_ids']] ) );
                    }
                    //------------------

                    // Set Product Image
                    if ( isset( $product_data['Image'] ) ) {

                        if($product->is_type('variation')) {
                            $product->update_meta_data('_thumbnail_id', $this->get_image_id($product_data['Image']));
                        }
                        else {
                            $product->set_image_id($this->get_image_id($product_data['Image']));
                        }
                    }
                    //-----------------

                    // Set Product Gallery Images
                    if(isset($product_data['Gallery Images'])) {
                        if($product->is_type('variation')) {
                            $image_ids = $this->get_variation_gallery_image_ids($product_data['Gallery Images']);
                            if($image_ids) {
                                $product->update_meta_data('_gallery_images', $image_ids);
                            }
                            else {
                                $product->delete_meta_data('_gallery_images');
                            }
                        }
                        else {
                            $product->set_gallery_image_ids($this->get_gallery_image_ids($product_data['Gallery Images'])); //Set gallery attachment ids. | array $image_ids List of image ids.
                        }
                    }
                    //---------------------------

                    if( $product->save() ) {
                        $return['status'] = true;
                        $return['id'] = $product->get_id();
                        $return['sku'] = @$product->get_sku();
                        $return['message'] = __('Product updated successfully', SN_WIE_SLUG);

                        // After Update Hook
                        do_action( 'sn_wie_product_updated', $product );
                        //------------------

                    }
                    else {
                        $return['status'] = false;
                        $return['id'] = @$product_data[$columns['id']];
                        $return['sku'] = @$product_data[$columns['sku']];
                        $return['message'] = __('Unable to update product', SN_WIE_SLUG);
                    }
                }
            }
            catch( Exception $e ) {
                $return['status'] = false;
                $return['id'] = null;
                $return['sku'] = @$product_data[$columns['sku']];
                $return['message'] = $e->getMessage();
            }
            return( $return );
        }

        /**
         * Insert product
         * @description Function to insert the product data
         * @param $product_data
         * @return array $return
         */
        private function insert_product( $product_data ) {
            $fn_status = true;
            $return = [];
            $columns = null;

            // Get all default columns
            if ( $fn_status == true ) {
                $columns = self::get_column_names();
            }
            //------------------------

            try {
                $product = null;
                $raw_attributes = null;
                $default_attributes = null;

                // Set default values
                if( !isset($product_data[$columns['type']]) || trim($product_data[$columns['type']]) == '' ) {
                    $product_data[$columns['type']] = 'simple';
                }
                if( !isset($product_data[$columns['published']]) || trim($product_data[$columns['published']]) == '' || trim($product_data[$columns['published']]) == '1' ) {
                    $product_data[$columns['published']] = 'publish';
                }
                if( !isset($product_data[$columns['stock_status']]) || trim($product_data[$columns['stock_status']]) == '' || trim($product_data[$columns['stock_status']]) == '1' ) {
                    $product_data[$columns['stock_status']] = 'instock';
                }
                if( !isset($product_data[$columns['backorders']]) || trim($product_data[$columns['backorders']]) == '' || trim($product_data[$columns['backorders']]) == '0' ) {
                    $product_data[$columns['backorders']] = 'no';
                }
                //-------------------

                if(strtolower($product_data[$columns['type']]) == 'simple') {
                    $product = new WC_Product();
                }
                elseif(strtolower($product_data[$columns['type']]) == 'variable') {
                    $product = new WC_Product_Variable();
                }
                elseif(strtolower($product_data[$columns['type']]) == 'variation') {
                    $product = new WC_Product_Variation();
                    $product->set_parent_id( wc_get_product_id_by_sku( $product_data['Parent'] ) );
                }

                if( isset( $product_data[$columns['name']] ) ) {
                    $product_name = $this->set_parsed_data( $product_data[$columns['name']] );
                    $product->set_name( $product_name );
                    if(strtolower($product_data[$columns['type']]) == 'variation') {
                        $product->add_meta_data('_variation_title', $product_name);
                    }
                }
                if( isset( $product_data[$columns['published']] ) ) {
                    if( $product->is_type( 'variation' ) ) {
                        $status = $product_data[$columns['published']]=='publish'?'publish':'private';
                    }
                    else {
                        $status = $product_data[$columns['published']]=='publish'?'publish':'draft';
                    }
                    $product->set_status( $status );
                }
                if( isset( $product_data[$columns['featured']] ) ) {
                    $product->set_featured($product_data[$columns['featured']]==1);
                }
                if(isset($product_data[$columns['catalog_visibility']])) {
                    $product->set_catalog_visibility($product_data[$columns['catalog_visibility']]);
                }
                if(isset($product_data[$columns['description']])) {
                    $product->set_description($this->set_description_parsed_data($product_data[$columns['description']]));
                }
                if(isset($product_data[$columns['short_description']])) {
                    $product->set_short_description($this->set_description_parsed_data($product_data[$columns['short_description']]));
                }
                if(isset($product_data[$columns['sku']])) {
                    $product->set_sku(sanitize_text_field($product_data[$columns['sku']]));
                }
                if(isset($product_data[$columns['price']])) {
                    $product->set_price($product_data[$columns['price']]);
                }
                if(isset($product_data[$columns['regular_price']])) {
                    $product->set_regular_price($product_data[$columns['regular_price']]);
                }
                if(isset($product_data[$columns['sale_price']])) {
                    $product->set_sale_price($product_data[$columns['sale_price']]);
                }
                if(isset($product_data[$columns['date_on_sale_from']])) {
                    $product->set_date_on_sale_from($product_data[$columns['date_on_sale_from']]);
                }
                if(isset($product_data[$columns['date_on_sale_to']])) {
                    $product->set_date_on_sale_to($product_data[$columns['date_on_sale_to']]);
                }
                if( isset($product_data[$columns['stock']]) && trim( $product_data[$columns['stock']] ) != '' ) {
                    $product->set_stock_quantity($product_data[$columns['stock']]);
                    if($product->is_type('variable')) {
                        $product->set_manage_stock(false);
                    }
                    else {
                        $product->set_manage_stock(true);
                    }
                }
                if(isset($product_data[$columns['stock_status']])) {
                    $product->set_stock_status($product_data[$columns['stock_status']]=='instock'?'instock':'outofstock');
                }
                if(isset($product_data[$columns['backorders']])) {
                    $product->set_backorders($product_data[$columns['backorders']]);
                }
                if( isset($product_data[$columns['low_stock_amount']]) ) {
                    $product->set_low_stock_amount($product_data[$columns['low_stock_amount']]);
                }
                if(isset($product_data[$columns['sold_individually']])) {
                    $product->set_sold_individually($product_data[$columns['sold_individually']] == 1);
                }
                if(isset($product_data[$columns['weight']])) {
                    $product->set_weight($product_data[$columns['weight']]);
                }
                if(isset($product_data[$columns['length']])) {
                    $product->set_length($product_data[$columns['length']]);
                }
                if(isset($product_data[$columns['width']])) {
                    $product->set_width($product_data[$columns['width']]);
                }
                if(isset($product_data[$columns['height']])) {
                    $product->set_height($product_data[$columns['height']]);
                }
                if(isset($product_data[$columns['tag_ids']])) {
                    $product->set_tag_ids($this->get_tag_id_by_name($product_data[$columns['tag_ids']]));
                }
                if( isset($product_data[$columns['download_limit']]) ) {
                    $product->set_download_limit($product_data[$columns['download_limit']]);
                }
                if( isset($product_data[$columns['download_expiry']]) ) {
                    $product->set_download_expiry($product_data[$columns['download_expiry']]);
                }
                if(isset($product_data[$columns['upsell_ids']])) {
                    $product->set_upsell_ids($this->get_product_ids_by_skus($product_data[$columns['upsell_ids']]));
                }
                if(isset($product_data[$columns['cross_sell_ids']])) {
                    $product->set_cross_sell_ids($this->get_product_ids_by_skus($product_data[$columns['cross_sell_ids']]));
                }
                if(isset($product_data[$columns['product_url']])) {
                    $product->update_meta_data('_product_url', $product_data[$columns['product_url']]);
                }
                if(isset($product_data[$columns['button_text']])) {
                    $product->update_meta_data('_button_text', $product_data[$columns['_button_text']]);
                }
                if(isset($product_data[$columns['reviews_allowed']])) {
                    $product->set_reviews_allowed($product_data[$columns['reviews_allowed']] == 1);
                }
                if(isset($product_data[$columns['purchase_note']])) {
                    $product->set_purchase_note($product_data[$columns['purchase_note']]);
                }

                // Insert Attributes & Meta
                foreach($product_data as $key => $column_data) {

                    if(substr(strtolower($key), 0, 10) == 'attribute:') {

                        $attribute_options = explode('#', $column_data);
                        $attributes = $attribute_options[0];
                        if($attributes != '') {

                            if(!$product->is_type('variation')) {
                                $attribute_values = explode(',', $attributes);
                                $attribute_name = 'pa_'.wc_sanitize_taxonomy_name(trim(str_replace('attribute:', '', strtolower($key))));
                                $attribute_variation = strpos($column_data, '#')!==false?1:0;

                                if($attribute_options[1]) {
                                    $default_attributes[$attribute_name] = wc_sanitize_taxonomy_name($attribute_options[1]);
                                }

                                $attribute = new WC_Product_Attribute();
                                $attribute->set_id(wc_attribute_taxonomy_id_by_name($attribute_name));      //if passing the attribute name to get the ID
                                $attribute->set_name($attribute_name);                                      //attribute name
                                $attribute->set_options($attribute_values);                                 //attribute value
                                $attribute->set_position(0);                                          //attribute display order
                                $attribute->set_visible(0);                                           //attribute visibility
                                $attribute->set_variation($attribute_variation);                            //to use this attribute as variant or not
                                $raw_attributes[] = $attribute;                                             //storing the attribute in an array
                            }
                            else {
                                //$attribute_name = 'attribute_pa_'.wc_sanitize_taxonomy_name(trim(str_replace('attribute:', '', strtolower($key))));
                                $attribute_name = 'pa_'.wc_sanitize_taxonomy_name(trim(str_replace('attribute:', '', strtolower($key))));
                                $raw_attributes[$attribute_name] = wc_sanitize_taxonomy_name($attributes);
                                //$product->set_attributes([$attribute_name => wc_sanitize_taxonomy_name($attributes)]);
                            }
                        }
                    }
                    elseif(substr(strtolower($key), 0, 5) == 'meta:') {
                        $meta_name = trim(str_replace('meta:', '', strtolower($key)));
                        $product->add_meta_data($meta_name, $column_data);
                    }
                }
                if ( $raw_attributes ) {
                    $product->set_attributes($raw_attributes);
                    if(!$product->is_type('variation')) {
                        $product->set_default_attributes($default_attributes);
                    }
                }
                //-------------------------

                // Set Product Categories
                if(isset($product_data[$columns['category_ids']])) {
                    $product->set_category_ids($this->get_category_ids_by_names($product_data[$columns['category_ids']]));
                }
                //-----------------------

                // Set Product Image
                if ( isset($product_data['Image'] ) ) {

                    if($product->is_type('variation')) {
                        $product->add_meta_data('_thumbnail_id', $this->get_image_id( $product_data['Image'] ));
                    }
                    else {
                        $product->set_image_id($this->get_image_id( $product_data['Image'] ));
                    }
                }
                //-----------------

                // Set Product Gallery Images
                if ( isset( $product_data['Gallery Images' ] )) {
                    if ( $product->is_type('variation') ) {
                        $image_ids = $this->get_variation_gallery_image_ids( $product_data['Gallery Images'] );
                        if ( $image_ids ) {
                            $product->update_meta_data( '_gallery_images', $image_ids );
                        }
                        else {
                            $product->delete_meta_data( '_gallery_images' );
                        }
                    }
                    else {
                        $product->set_gallery_image_ids( $this->get_gallery_image_ids( $product_data['Gallery Images'] ) ); //Set gallery attachment ids. | array $image_ids List of image ids.
                    }
                }
                //---------------------------

                if($product->save())
                {
                    $return['status'] = true;
                    $return['id'] = $product->get_id();
                    $return['sku'] = @$product->get_sku();
                    $return['message'] = __('Product inserted successfully', SN_WIE_SLUG);

                    // After Product Insert Hook
                    do_action( 'sn_wie_product_inserted', $product );
                    //--------------------------
                }
                else {
                    $return['status'] = false;
                    $return['id'] = null;
                    $return['sku'] = @$product_data[$columns['sku']];
                    $return['message'] = __('Unable to insert product', SN_WIE_SLUG);
                }
            }
            catch(Exception $e) {
                $return['status'] = false;
                $return['id'] = null;
                $return['sku'] = @$product_data[$columns['sku']];
                $return['message'] = $e->getMessage();
            }
            return($return);
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
            readfile(SN_WIE_UPLOAD_PATH.'/product/'.$file_name, null);
            exit();
        }

        /**
         * Delete all logs
         * @description Function to delete all logs
         */
        public function delete_all_logs() {

            $files_list = scandir(SN_WIE_UPLOAD_PATH.'/product');

            foreach($files_list as $file) {
                $extension = pathinfo( $file, PATHINFO_EXTENSION );
                if( strtolower( $extension ) == 'csv' ) {
                    $file_path = SN_WIE_UPLOAD_PATH.'/product/'.$file;
                    if ( is_file( $file_path ) ) {
                        unlink( $file_path );
                    }
                }
            }

            // Set message
            SN_WIE_INIT::set_message(__('Logs deleted', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-products&tab=log' ) );
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
                    update_option( 'sn_wie_product_import_record_per_request', $import_record_per_request );
                }

                $export_record_per_request = intval( $_POST['export_record_per_request'] );
                if( $export_record_per_request != 0 ) {
                    update_option( 'sn_wie_product_export_record_per_request', $export_record_per_request );
                }
            }
            //------------------------

            // Set message
            SN_WIE_INIT::set_message(__('Settings updated', SN_WIE_SLUG), 'success');
            //------------

            wp_redirect( admin_url( 'admin.php?page=sn-wie-products&tab=setting' ) );
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
                'id'                 => __( 'ID', SN_WIE_SLUG ),
                'type'               => __( 'Type', SN_WIE_SLUG ),
                'sku'                => __( 'SKU', SN_WIE_SLUG ),
                'name'               => __( 'Name', SN_WIE_SLUG ),
                'published'          => __( 'Published', SN_WIE_SLUG ),
                'featured'           => __( 'Is featured?', SN_WIE_SLUG ),
                'catalog_visibility' => __( 'Visibility in catalog', SN_WIE_SLUG ),
                'short_description'  => __( 'Short description', SN_WIE_SLUG ),
                'description'        => __( 'Description', SN_WIE_SLUG ),
                'date_on_sale_from'  => __( 'Date sale price starts', SN_WIE_SLUG ),
                'date_on_sale_to'    => __( 'Date sale price ends', SN_WIE_SLUG ),
                'tax_status'         => __( 'Tax status', SN_WIE_SLUG ),
                'tax_class'          => __( 'Tax class', SN_WIE_SLUG ),
                'stock_status'       => __( 'In stock?', SN_WIE_SLUG ),
                'stock'              => __( 'Stock', SN_WIE_SLUG ),
                'low_stock_amount'   => __( 'Low stock amount', SN_WIE_SLUG ),
                'backorders'         => __( 'Backorders allowed?', SN_WIE_SLUG ),
                'sold_individually'  => __( 'Sold individually?', SN_WIE_SLUG ),
                'weight'             => sprintf( __( 'Weight (%s)', SN_WIE_SLUG ), get_option( 'woocommerce_weight_unit' ) ),
                'length'             => sprintf( __( 'Length (%s)', SN_WIE_SLUG ), get_option( 'woocommerce_dimension_unit' ) ),
                'width'              => sprintf( __( 'Width (%s)', SN_WIE_SLUG ), get_option( 'woocommerce_dimension_unit' ) ),
                'height'             => sprintf( __( 'Height (%s)', SN_WIE_SLUG ), get_option( 'woocommerce_dimension_unit' ) ),
                'reviews_allowed'    => __( 'Allow customer reviews?', SN_WIE_SLUG ),
                'purchase_note'      => __( 'Purchase note', SN_WIE_SLUG ),
                'sale_price'         => __( 'Sale price', SN_WIE_SLUG ),
                'regular_price'      => __( 'Regular price', SN_WIE_SLUG ),
                'price'              => __( 'Price', SN_WIE_SLUG ),
                'category_ids'       => __( 'Categories', SN_WIE_SLUG ),
                'tag_ids'            => __( 'Tags', SN_WIE_SLUG ),
                'shipping_class_id'  => __( 'Shipping class', SN_WIE_SLUG ),
                'image'              => __( 'Image', SN_WIE_SLUG ),
                'gallery_images'     => __( 'Gallery Images', SN_WIE_SLUG ),
                'download_limit'     => __( 'Download limit', SN_WIE_SLUG ),
                'download_expiry'    => __( 'Download expiry days', SN_WIE_SLUG ),
                'parent_id'          => __( 'Parent', SN_WIE_SLUG ),
                'grouped_products'   => __( 'Grouped products', SN_WIE_SLUG ),
                'upsell_ids'         => __( 'Upsells', SN_WIE_SLUG ),
                'cross_sell_ids'     => __( 'Cross-sells', SN_WIE_SLUG ),
                'product_url'        => __( 'External URL', SN_WIE_SLUG ),
                'button_text'        => __( 'Button text', SN_WIE_SLUG ),
                'menu_order'         => __( 'Position', SN_WIE_SLUG )
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
                $value = get_option('sn_wie_product_'.$key);
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
         * Get tags
         * @description Function to return comma separated product tags string
         * @param $product_id
         * @return string
         */
        private function get_tags( $product_id ) {
            $tag_list = [];
            $terms = get_the_terms( $product_id, 'product_tag' );
            if ( $terms ) {
                if ( count( $terms ) > 0 ) {
                    foreach($terms as $term) {
                        $tag_list[] = trim($term->name);
                    }
                }
            }
            return(implode(',', $tag_list));
        }

        /**
         * Get product ids by skus
         * @description Function to return product id array from skus
         * @param $product_skus
         * @return array
         */
        private function get_product_ids_by_skus( $product_skus ) {
            $product_ids = null;
            if( trim( $product_skus ) ) {
                $product_skus = explode( '|', $product_skus );
                foreach($product_skus as $product) {
                    $product_id = wc_get_product_id_by_sku( $product );
                    if( $product_id ) {
                        $product_ids[] = $product_id;
                    }
                }
            }
            return( $product_ids );
        }

        /**
         * Get tag id by name
         * @description Function to return product tag id array from name
         * @param $product_tags
         * @return array
         */
        private function get_tag_id_by_name( $product_tags ) {
            $product_tag_ids = [];
            $product_tags = explode( '|', $product_tags );
            foreach( $product_tags as $tag ) {
                $tag_id = get_term_by( 'name', trim( $tag ), 'product_tag' )->term_id;
                if( $tag_id ) {
                    $product_tag_ids[] = $tag_id;
                }
                else {
                    $term_detail = wp_insert_term( trim( $tag ), 'product_tag' );
                    if( !is_wp_error($term_detail) ) {
                        $product_tag_ids[] = $term_detail['term_id'];
                    }
                }
            }
            return( $product_tag_ids );
        }

        /**
         * Get category ids by names
         * @description Function to return product category id array from name
         * @param $product_categories
         * @return int
         */
        private function get_category_ids_by_names( $product_categories ) {
            $product_category_ids = [];
            $product_categories = explode( '|', $product_categories );
            foreach( $product_categories as $category ) {
                $category_id = get_term_by( 'name', $category, 'product_cat' )->term_id;
                if($category_id) {
                    $product_category_ids[] = $category_id;
                }
                else {
                    $term_detail = wp_insert_term( trim( $category ), 'product_cat' );
                    if( !is_wp_error($term_detail) ) {
                        $product_category_ids[] = $term_detail['term_id'];
                    }
                }
            }
            return( $product_category_ids );
        }

        /**
         * Get image id
         * @description Function to return image id from url
         * @param $url
         * @return int
         */
        private function get_image_id( $url ) {

            return( $this->upload_image( $url ) );
        }

        /**
         * Get gallery image id
         * @description Function to return image id from url
         * @param $url
         * @return int
         */
        private function get_gallery_image_ids($image_names) {
            $image_ids = [];

            $image_names = explode('|', $image_names);
            foreach($image_names as $image) {
                $image_ids[] = $this->upload_image($image);
            }
            return($image_ids);
        }

        /**
         * Get variation gallery image id
         * @description Function to return image id from url
         * @param $url
         * @return int
         */
        private function get_variation_gallery_image_ids($image_names) {
            global $wpdb;
            $image_ids = [];

            $image_names = explode('|', $image_names);
            foreach($image_names as $image) {
                $image_id = $this->upload_image($image);
                if($image_id) {
                    $image_ids[] = $image_id;
                }
            }
            return(implode(',', $image_ids));
        }

        /**
         * Upload image
         * @description Function to upload image from url and return id
         * @param $url
         * @return int
         */
        private function upload_image( $url ) {
            global $wpdb;
            $thumb_id = 0;
            $url = esc_url( $url );

            if ( $url ) {

                $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$url'";
                $row = $wpdb->get_row( $query );
                if ( isset( $row->ID ) ) {
                    $thumb_id = $row->ID;
                    return( $thumb_id );
                }
                else {

                    $tmp = download_url( $url );
                    preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);

                    $file_array['name'] = basename( $matches[0] );
                    $file_array['tmp_name'] = $tmp;

                    if ( is_wp_error( $tmp ) ) {
                        @unlink( $file_array['tmp_name'] );
                        $file_array['tmp_name'] = '';
                        return( false );
                    }
                    else {
                        $thumb_id = media_handle_sideload( $file_array, 0 );
                        if ( $thumb_id ){
                            return( $thumb_id );
                        }
                    }
                }
            }
            return( $thumb_id );
        }

        /**
         * Set parsed data
         * @description Function to parse the data
         * @param $value
         * @return mixed
         */
        private function set_parsed_data( $value ) {

            $use_mb = function_exists( 'mb_convert_encoding' );

            if ( $use_mb ) {
                $encoding = mb_detect_encoding( $value, mb_detect_order(), true );
                if ( $encoding ) {
                    $value = mb_convert_encoding( $value, 'UTF-8', $encoding );
                } else {
                    $value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
                }
            } else {
                $value = wp_check_invalid_utf8( $value, true );
            }
            return( $value );
        }

        /**
         * Set description parsed data
         * @description Function to parse the data
         * @param $value
         * @return mixed
         */
        private function set_description_parsed_data( $value ) {
            $parts = explode( "\\\\n", $value );
            foreach ( $parts as $key => $part ) {
                $parts[ $key ] = str_replace( '\n', "\n", $part );
            }

            return implode( '\\\n', $parts );
        }

        /**
         * Filter description field
         * @description Function to filter description field
         * @param $description
         * @return string
         */
        private function filter_description_field( $description ) {
            $description = str_replace( '\n', "\\\\n", $description );
            $description = str_replace( "\n", '\n', $description );
            return( $description );
        }

        /**
         * Get mapping profile list
         * @description Function to return mapping profiles
         * @return array
         */
        public static function get_mapping_profile_list() {
            $mapping_profile_list = [];

            $mapping_list = get_option( 'sn_wie_product_mapping_profile_list' );
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
                    if( $key == 'sn_wie_product_export_schedule' ) {
                        $schedule_detail = array_values($schedule);
                        $schedule_detail[0]['args']['schedule_type'] = 'export';
                        $schedule_detail[0]['args']['next_run_time'] = $time;
                        $schedule_detail[0]['args']['recurring_interval'] = $schedule_list[$schedule_detail[0]['args']['schedule_interval']]['display'];
                        $schedule_profile_list[] = $schedule_detail[0]['args'];
                    }
                    elseif( $key == 'sn_wie_product_import_schedule' ) {
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
            if( file_exists( SN_WIE_UPLOAD_PATH.'/product' ) ) {
                $old_files_list = scandir(SN_WIE_UPLOAD_PATH.'/product');
                foreach($old_files_list as $file) {
                    $extension = pathinfo( $file, PATHINFO_EXTENSION );
                    if( strtolower( $extension ) == 'csv' ) {
                        $file_path = SN_WIE_UPLOAD_PATH.'/product/'.$file;
                        $file_url = admin_url( 'admin-post.php?action=sn_wie_download_product_export_file&file_name='.$file );
                        $file_list[filemtime($file_path)] = ['name' => $file, 'path' => $file_path, 'url' => $file_url];
                    }
                }
                ksort( $file_list );
            }
            return( $file_list );
        }
    }
}

$sn_wie_product = new SN_WIE_PRODUCT();
