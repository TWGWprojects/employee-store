<?php $tag_id = 'testimonial_' .rand().time(); 
	$args = array(
		'post_type' => 'testimonial',
		'posts_per_page' => -1,
		'post_status' => 'publish'
	);

	$query = new WP_Query($args); 
?>
<?php if($query->have_posts()):?>
<div class="bwp-testimonial <?php echo esc_attr($layout); ?>">
 <div class="block">
	<?php if( $title1) : ?>
		<div class="testimonial-title">
			<h2><?php echo esc_html( $title1 ); ?></h2>
		</div>
	<?php endif; ?>
  <div class="block_content">
   <div id="<?php echo $tag_id; ?>" class="slick-carousel" data-nav="<?php echo esc_attr($show_nav);?>" data-dots="<?php echo esc_attr($show_pag);?>" data-columns4="<?php echo $columns4; ?>" data-columns3="<?php echo $columns3; ?>" data-columns2="<?php echo $columns2; ?>" data-columns1="<?php echo $columns1; ?>" data-columns="<?php echo $columns; ?>">
		<?php while($query->have_posts()):$query->the_post(); ?>
			<?php $testimonial_job  = get_post_meta( get_the_ID(), 'testimonial_job',true) ? get_post_meta( get_the_ID(), 'testimonial_job',true) : ''; ?>
			<!-- Wrapper for slides -->
			<div class="testimonial-item">
				<div class="item">
					<div class="testimonial-info-content">
						<div class="testimonial-image">
							<?php the_post_thumbnail( 'thumbnail' ); ?>
						</div>
						<div class="testimonial-info">
							<?php echo wpbingo_get_excerpt( $length, false ); ?>
							<h5 class="testimonial-customer-name"><?php the_title(); ?></h5>
							<?php if($testimonial_job): ?>	
							<div class="testimonial-job"><?php echo esc_html($testimonial_job); ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endwhile; wp_reset_postdata();?>
   </div>
  </div>
 </div>
</div>
<?php endif;?>