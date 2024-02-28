	<?php 
		$wicky_settings = wicky_global_settings();
		$enable_sticky_header = ( isset($wicky_settings['enable-sticky-header']) && $wicky_settings['enable-sticky-header'] ) ? ($wicky_settings['enable-sticky-header']) : false;
		$show_minicart = (isset($wicky_settings['show-minicart']) && $wicky_settings['show-minicart']) ? ($wicky_settings['show-minicart']) : false;
		$show_searchform = (isset($wicky_settings['show-searchform']) && $wicky_settings['show-searchform']) ? ($wicky_settings['show-searchform']) : false;		
		$show_wishlist = (isset($wicky_settings['show-wishlist']) && $wicky_settings['show-wishlist']) ? ($wicky_settings['show-wishlist']) : false;
	?>
	<h1 class="bwp-title hide"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
	<header id="bwp-header" class="bwp-header header-v4">
		<div class='header-wrapper'>
			<div class="header-top">
				<div class="row">
					<?php if($show_minicart || $show_searchform ){ ?>
						<div class="header-logo col-xs-12 col-sm-12 hidden-md hidden-lg hidden-xl hidden-sm">
							<?php wicky_header_logo();?>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-6 col-6 header-left">
							<div class="header-content sticky-sidebar">
								<div class="active-menu">
									<span class="line-1"></span>
								  	<span class="line-2"></span>
									<span class="line-3"></span>
								</div>
								<div class="header-main">
									<div class="active-menu"></div>
									<div class="wpbingo-menu-mobile wpbingo-menu-sidebar">
										<?php wicky_top_menu(); ?>
									</div>
								</div>
							</div>
							<div class="header-logo hidden-xs">
								<?php wicky_header_logo();?>
							</div>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-6 col-6 header-right">
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
					<?php } ?>
				</div>
			</div>
		</div>
	</header><!-- End #bwp-header -->