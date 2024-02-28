<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! $product_attributes ) {
	return;
}

global $product;
$product_details = $product->get_data();
$pro_id = $product->get_id();
$sccdesc = get_post_meta($pro_id,'sccdesc',true);
?>
<table class="woocommerce-product-attributes shop_attributes">
	<?php foreach ( $product_attributes as $product_attribute_key => $product_attribute ) : ?>
		<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--<?php echo esc_attr( $product_attribute_key ); ?>">
			<th class="woocommerce-product-attributes-item__label"><?php echo wp_kses_post( $product_attribute['label'] ); ?></th>
			<td class="woocommerce-product-attributes-item__value"><?php echo wp_kses_post( $product_attribute['value'] ); ?></td>
		</tr>
	<?php endforeach; ?>
	<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--description">
		<th class="woocommerce-product-attributes-item__label"><?php esc_html_e( 'Description', 'woocommerce' );;?></th>
		<td class="woocommerce-product-attributes-item__value"><p><?php echo  $product_details['description'];?></p></td>
	</tr>

	<?php
	if(!empty($sccdesc)){
		?>
		<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--description">
			<th class="woocommerce-product-attributes-item__label"><?php esc_html_e( 'SCC', 'woocommerce' );;?></th>
			<td class="woocommerce-product-attributes-item__value"><p><?php echo  $sccdesc;?></p></td>
		</tr>
		<?php
	}
		
	?>
</table>
