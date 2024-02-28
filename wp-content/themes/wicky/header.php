<?php
/**
 * Version:            1.0.0
 * Theme Name:         wicky
 * Theme URI:          http://wpbingosite.com/themes/wicky/
 * Author:             Wpbingo
 * Author URI:         http://wpbingosite.com/
 * License:            GNU General Public License v2 or later
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<?php 
	global $page_id;
	$wicky_settings = wicky_global_settings();
	$direction = wicky_get_direction(); 
	$page_id = get_the_ID();	
	$header_style = wicky_get_config('header_style', ''); 
	$header_style  = (get_post_meta( $page_id, 'page_header_style', true )) ? get_post_meta($page_id, 'page_header_style', true ) : $header_style ;
	$enable_sticky_header = ( isset($wicky_settings['enable-sticky-header']) && $wicky_settings['enable-sticky-header'] ) ? ($wicky_settings['enable-sticky-header']) : false;
	$show_minicart = (isset($wicky_settings['show-minicart']) && $wicky_settings['show-minicart']) ? ($wicky_settings['show-minicart']) : false;
	$show_searchform = (isset($wicky_settings['show-searchform']) && $wicky_settings['show-searchform']) ? ($wicky_settings['show-searchform']) : false;	
?>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="//gmpg.org/xfn/11">
	<link rel="pingback" href="<?php esc_url(bloginfo( 'pingback_url' )); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php wicky_loading_overlay(); ?>
<div id='page' class="hfeed page-wrapper">
	<?php if(isset($header_style) && $header_style) { ?>
		<?php get_template_part('templates/headers/header',$header_style); ?>
	<?php }else{ ?>
		<div id='bwp-header' class="bwp-header bwp-header-default">
			<div class="container">
				<div class='header-content' data-sticky_header="<?php echo esc_attr($enable_sticky_header); ?>">
					<div class="row">
						<div class="col-xl-2 col-lg-2 col-md-6 col-sm-6 col-6 header-logo">
							<?php wicky_header_logo(); ?>
						</div>
						<?php if($show_minicart || $show_searchform){ ?>
							<div class="col-xl-8 col-lg-8 col-md-12 col-12  wpbingo-menu-mobile">
								<?php wicky_top_menu(); ?>
							</div>
							<div class="col-xl-2 col-lg-2 col-md-6 col-sm-6 col-6 margin-top-20">
								 <!-- Begin menu -->
								<?php if($show_minicart && class_exists( 'WooCommerce' )){ ?>
								<div class="wpbingoCartTop pull-right">
									<?php get_template_part( 'woocommerce/minicart-ajax' ); ?>
								</div>
								<?php } ?>
								<!-- Begin Search -->
								<?php if($show_searchform && class_exists( 'WooCommerce' )){ ?>
								<div class="search-box pull-right">
									<div class="search-toggle"><i class="fa fa-search"></i></div>
									<div class="dropdown-search"><?php wicky_search_form_product(); ?></div>
								</div>
								<?php } ?>
								<!-- End Search -->
							</div>
						<?php }else{ ?>
							<div class="col-xl-10 col-lg-10 col-md-6 col-6 wpbingo-menu-mobile text-right">
								<?php wicky_top_menu(); ?>
							</div>						
						<?php } ?>
					</div>
				</div>
			</div>
		</div><!-- End header-wrapper -->	
	<?php } ?>
<div id="bwp-main" class="bwp-main">
<?php wicky_page_title(); ?>