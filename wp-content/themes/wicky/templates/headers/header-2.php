	<?php 
		$wicky_settings = wicky_global_settings();
		$enable_sticky_header = ( isset($wicky_settings['enable-sticky-header']) && $wicky_settings['enable-sticky-header'] ) ? ($wicky_settings['enable-sticky-header']) : false;
		$show_minicart = (isset($wicky_settings['show-minicart']) && $wicky_settings['show-minicart']) ? ($wicky_settings['show-minicart']) : false;
		$show_searchform = (isset($wicky_settings['show-searchform']) && $wicky_settings['show-searchform']) ? ($wicky_settings['show-searchform']) : false;		
		$show_wishlist = (isset($wicky_settings['show-wishlist']) && $wicky_settings['show-wishlist']) ? ($wicky_settings['show-wishlist']) : false;
	?>
	<h1 class="bwp-title hide"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
	<header id="bwp-header" class="bwp-header header-v2">
		<div class="header-wrapper">
			<div class="header-content sticky-sidebar">
				<div class="btn-sticky hidden-lg hidden-md"></div>
				<div class="header-main">
					<div class="header-logo">
						<?php wicky_header_logo(); ?>
					</div>
					<div class="header-cart">
						<!-- Begin Search -->
						<?php if($show_searchform && class_exists( 'WooCommerce' )){ ?>
							<?php get_template_part( 'search-form' ); ?>
							<?php } ?>
						<!-- End Search -->	
						<?php if(is_active_sidebar('top-link')){ ?>
						<div class="block-top-link">
							<?php dynamic_sidebar( 'top-link' ); ?>
						</div>
						<?php } ?>
						<?php if($show_minicart && class_exists( 'WooCommerce' )){ ?>
						<div class="wicky-topcart">
							<?php get_template_part( 'woocommerce/minicart-ajax' ); ?>
						</div>
						<?php } ?>
					</div>
					<div class="wpbingo-menu-mobile wpbingo-menu-sidebar">
						<?php wicky_top_menu(); ?>
					</div>
					<div class="title-social"><?php echo esc_html__( 'follow us','wicky' ); ?></div>
					<div class="icon-social"><?php echo do_shortcode( "[social_link]" ); ?></div>
					<?php if( isset($wicky_settings['copy-right']) && $wicky_settings['copy-right'] ) : ?>
					<div class="copy-right">
						<?php echo esc_html($wicky_settings['copy-right']); ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div><!-- End header-wrapper -->
	</header><!-- End #bwp-header -->