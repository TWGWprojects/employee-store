<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop, $post;


?>
<div class="products-entry clearfix product-wapper sfsf">
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