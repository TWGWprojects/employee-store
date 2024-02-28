<?php

	$tag_id = 'recent_post_' .rand().time(); 
	$args = array(
	'post_type' => 'post',
	'cat' => $category, 
	'posts_per_page' => $limit
	);

	$query = new WP_Query($args);
	$post_count = $query->post_count;
	$j = 0;
?>
<?php if($query->have_posts()):?>
<div class="bwp-recent-post <?php echo esc_attr($layout); ?>">
 <div class="block">
 	<?php if(isset($title1) && $title1) { ?>
	<div class="title-block">
		<h2><?php echo esc_html($title1); ?></h2>
		<?php if($description) { ?>
		<div class="page-description"><?php echo esc_html($description); ?></div>
		<?php } ?>  
	</div>
	<?php } ?>
  <div class="block_content">
   <div id="<?php echo esc_attr($tag_id); ?>" class="slick-carousel" data-slidestoscroll="true" data-dots="<?php echo esc_attr($show_pag);?>" data-columns4="<?php echo $columns4; ?>" data-columns3="<?php echo $columns3; ?>" data-columns2="<?php echo $columns2; ?>" data-columns1="<?php echo $columns1; ?>" data-columns="<?php echo $columns; ?>">
		<?php while($query->have_posts()):$query->the_post(); ?>
		<!-- Wrapper for slides -->
			<div class="item <?php if ($j%2==0) { ?> left <?php }else{ ?>right<?php } ?> ">
				<div  <?php post_class( 'post-grid' ); ?>>	
					<div class="post-inner style">
						<?php if( bwp_category_post()){ ?>
						<div class="post-categories">
							<a href="<?php echo esc_url(bwp_category_post()->cat_link);?>"><?php echo esc_html(bwp_category_post()->name); ?></a>
						</div>
						<?php } ?>
						<?php wpbingo_posted_on(); ?>
						<div class="post-thumbnail">
							<a href="<?php the_permalink(); ?>" aria-hidden="true">
								<?php
									if( has_post_thumbnail() ) :
										the_post_thumbnail( 'post-thumbnail', array( 'alt' => get_the_title() ) );
									else :
										echo '<img src="' . esc_url( get_template_directory_uri() . '/images/placeholder.jpg' ) . '" alt="' . get_the_title() . '">';
									endif;
								?>
							</a>
						</div>
						<div class="post-content">
							<h2 class="entry-title"><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h2>	
							<?php echo wpbingo_get_excerpt( $length, false ); ?>	
						</div>
					</div>
				</div><!-- #post-## -->
			</div>
		<?php $j++; endwhile; wp_reset_postdata(); ?>
   </div>
  </div>
 </div>
</div>
<?php endif;?>