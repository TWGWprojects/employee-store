<?php
$classes = array('post-grid',$attributes); 
$tag_id = 'bwp_tab_' .rand().time();
if($id_group):
	$tabs = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type' => 'bwp_tab',
			'tax_query' => array(
				array(
					'taxonomy' => 'tabs_group',
					'field' => 'term_id',
					'terms' => $id_group,
				)
			)
		)
	);
	if($tabs): ?>
	<div id="<?php echo esc_html($tag_id); ?>" class="bwp-tabs <?php echo esc_attr($layout); ?>">
		<div class="block_content">
			<?php if (isset($title1) && $title1){ ?>
			<div class="title-block">
			<?php echo '<h2>'. esc_html($title1) .'</h2>'; ?>
			</div>
			<?php } ?>
			<ul class="nav nav-tabs">
			<?php foreach($tabs as $key=>$tab){ ?>
			  <li <?php if($key==0){ ?>class="active"<?php } ?>><a data-toggle="tab" href="#<?php echo esc_attr($tag_id.$tab->ID); ?>"><?php echo esc_html($tab->post_title); ?></a></li>
			<?php } ?>
			</ul>
			<div class="row">
				<div class="tab-content">
					<?php foreach($tabs as $key=>$tab){ ?>
					<div id="<?php echo esc_attr($tag_id.$tab->ID); ?>" class="tab-pane <?php if($key==0){ ?>active<?php } ?>">
						<?php if( in_array( 'elementor/elementor.php', apply_filters('active_plugins', get_option( 'active_plugins' ))) ){ ?>
							<?php		
							$elementor_instance = Elementor\Plugin::instance();
							echo $elementor_instance->frontend->get_builder_content_for_display( $tab->ID );
							?>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
<?php endif; ?>