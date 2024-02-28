<?php if(isset($user_id) && $user_id && isset($access_token) && $access_token ):?>
<div class="bwp-instagram slider <?php echo esc_attr($padding); ?>" 
	data-user_id="<?php echo $user_id; ?>"
	data-rows = "<?php echo esc_attr($item_row); ?>"
	data-access_token="<?php echo $access_token; ?>"
	data-limit="<?php echo esc_attr($limit); ?>"
	data-width="<?php echo esc_attr($width); ?>"
	data-height="<?php echo esc_attr($height); ?>"
	data-text_check_user="<?php echo __( 'Please check User ID, Access token or Networking again', "wpbingo" ); ?>"
	data-text_image_show="<?php echo __( 'No Image To Show', "wpbingo" ); ?>"
	>
 <div class="block">
 	<?php if(isset($title1) && $title1) {?>
	<div class="block-title">
		<div class="instagram-title">
			<?php if($subtitle) { ?>
			<div class="subtitle"><?php echo $subtitle; ?></div>
			<?php } ?> 
			<?php
				echo '<h2>'. esc_html($title1) .'</h2>';
			?>
		</div>
	</div>
	<?php } ?>
	<div class="block_content">
		<div class="instagram">
			<div class="container">
				<div class="row">
					<div class="content_instagram slick-carousel"
						data-columns4="<?php echo esc_attr($columns4); ?>"
						data-columns3="<?php echo esc_attr($columns3); ?>"
						data-columns2="<?php echo esc_attr($columns2); ?>"
						data-columns1="<?php echo esc_attr($columns1); ?>"
						data-columns="<?php echo esc_attr($columns); ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
  <div class="description"><?php echo $description; ?></div>
 </div>
</div>
<?php endif;?>