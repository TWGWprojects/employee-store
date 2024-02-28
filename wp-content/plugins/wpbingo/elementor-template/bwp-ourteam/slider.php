<?php 
do_action( 'before' );
$args = array(
	'post_type' => 'ourteam',
	'posts_per_page' => $numberposts,
	'post_status' => 'publish'
);

$query = new WP_Query($args);

if ( $query -> have_posts() ){ ?>
	<div class="bwp-ourteam <?php echo esc_attr($layout); ?> <?php if(empty($title1)) echo 'no-title'; ?>"> 
		<div class="ourteam-content">
			<?php if($title1) { ?>
				<div class="block-title">
					<h2><?php echo esc_html($title1); ?></h2>
				</div> 
			<?php } ?>
			<div class="slider slider-for slick-carousel" data-dots="true" data-columns4="<?php echo $columns4; ?>" data-columns3="<?php echo $columns3; ?>" data-columns2="<?php echo $columns2; ?>" data-columns1="<?php echo $columns1; ?>" data-columns="<?php echo $columns; ?>">	
			<?php while($query->have_posts()):$query->the_post(); ?>
				<div class="content">
					<div class="ourteam-image">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('full', array( 'class' => 'img-responsive' )) ?></a>
						<div class="position-social">
							<?php 
								$team_facebook  	= get_post_meta( get_the_ID(), 'team_facebook',true) ? get_post_meta( get_the_ID(), 'team_facebook',true) : '#';
								$team_twitter  		= get_post_meta( get_the_ID(), 'team_twitter',true) ? get_post_meta( get_the_ID(), 'team_twitter',true) : '#';
								$team_google_plus  	= get_post_meta( get_the_ID(), 'team_google_plus',true) ? get_post_meta( get_the_ID(), 'team_google_plus',true) : '#';
								$team_pinterest  	= get_post_meta( get_the_ID(), 'team_pinterest',true) ? get_post_meta( get_the_ID(), 'team_pinterest',true) : '#';
								if( $team_facebook || $team_twitter || $team_google_plus || $team_tumblr || $team_pinterest ) :
									echo '<ul class="social-link">';
									if( $team_facebook ) {
										echo '<li><a href="' . $team_facebook . '"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>';
									}
									if( $team_twitter ) {
										echo '<li><a href="' . $team_twitter . '"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>';
									}
									if( $team_google_plus ) {
										echo '<li><a href="' . $team_google_plus . '"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>';
									}
									if( $team_pinterest ) {
										echo '<li><a href="' . $team_pinterest . '"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>';
									}
									echo '</ul>';
								endif;
							?>	
						</div>
					</div>
					<div class="ourteam-item">
						<div class=" ourteam-info">
							<?php 
								$team_job  = get_post_meta( get_the_ID(), 'team_job',true) ? get_post_meta( get_the_ID(), 'team_job',true) : '';				
							?>
							<a class="ourteam-customer-name" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_title(); ?></a>
							<?php if($team_job): ?>	
								<div class="team-job"><?php echo esc_html($team_job); ?></div>
							<?php endif; ?>	
						</div>
					</div>
				</div>
			<?php endwhile; wp_reset_postdata();?>
			</div>
		</div>
	</div>
	<?php
}
?>