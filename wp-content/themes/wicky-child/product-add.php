<?php
require_once("../../../wp-load.php");

require_once(__DIR__.'\soap_api.php');
require_once(__DIR__.'\usps.php');


$products = _add_update_product(); 



if(!empty($products)){

    $taxo = array('Brand'=>'Brands', 'Master Code'=>'Master Codes', 'Pack Description' => 'Pack Description','Varietal Flavor Description' => 'Varietal Flavor Description', 'Tier Description' => 'Tier Description', 'Size Description' => 'Size Description');
    $attributes = [
        'pa_brand'=>'brand',
        'pa_master-code'=>'master_code',
        'pa_pack-description' => 'pack_description',
        'pa_package-format' => 'package_format',
        'pa_size' => 'size_description',
        'pa_tier-description' => 'tier_description',
        'pa_varietal-flavor' => 'varietal_flavor_description'
    ];
    $raw_attributes = array();

   
    if(!empty($products)){
        foreach($products as $product){
            if($product['stockingtype'] == 'M' || $product['stockingtype'] == 'P'){
                
                $post_id = wc_get_product_id_by_sku($product['SKU']);
                if($post_id==0){
                    $post_id = wp_insert_post(
                        array(
                            'post_title' => $product['name'],
                            'post_type' => 'product',
                            'post_status' => 'publish'
                        )
                    );
                    $pro = new WC_Product( $post_id );
                    $pro->set_image_id(61923);
                    $pro->set_sku($product['SKU']);
                }else{
                    $pro = new WC_Product( $post_id );
                }
                $pro->set_description($product['description']);
                $pro->set_short_description($product['description']);
                $pro->add_meta_data('appellation', $product['appellation'], true);
                $pro->add_meta_data('stocking_type', $product['stockingtype'], true);
                $pro->add_meta_data('vintage_description', $product['vintage'],  true);
                $pro->add_meta_data('unit_of_measure', $product['unit_of_measure'],  true);
                $pro->add_meta_data('export', $product['export'],  true);
                $pro->add_meta_data('sccdesc', $product['sccdesc'],  true);
                
                $pro->set_image_id(61923);
                $size = $product['size_description'];
                if (stripos($size, 'ML') !== false) {
                    $size_int = (int) str_replace('ML','',$size);
                    $price = number_format( $size_int*(1.12/750) ,2);
                }else{
                    $size_int = (int) str_replace('L','',$size);
                    $price = number_format($size_int*1000*(1.12/750),2);
                }
                //file_put_contents('logs.txt', $size.$size_int.'/'.$price.PHP_EOL , FILE_APPEND | LOCK_EX);
                $pro->set_regular_price($price);
                $pro->set_sale_price($price);
                foreach($taxo as $k => $v){
                    $slug = strtolower(str_replace(' ','_',$k));
                    $term = term_exists($product[$slug], $slug);
                    if ( $term !== 0 && $term !== null ) {
                        $term_id = (int)$term['term_id'];
                    }else{
                        $term = wp_insert_term($product[$slug], $slug);
                        $term_id = $term['term_id'];
                    }
                    wp_set_object_terms($post_id, $term_id, $slug);
                }
                foreach($attributes as $k => $v){
                    $option = $product[$v];
                    /*$term = term_exists([$product[$v]], $k);
                    if ( $term !== 0 && $term !== null ) {
                        $option = $product[$v];
                    }else{
                        $term = wp_insert_term($product[$v], $k);
                        $option = $product[$v];
                    }*/
                    $attribute = new WC_Product_Attribute();
                    $attribute->set_id(wc_attribute_taxonomy_id_by_name($k)); //if passing the attribute name to get the ID
                    $attribute->set_name($k); //attribute name
                    $attribute->set_options([$option]); // attribute value
                    //$attribute->set_position(1); //attribute display order
                    $attribute->set_visible(1); //attribute visiblity
                    //$attribute->set_variation(0);//to use this attribute as varint or not

                    $raw_attributes[] = $attribute; //<--- storing the attribute in an array
                }
                $pro->set_attributes($raw_attributes);
                $pro->save();
            }
        }
    }
}
    die;

