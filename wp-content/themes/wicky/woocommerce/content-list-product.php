<?php 
global $product, $woocommerce_loop, $post;
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 10 );
remove_action('woocommerce_after_shop_loop_item', 'wicky_quickview', 35 );
add_action('woocommerce_before_shop_loop_item_title', 'wicky_quickview', 35 );
?>
<div class="products-entry clearfix product-wapper">
	<div class="row">
		<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
		<div class="col-sm-2">
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
			</div>
		</div>
		<div class="col-sm-10">
			<div class="products-content">
				<?php woocommerce_template_loop_rating(); ?>
				<h3 class="product-title"><a href="<?php esc_url(the_permalink()); ?>"><?php esc_html(the_title()); ?></a></h3>
				<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
				<div class='product-button'>
					<?php do_action('woocommerce_after_shop_loop_item'); ?>
				</div>
				<?php wicky_add_excerpt_in_product_archives(); ?>
			</div>
		</div>
	</div>
</div>