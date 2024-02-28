<?php 
	get_header(); 
	$wicky_settings = wicky_global_settings();
	$background = wicky_get_config('background');
	$bgs = isset($wicky_settings['img-404']['url']) && $wicky_settings['img-404']['url'] ? $wicky_settings['img-404']['url'] : "";
	$title_error 	=	( isset($wicky_settings['title-error']) && $wicky_settings['title-error'] ) ? $wicky_settings['title-error'] : esc_html__('Page not found!', 'wicky');
	$sub_error		=	( isset($wicky_settings['sub-error']) && $wicky_settings['sub-error'] ) ? $wicky_settings['sub-error'] : esc_html__('Oops! Page you are looking for does not exist. ', 'wicky');
	$btn_error		=	( isset($wicky_settings['btn-error']) && $wicky_settings['btn-error'] ) ? esc_html($wicky_settings['btn-error']) : esc_html__('back to home page', 'wicky');
?>
<div class="page-404">
	<div class="img-404">
		<?php if($bgs){ ?>
			<img src="<?php echo esc_url($bgs); ?>" alt="<?php echo esc_attr__('Image 404','wicky'); ?>">
		<?php }else{ ?>
			<img src="<?php echo esc_url(get_template_directory_uri().'/images/image_404.jpg'); ?>" alt="<?php echo esc_attr__('Image 404','wicky'); ?>" >							
		<?php } ?>	
	</div>
	<div class="content-page-404">
		<div class="sub-error">
			<?php echo esc_html($title_error); ?>
		</div>
		<div class="sub-error1">
			<?php echo esc_html($sub_error); ?>
		</div>
		<div class="button-page-404">
			<a class="btn" href="<?php echo esc_url( home_url('/') ); ?>"><?php echo esc_html($btn_error); ?></a>
		</div>
	</div>
</div>
<?php
get_footer();