<div class="bwp-widget-banner <?php echo esc_html( $layout ); ?>">
	<?php if( $title1) : ?>
		<h3 class="title-banner"><?php echo esc_html( $title1 ); ?></h3>
	<?php endif;?>
	<?php  if($image): ?>	
	<div class="bg-banner">
		<div class="banner-wrapper banners">
			<?php  if($link): ?>
			<div class="bwp-image">
				<a href="<?php echo esc_url($link);?>"><img src="<?php echo esc_url($image); ?>" alt=""></a>
			</div>		
			<?php endif;?>
			<?php if(isset($category) && $category) : ?>
				<?php $term = get_term_by('slug', $category, 'product_cat'); ?>
				<div class="item-content">
					<h3 class="item-name">
						<a href="<?php echo get_term_link( $term->term_id, 'product_cat' ); ?>">
							<?php echo esc_html($term->name); ?>
						</a>
					</h3>
					<?php if( $description) : ?>
						<div class="bwp-image-description">
							<?php if(isset($description) && $description){?>						
								<?php echo ($description); ?>							
							<?php } ?>
						</div>
					<?php endif; ?>
					<?php if(isset($show_count) && $show_count) : ?>
					<div class="item-count">
						<?php if($term->count == 1){?>
							<?php echo esc_attr($term->count) .'<span>'. esc_html__(' Item', 'wpbingo').'</span>'; ?>
						<?php }else{ ?>
							<?php echo esc_attr($term->count) .'<span>'. esc_html__(' Items', 'wpbingo').'</span>'; ?>
						<?php } ?>
					</div>
					<?php endif;?>	
					<?php if($label): ?>
						<div class="bwp-image-button">
							<a href="<?php echo get_term_link( $term->term_id, 'product_cat' ); ?>"><?php echo esc_html( $label ); ?></a>
						</div>
					<?php endif;?>
				</div>
			<?php endif;?>
		</div>
	</div>
	<?php endif;?>
</div>
