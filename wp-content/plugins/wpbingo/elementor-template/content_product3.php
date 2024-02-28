<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop, $post;

if ( ! $product || ! $product->is_visible() ) {
	return;
}
?>
<div class="products-entry clearfix product-wapper">
<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
	<div class="products-thumb">
		<div class="product-group-content">
			<div class="products-content">
				<div class="box-title">
					<h3 class="product-title"><a href="<?php esc_url(the_permalink()); ?>"><?php esc_html(the_title()); ?></a></h3>
					<div class="line"></div>
					<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
				</div>
				<?php woocommerce_template_loop_rating(); ?>
			</div>
		</div>
	</div>
	
</div>