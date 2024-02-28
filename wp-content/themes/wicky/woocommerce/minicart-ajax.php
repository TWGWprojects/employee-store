<?php 
if ( !class_exists('Woocommerce') ) { 
	return false;
}
global $woocommerce; ?>
<div id="cart" class="dropdown mini-cart top-cart">
	<a class="dropdown-toggle cart-icon" data-toggle="dropdown" data-hover="dropdown" data-delay="0" href="#" title="<?php esc_attr_e('View your shopping cart', 'wicky'); ?>">
		<i class="icon_cart_alt"></i>
		<span class="mini-cart-items"><span class="items-class"> <?php echo esc_html__('My cart:','wicky') ?> </span><?php echo esc_attr($woocommerce->cart->cart_contents_count); ?> <span class="text-cart-items"><?php echo esc_html__('Items','wicky') ?></span></span>
		<span class="text-price-cart"><?php echo wp_kses($woocommerce->cart->get_cart_total(),'social'); ?></span>
    </a>
	<div class="cart-popup">
		<?php woocommerce_mini_cart(); ?>
	</div>
</div>