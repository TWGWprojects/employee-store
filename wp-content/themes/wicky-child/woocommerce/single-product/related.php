<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.9.0
 */
if( wicky_get_config( 'product-related' ) == '1' ) :
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	global $product, $woocommerce_loop;
	if ( empty( $product ) || ! $product->exists() ) {
		return;
	}
	$related =  wc_get_related_products( $product->get_id() );
	if ( sizeof( $related ) === 0 ) return;
	$args = apply_filters( 'woocommerce_related_products_args', array(
		'post_type'            => 'product',
		'ignore_sticky_posts'  => 1,
		'no_found_rows'        => 1,
		'posts_per_page'       => (int)wicky_get_config( 'product-related-count' ),
		'orderby'              => $orderby,
		'post__in'             => $related,
		'post__not_in'         => array( $product->get_id() )
	) );
	$products = new WP_Query( $args );
	$woocommerce_loop['columns'] = 1;
	if ( $products->have_posts() ) : ?>
		<div class="related">
			<div class="title-block"><h2><?php esc_html_e( 'Related Products', 'wicky' ); ?></h2></div>
			<div class="content-product-list">
				<div class="products-list grid slick-carousel" data-nav="true" data-columns4="1" data-columns3="2" data-columns2="2" data-columns1="<?php echo esc_attr((int)wicky_get_config( 'product-related-cols',3 )); ?>" data-columns="<?php echo esc_attr((int)wicky_get_config( 'product-related-cols',3 )); ?>">
					<?php while ( $products->have_posts() ) : $products->the_post(); ?>
					<div class="products-entry clearfix product-wapper">
					<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
						<div class="products-thumb">
							<?php
								/**
								 * woocommerce_before_shop_loop_item_title hook
								 *
								 * @hooked woocommerce_show_product_loop_sale_flash - 10
								 * @hooked woocommerce_template_loop_product_thumbnail - 10
								 */
								do_action( 'woocommerce_before_shop_loop_item_title' );
							?>
							<div class='product-button'>
								<?php do_action('woocommerce_after_shop_loop_item'); ?>
							</div>
						</div>
						<div class="products-content"> 
							<?php


							$product_attributes = $product->get_attributes(); 
							$product_sku = $product->get_sku(); 
							$pro_id = $product->get_id();
				

							if(!empty($product_attributes)){
				
								$brands_name = array();
								$pa_varietal_name = array();
								$pa_tier_description_name = array();
								$pa_size_name = array();
								$pa_pack_description_name = array();
								$pa_mastercode_name = array();
				
								
								// Brand name list
								$brand_ids = $product_attributes['pa_brand']['options'];
								if(!empty($brand_ids)){
									foreach($brand_ids as $brand_id){
										$brands_name[] = get_term( $brand_id )->name;
									}
									$brand_name_str =  implode(', ', $brands_name);
								}
				
								// Varietal flavor
								$pa_varietal_ids = $product_attributes['pa_varietal-flavor']['options'];
								if(!empty($pa_varietal_ids)){
									foreach($pa_varietal_ids as $pa_varietal_id){
										$pa_varietal_name[] = get_term( $pa_varietal_id )->name;
									}
									$pa_varietal_name_str =  implode(', ', $pa_varietal_name);
								}
				
								// Tier Description
								$pa_tier_description_ids = $product_attributes['pa_tier-description']['options'];
								if(!empty($pa_tier_description_ids)){
									foreach($pa_tier_description_ids as $pa_tier_description_id){
										$pa_tier_description_name[] = get_term( $pa_tier_description_id )->name;
									}
									$pa_tier_description_name_str =  implode(', ', $pa_tier_description_name);
								}
				
								// Sizes
								$pa_size_ids = $product_attributes['pa_size']['options'];
								if(!empty($pa_size_ids)){
									foreach($pa_size_ids as $pa_size_id){
										$pa_size_name[] = get_term( $pa_size_id )->name;
									}
									$pa_size_name_str =  implode(', ', $pa_size_name);
								}
				
								// Pack description
								$pa_pack_description_ids = $product_attributes['pa_pack-description']['options'];
								if(!empty($pa_pack_description_ids)){
									foreach($pa_pack_description_ids as $pa_pack_description_id){
										$pa_pack_description_name[] = get_term( $pa_pack_description_id )->name;
									}
									$pa_pack_description_name_str =  implode(', ', $pa_pack_description_name);
								}
				
								// Master code
								$pa_mastercode_ids = $product_attributes['pa_master-code']['options'];
								if(!empty($pa_mastercode_ids)){
									foreach($pa_mastercode_ids as $pa_mastercode_id){
										$pa_mastercode_name[] = get_term( $pa_mastercode_id )->name;
									}
									$pa_mastercode_name_str =  implode(', ', $pa_mastercode_name);
								}

								// SSC code
								$SCCDesc = '';
								$sccdesc = get_post_meta($pro_id,'sccdesc',true);
								
							?>
								<h3 class="product-title"><a href="<?php esc_url(the_permalink()); ?>"><?php echo esc_html($brand_name_str.' '.$pa_varietal_name_str.' '.$pa_tier_description_name_str.' '.$pa_size_name_str.' '.$pa_pack_description_name_str.' '.$product_sku.' '.$sccdesc); ?></a></h3>
							<?php
							}
							?>
							
							<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
						</div>
					</div>
					<?php endwhile; // end of the loop. ?>
				</div>
			</div>	
		</div>
	<?php endif;
	wp_reset_postdata();
endif;