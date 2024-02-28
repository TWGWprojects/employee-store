<?php
	$class_col_lg = ($columns == 5) ? '2-4'  : (12/$columns);
	$class_col_md = ($columns1 == 5) ? '2-4'  : (12/$columns1);
	$class_col_sm = ($columns2 == 5) ? '2-4'  : (12/$columns2);
	$class_col_xs = ($columns3 == 5) ? '2-4'  : (12/$columns3); 
	$attributes = 'col-lg-'.$class_col_lg .' col-md-'.$class_col_md .' col-sm-'.$class_col_sm .' col-xs-'.$class_col_xs; 	
?>
<?php if(isset($user_id) && $user_id && isset($access_token) && $access_token ):?>
<div class="bwp-instagram default <?php echo esc_attr($padding); ?>" 
	data-user_id="<?php echo esc_attr($user_id); ?>"
	data-rows = "<?php echo esc_attr($item_row); ?>"
	data-access_token="<?php echo $access_token; ?>"
	data-limit="<?php echo esc_attr($limit); ?>"
	data-width="<?php echo esc_attr($width); ?>"
	data-height="<?php echo esc_attr($height); ?>"
	data-text_check_user="<?php echo __( 'Please check User ID, Access token or Networking again', "wpbingo" ); ?>"
	data-text_image_show="<?php echo __( 'No Image To Show', "wpbingo" ); ?>" >
	<div class="block">
		<?php if(isset($title1) && $title1) {?>
			<div class="instagram-title">
				<?php
					echo '<h2>'. esc_html($title1) .'</h2>';
				?>
			</div>
		<?php } ?>
		<div class="block_content">
			<div class="content_instagram row" data-attributes="<?php echo esc_attr($attributes); ?>">
			</div>
		</div>
	</div>
</div>
<?php endif;?>