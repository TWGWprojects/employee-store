	<?php 
		$wicky_settings = wicky_global_settings();
		$enable_sticky_header = ( isset($wicky_settings['enable-sticky-header']) && $wicky_settings['enable-sticky-header'] ) ? ($wicky_settings['enable-sticky-header']) : false;
		$show_minicart = (isset($wicky_settings['show-minicart']) && $wicky_settings['show-minicart']) ? ($wicky_settings['show-minicart']) : false;
		$show_searchform = (isset($wicky_settings['show-searchform']) && $wicky_settings['show-searchform']) ? ($wicky_settings['show-searchform']) : false;		
		$show_wishlist = (isset($wicky_settings['show-wishlist']) && $wicky_settings['show-wishlist']) ? ($wicky_settings['show-wishlist']) : false;
	?>
<h1 class="bwp-title hide"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
<header id="bwp-header" class="bwp-header header-v3<?php if($show_minicart || $show_searchform || is_active_sidebar('top-link')){ ?> header-absolute<?php } ?>">
	<div class='header-wrapper'>
		<div class="header-top">
			<div class="container">
				<div class="row">
					<?php if($show_minicart || $show_searchform || is_active_sidebar('top-link')){ ?>
						<div class="col-xl-2 col-lg-3 col-md-12 col-sm-12 header-left">
							<?php wicky_header_logo(); ?>
						</div>
						<div class="col-xl-8 col-lg-6 col-md-4 col-sm-2 col-6 header-center">
							<div class='header-content' data-sticky_header="<?php echo esc_attr($wicky_settings['enable-sticky-header']); ?>">
								<div class="header-wpbingo-menu-left row">
									<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 header-menu">
										<div class="wpbingo-menu-mobile">
											<?php wicky_top_menu(); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-8 col-sm-10 col-6 header-right">
							<?php if(is_active_sidebar('top-link')){ ?>
								<div class="block-top-link">
									<?php dynamic_sidebar( 'top-link' ); ?>
								</div>
							<?php } ?>
							<!-- Begin Search -->
							<?php if($show_searchform && class_exists( 'WooCommerce' )){ ?>
							<div class="search-box">
								<div class="search-toggle"><i class="icon-search"></i></div>
							</div>
							<?php } ?>
							<!-- End Search -->
							<?php if($show_minicart && class_exists( 'WooCommerce' )){ ?>
							<div class="wicky-topcart">
								<?php get_template_part( 'woocommerce/minicart-ajax' ); ?>
							</div>
							<?php } ?>
						</div>
					<?php }else{ ?>
						<div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 col-6 header-test header-left">
							<?php wicky_header_logo(); ?>
						</div>
						<div class="col-xl-10 col-lg-9 col-md-6 col-sm-6 col-6 header-test header-menu">
							<div class="wpbingo-menu-mobile">
								<?php wicky_top_menu(); ?>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div><!-- End header-wrapper -->
</header><!-- End #bwp-header -->