<?php
/**
 * @package Stock update api log
 * @version 1.7.2
 */
/*
Plugin Name: Stock update api log
Plugin URI: 
Description: Used to log stock update data 
Author: Shishir
Version: 1.0.0
Author URI: 
*/


global $stock_log_db_version;
global $wpdb, $table_name;
$stock_log_db_version = '1.0';

$table_name = $wpdb->prefix . 'stock_api_log';

function stock_log_install() {
	global $wpdb, $table_name;
	global $stock_log_db_version;
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id bigint NOT NULL AUTO_INCREMENT,
		sku varchar(55) DEFAULT '' NOT NULL,
		stock bigint NOT NULL,
		last_update datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'stock_log_db_version', $stock_log_db_version );
}
register_activation_hook( __FILE__, 'stock_log_install' );


add_action( 'insert_stock_log', 'log_stock_data', 10, 1 );

function log_stock_data($data){
    global $wpdb, $table_name;
	       
    $query = $wpdb->prepare("SELECT sku FROM $table_name WHERE sku = %s ",  $data['sku']);
    $results = $wpdb->get_row( $query );
    
    if($results){
        $wpdb->update( 
            $table_name, 
            array( 
                'stock' => $data['stock'], 
                'last_update' => date("Y-m-d H:i:s")
            ) ,
            array(
                'sku' => $results->sku
            )
        );
    }else{
        
        $wpdb->insert( 
            $table_name, 
            array( 
                'sku' => $data['sku'],
                'stock' => $data['stock'], 
                'last_update' => date("Y-m-d H:i:s")
            ) 
        );
    }
}

add_action('admin_menu', 'add_inventory_log_menu');

function add_inventory_log_menu(){
    add_submenu_page( 'woocommerce', 'Inventory Log', 'Inventory Log',
    'manage_options', 'inventory-log','inventory_log_report');
}


function inventory_log_report(){    
    global $wpdb, $table_name;
	
    if(isset($_GET['sort']) && $_GET['sort'] != ''){
        $sort = $_GET['sort'];
        $order = ($_GET['sort'] =='asc')? 'desc':'asc';
    }else{
        $sort = 'desc';
        $order = 'asc';
    }       
    $query ="SELECT * FROM $table_name order by stock $sort";
    $results = $wpdb->get_results( $query );
    if(count($results)> 0){
        $get_time = (get_option('_inventory_api_time')) ? unserialize( get_option('_inventory_api_time') ) : '';
        if(!empty($get_time)){
            $date = new DateTime();
            $date->setTimestamp($get_time['start']);
            $date->setTimezone(new \DateTimeZone('America/Los_Angeles'));
            if($get_time['end'] !=''){                
                $edate = new DateTime();
                $edate->setTimestamp($get_time['start']);
                $edate->setTimezone(new \DateTimeZone('America/Los_Angeles'));
                $get_time['end'] = $edate->format('d-m-Y H:i:s') ;
            }
        ?>
        <div> Start : <?=$date->format('d-m-Y H:i:s') ?><br>
        End : <?=$get_time['end'] ?>
        <?php }?>
        <table class="wp-list-table widefat fixed striped users">   
            <tr>
                <th>SKU</th>
                <th><a href="<?=admin_url('admin.php?page=inventory-log&sort='.$order)?>">Stock<a></th>
                <th>Last Update</th>
            </tr>
            <?php foreach($results as $row){
                
            $date->setTimestamp(strtotime($row->last_update));
                ?>
                <tr>
                    <td><?=$row->sku?></td>
                    <td><?=$row->stock?></td>
                    <td><?=$date->format('d-m-Y H:i:s')?></td>
                </tr>
            <?php } ?>
        </table>
        <?php
    }else{
        echo "No Record";
    }
}