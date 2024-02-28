<?php
/**
 * @package Delete Woo Product
 * @version 1.0.0
 */
/*
Plugin Name: Delete Woo Product
Plugin URI: 
Description: Delete Woo Product
Author: Shishir
Version: 1.0.0
Author URI: 
*/

function delete_woo_product() {

    $posts = get_posts(
        array(
            'post_type' => 'product',
            'posts_per_page' => 500,
            'orderby' => 'date',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key'  => '_stock',
                    'value' => '1',
                    'compare' => '<'
                )
            )
        )
    );
    
    foreach($posts as $post){
        $id = $post->ID;
        $product = wc_get_product( $id );

        // If we're forcing, then delete permanently.
        if ( $product->is_type( 'variable' ) ) {
            foreach ( $product->get_children() as $child_id ) {
                $child = wc_get_product( $child_id );
                if ( ! empty( $child ) ) {
                    $child->delete( true );
                }
            }
        } else {
            // For other product types, if the product has children, remove the relationship.
            foreach ( $product->get_children() as $child_id ) {
                $child = wc_get_product( $child_id );
                if ( ! empty( $child ) ) {
                    $child->set_parent_id( 0 );
                    $child->save();
                }
            }
        }

        $product->delete( true );
        $result = ! ( $product->get_id() > 0 );
    }

}

add_action( 'wp_ajax_delete_all_product', 'delete_woo_product' );
function list_all_delete_product(){
    $posts = get_posts(
        array(
            'post_type' => 'product',
            'posts_per_page' => 500,
            'orderby' => 'date',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key'  => '_stock',
                    'value' => '1',
                    'compare' => '<'
                )
            )
        )
    );
    ?>
    <a href="<?=admin_url('admin-ajax.php?action=delete_all_product')?>"><button class="button-primary button ">Delete all products</button></a>
    <table class="wp-list-table widefat fixed striped users">   
    <tr>
        <th>SKU</th>
        <th>Stock</th>
        <th>Stocking Type</th>
    </tr>
    <?php
    foreach($posts as $post){
        $product = wc_get_product( $post->ID );
        ?>
        <tr>
            <td><?=$product->get_sku()?></td>
            <td><?=$product->get_stock_quantity()?></td>
            <td><?=get_post_meta($post->ID, 'stocking_type', true)?></td>
        </tr>
    <?php
    }
    echo "</table>";
}

add_action('admin_menu', 'add_delete_product_menu');

function add_delete_product_menu(){
    add_submenu_page( 'woocommerce', 'Delete Products', 'Delete Products',
    'manage_options', 'delete-product','list_all_delete_product');
}