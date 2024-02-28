<?php
/*
* Plugin Name: WooCommerce Add Taxonomy to Export
* Plugin URI: https://gist.github.com/helgatheviking/114c8df50cabb7119b3c895b1d854533/
* Description: Add a custom taxonomy to WooCommerce import/export.
* Version: 1.0.1
* Author: Kathy Darling
* Author URI: https://kathyisawesome.com/
*
* Woo: 18716:fbca839929aaddc78797a5b511c14da9
*
* Text Domain: woocommerce-product-bundles
* Domain Path: /languages/
*
* Requires at least: 5.0
* Tested up to: 5.0
*
* WC requires at least: 3.5
* WC tested up to: 3.5.4
*
* Copyright: © 2017-2019 SomewhereWarm SMPC.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
* Add CSV columns for exporting extra data.
*
* @param  array  $columns
* @return array  $columns
*/
global $taxo;
$taxo = array('Brand'=>'Brands', 'Master Code'=>'Master Codes', 'Pack Description' => 'Pack Description','Varietal Flavor Description' => 'Varietal Flavor Description', 'Tier Description' => 'Tier Description', 'Size Description' => 'Size Description');
function kia_add_columns( $columns ) {
    global $taxo;
    foreach($taxo as $k=>$v){
        $slug = strtolower(str_replace(' ','_',$k));
        $columns[ $slug ] = __( $k, 'wineshop' );
    }
	return $columns;
}
add_filter( 'woocommerce_product_export_column_names', 'kia_add_columns' );
add_filter( 'woocommerce_product_export_product_default_columns', 'kia_add_columns' );

function kia_export_brand( $value, $product ) {
    global $taxo, $cur_taxo;
    $terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'brand' ) );
    if ( ! is_wp_error( $terms ) ) {
        $data = array();
        foreach ( (array) $terms as $term ) {
            $data[] = $term->name;
        }
        $value = implode(',', $data );
    }
    return $value;
}

function kia_export_master_code( $value, $product ) {
    global $taxo, $cur_taxo;
    $terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'master_code' ) );
    if ( ! is_wp_error( $terms ) ) {
        $data = array();
        foreach ( (array) $terms as $term ) {
            $data[] = $term->name;
        }
        $value = implode(',', $data );
    }
    return $value;
}

function kia_export_pack_description( $value, $product ) {
    global $taxo, $cur_taxo;
    $terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'pack_description' ) );
    if ( ! is_wp_error( $terms ) ) {
        $data = array();
        foreach ( (array) $terms as $term ) {
            $data[] = $term->name;
        }
        $value = implode(',', $data );
    }
    return $value;
}

function kia_export_varietal_flavor_description( $value, $product ) {
    global $taxo, $cur_taxo;
    $terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'varietal_flavor_description' ) );
    if ( ! is_wp_error( $terms ) ) {
        $data = array();
        foreach ( (array) $terms as $term ) {
            $data[] = $term->name;
        }
        $value = implode(',', $data );
    }
    return $value;
}

function kia_export_tier_description( $value, $product ) {
    global $taxo, $cur_taxo;
    $terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'tier_description' ) );
    if ( ! is_wp_error( $terms ) ) {
        $data = array();
        foreach ( (array) $terms as $term ) {
            $data[] = $term->name;
        }
        $value = implode(',', $data );
    }
    return $value;
}

function kia_export_size_description( $value, $product ) {
    global $taxo, $cur_taxo;
    $terms = get_terms( array( 'object_ids' => $product->get_ID(), 'taxonomy' => 'size_description' ) );
    if ( ! is_wp_error( $terms ) ) {
        $data = array();
        foreach ( (array) $terms as $term ) {
            $data[] = $term->name;
        }
        $value = implode(',', $data );
    }
    return $value;
}

foreach($taxo as $k=>$v){
    $slug = strtolower(str_replace(' ','_',$k));
    
    $exp_func = 'kia_export_'.$slug;
    
    add_filter( 'woocommerce_product_export_product_column_'.$slug, $exp_func, 10, 2 );
}

/**
 * Import
 */
/**
 * Register the 'Custom Column' column in the importer.
 *
 * @param  array  $columns
 * @return array  $columns
 */
function kia_map_columns( $columns ) {
    global $taxo;
    foreach($taxo as $k=>$v){
        $slug = strtolower(str_replace(' ','_',$k));
        $columns[ $slug ] = __( $k, 'wineshop' );
    }
	return $columns;
}
add_filter( 'woocommerce_csv_product_import_mapping_options', 'kia_map_columns' );
/**
 * Add automatic mapping support for custom columns.
 *
 * @param  array  $columns
 * @return array  $columns
 */
function kia_add_columns_to_mapping_screen( $columns ) {global $taxo;
    foreach($taxo as $k=>$v){
        $slug = strtolower(str_replace(' ','_',$k));
        $columns[ __( $k, 'wineshop' ) ] 	= $slug;
        // Always add English mappings.
        $columns[ $k ]	= $slug;
    }
	return $columns;
}
add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'kia_add_columns_to_mapping_screen' );
/**
 * Decode data items and parse JSON IDs.
 *
 * @param  array                    $parsed_data
 * @param  WC_Product_CSV_Importer  $importer
 * @return array
 */
function kia_parse_taxonomy_json( $parsed_data, $importer ) {
    global $taxo;
    foreach($taxo as $k=>$v){
        $slug = strtolower(str_replace(' ','_',$k));
        if ( ! empty( $parsed_data[ $slug ] ) ) {
            $data = explode( ',', $parsed_data[ $slug ] );
            unset( $parsed_data[ $slug ] );
            if ( is_array( $data ) ) {
                $parsed_data[ $slug ] = array();
                foreach ( $data as $term_id ) {
                    $parsed_data[ $slug ][] = $term_id;
                }
            }
        }
    }
	return $parsed_data;
}
add_filter( 'woocommerce_product_importer_parsed_data', 'kia_parse_taxonomy_json', 10, 2 );
/**
 * Set taxonomy.
 *
 * @param  array  $parsed_data
 * @return array
 */
function kia_set_taxonomy( $product, $data ) {
	if ( is_a( $product, 'WC_Product' ) ) {
        global $taxo;
        foreach($taxo as $k=>$v){
            $slug = strtolower(str_replace(' ','_',$k));
            if( ! empty( $data[ $slug ] ) ) {
                wp_set_object_terms( $product->get_id(),  (array) $data[ $slug ], $slug );
            }
        }
	}
	return $product;
}
add_filter( 'woocommerce_product_import_inserted_product_object', 'kia_set_taxonomy', 10, 2 );