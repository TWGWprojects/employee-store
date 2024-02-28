<?php
/**
 * Product loop sale flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/sale-flash.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post, $product;
$wicky_settings = wicky_global_settings();
$product_hot_label = isset($wicky_settings['product-hot-label']) && !empty($wicky_settings['product-hot-label']) ? $wicky_settings['product-hot-label'] : esc_html__('Hot','wicky');
$product_sale_label = wicky_get_product_discount();
$product_sale = wicky_get_config('product-sale',true);
?>
<?php if($product_sale ) : ?>
	<?php if ( $product->is_on_sale() ) : ?>
		<div class='product-lable'>
			<?php echo apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html($product_sale_label) . '</span>', $post, $product ); ?>
		</div>
	<?php endif; ?>
	<?php if(isset($wicky_settings['product-hot']) && $wicky_settings['product-hot']) : ?>
		<?php if ($product->is_featured()) : ?>
			<?php echo apply_filters('woocommerce_featured_flash', '<div class="vgwc-label vgwc-featured hot">' . esc_html($product_hot_label) . '</div>', $post, $product); ?>
		<?php endif; ?>
	<?php endif; ?>	
<?php endif; ?>