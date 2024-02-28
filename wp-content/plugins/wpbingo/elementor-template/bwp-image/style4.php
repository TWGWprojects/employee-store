<div class="bwp-widget-banner <?php echo esc_html( $layout ); ?>">
	<?php  if($image): ?>	
	<div class="bg-banner">		
		<div class="banner-wrapper banners">
			<div class="bwp-image">
				<?php  if($link): ?>
				<a href="<?php echo esc_url($link);?>">
						<img src="<?php echo esc_url($image); ?>" alt=""></a>
				<?php endif; ?>
			</div>
			<div class="banner-wrapper-infor">
				<?php if( $title1) : ?>
				<h3 class="title-banner"><?php echo esc_html( $title1 ); ?></h3>
				<?php endif; ?>
				<?php if( $subtitle) : ?>
					<div class="bwp-image-subtitle">					
						<?php echo ($subtitle); ?>		
					</div>	
				<?php endif;?>
				<?php if( $description) : ?>
				<h3 class="description"><?php echo esc_html( $description ); ?></h3>
				<?php endif; ?>
				<?php if( $label) : ?>
					<div class="button-banner">
						<a href="<?php echo esc_url($link);?>">
							<?php echo esc_html( $label ); ?>
						</a>
					</div>
				<?php endif;?>
			</div>
		</div>
	</div>
	<?php endif;?>
</div>
