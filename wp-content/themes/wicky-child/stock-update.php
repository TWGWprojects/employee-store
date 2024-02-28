<?php
require_once("../../../wp-load.php");

require_once(__DIR__.'\soap_api.php');
require_once(__DIR__.'\usps.php');

    global $wpdb;
   
    $time = serialize(['start' => time(), 'end' => '']);   
    
    
    
    if(isset($_GET['reset'])){
        
        $recent_posts = wp_get_recent_posts(array(
            'numberposts' => 1, 
            'post_type'   => 'product',// Number of recent posts thumbnails to display
            'post_status' => 'publish' // Show only the published posts
        ));        
        $post_id_recent = $recent_posts[0]['ID'];
        update_option('_last_stock_update_prod', $post_id_recent);
        update_option('_inventory_api_time',$time);
    }
        
   

    if(get_option('_last_stock_update_prod_date')){
        if(get_option('_last_stock_update_prod_date') < date('Y-m-d')){
            
            $recent_posts = wp_get_recent_posts(array(
                'numberposts' => 1, 
                'post_type'   => 'product',// Number of recent posts thumbnails to display
                'post_status' => 'publish' // Show only the published posts
            ));
            $post_id_recent = $recent_posts[0]['ID'];
            update_option('_last_stock_update_prod', $post_id_recent);            
            update_option('_inventory_email_status','');
            update_option('_last_stock_update_prod_date', date('Y-m-d')); 
            update_option('_inventory_api_time',$time);
        }   
    }else{
        update_option('_last_stock_update_prod_date', date('Y-m-d'));
    }
    $last_id = (get_option('_last_stock_update_prod'))? get_option('_last_stock_update_prod') : 0;

    
    $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID <= %d AND post_status = 'publish' AND post_type = 'product' order by ID DESC LIMIT 70", $last_id );
    $results = $wpdb->get_results( $query );
    
   
    // file_put_contents('logs.txt', $last_id.json_encode ($results).PHP_EOL , FILE_APPEND | LOCK_EX);
    if(count($results)>0){
        foreach($results as $prod){
           
        // wc_update_product_stock($prod, $stock);set_catalog_visibility
            update_stock_common($prod->ID);
            $last_id = $prod->ID;
            // file_put_contents('logs.txt', ($last_id).PHP_EOL , FILE_APPEND | LOCK_EX);
        }
    }else{
        $get_time = (get_option('_inventory_api_time')) ? unserialize( get_option('_inventory_api_time') ) : '';
        if(!empty($get_time) && $get_time['end'] != ''){
            $get_time['end'] = time(); 
            update_option('_inventory_api_time',serialize( $get_time ));
        }
        update_option('_last_price_update_prod_date',date("Y-m-d"));
    }
    update_option('_last_stock_update_prod', $last_id);