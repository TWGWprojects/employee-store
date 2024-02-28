<?php $wicky_settings = wicky_global_settings(); ?>
<article id="post-<?php esc_attr(the_ID()); ?>" <?php post_class(); ?>>
	<?php if ( in_array( 'category', get_object_taxonomies( get_post_type() ) ) && wicky_categorized_blog() ) : ?>
		<div class="cat-links"><?php echo get_the_category_list(', '); ?></div>
	<?php endif; ?>	
	<?php
		$show_post_title = wicky_get_config('post-title',true);
		if ($show_post_title){
			if ( is_single() ){
				the_title( '<h3 class="entry-title">', '</h3>' );
			}else {
				the_title( '<h3 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h3>' );
			}
		}
	?>
	<div class="entry-by entry-meta">
		<?php wicky_single_posted_on_2(); ?>
	</div>
	<?php if ( get_the_post_thumbnail() ){ ?>
		<div class="entry-thumb single-thumb">
			<?php the_post_thumbnail( 'full' ); ?>
		</div>
	<?php }; ?>	
	<?php if ( is_search() ) : ?>
	<div class="entry-summary">
		<?php the_excerpt(); ?>
	</div><!-- .entry-summary -->
	<?php else : ?>
	<div class="post-content">
		<div class="post-excerpt clearfix">
			<?php
				/* translators: %s: Name of current post */
				the_content( sprintf(
					the_title( '<span class="screen-reader-text">', '</span>', false )
				) );
				wp_link_pages( array(
					'before'      => '<div class="page-links clearfix"><span class="page-links-title">' . esc_html__( 'Pages:', 'wicky' ) . '</span>',
					'after'       => '</div>',
					'link_before' => '<span>',
					'link_after'  => '</span>',
				) );
			?>
		</div>
		<div class="clearfix"></div>
	</div><!-- .entry-content -->
	<?php $tags_list = get_the_tag_list( '', esc_html_x( ' ', 'list item separator', 'wicky' ) ); ?>
	<?php if ( $tags_list || ( shortcode_exists( 'social_share' ) ) ) { ?>
	<div class="post-content-entry">
		<!-- Tag -->
		<?php
		if ( 'post' === get_post_type() ) {
			$tags_list = get_the_tag_list( '', esc_html_x( ' ', 'list item separator', 'wicky' ) );
			if ( $tags_list ) {
				printf( '<div class="tags-links"><span>' . esc_html__( ' Tags:', 'wicky' ) . '</span>' . esc_html__( ' %1$s', 'wicky' ) . '</div>', $tags_list ); // WPCS: XSS OK.
			}
		}		
		?>
		<!-- Social Share -->
		<?php if ( shortcode_exists( 'social_share' ) ) : ?> 
			<div class="entry-social-share">
			<span class="title-social"><?php echo esc_html__( 'Share:', 'wicky' ) ?></span>
			<?php echo do_shortcode( "[social_share]" ); ?>	
			</div>
		<?php endif; ?>
	</div>
	<?php } ?>
	<!-- Previous/next post navigation. -->
	<div class="clearfix"></div>
	<?php wicky_entry_footer(); ?>	
	<?php endif; ?>
</article><!-- #post-## -->