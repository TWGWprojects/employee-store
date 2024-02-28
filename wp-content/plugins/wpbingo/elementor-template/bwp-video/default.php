<div class="bwp-widget-video <?php echo esc_attr($class); ?>">
	<?php  if($image): ?>	
	<div class="bg-video">		
		<div class="video-wrapper videos">
			<div class="bwp-image">
				<?php  if($link): ?>
					<a class="bwp-video" href="<?php echo esc_url($link); ?>">
						<div class="videoThumb">
							<img class="img-responsive" src="<?php echo esc_url($image); ?>" alt="" />
						</div>
					</a>
					<div class="box-title">
						<?php if( $title1) : ?>
							<div class="title-video"><h2><?php echo esc_html( $title1 ); ?></h2></div>
						<?php endif; ?>
						<?php if( $desc) : ?>
							<div class="desc-video"><?php echo esc_html( $desc ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif;?>
			</div>
		</div>
	</div>
	<?php endif;?>	
</div>
