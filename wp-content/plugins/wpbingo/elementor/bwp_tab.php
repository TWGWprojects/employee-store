<?php
namespace ElementorWpbingo\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit;
class Bwp_Tab extends Widget_Base {
	public function get_name() {
		return 'bwp_tab';
	}
	public function get_title() {
		return __( 'Wpbingo Tabs', 'wpbingo' );
	}
	public function get_icon() {
		return 'fa fa-tasks';
	}	
	public function get_categories() {
		return [ 'general' ];
	}
	protected function register_controls() {
		$groups = get_terms( array( 'taxonomy' => 'tabs_group'));
		$terms = array();
		if($groups){
			foreach ( $groups as $group ) {
				$terms[$group->term_id] = $group->name;
			}
		}
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'wpbingo' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'subtitle',
			[
				'label' => __( 'Sub Title', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your sub title here', 'wpbingo' ),
			]
		);		
		$this->add_control(
			'title1',
			[
				'label' => __( 'Title', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your title here', 'wpbingo' ),
			]
		);
		$this->add_control(
			'id_group',
			[
				'label' => __( 'Select Tabs Group', 'wpbingo' ),
				'multiple' => true,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $terms,
			]
		);
		$this->add_control(
			'image',
			[
				'label' => __( 'Choose Image', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);
		$this->add_control(
			'layout',
			[
				'label' => __( 'Layout', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'  => __( 'Default', 'wpbingo' ),
				],
			]
		);
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		$subtitle = ( $settings['subtitle'] ) ? $settings['subtitle'] : '';
		$title1 = ( $settings['title1'] ) ? $settings['title1'] : '';
		$id_group		 	= 	( $settings['id_group'] ) ? (int)$settings['id_group'] : '';
		$image		 = 	( $settings['image'] && $settings['image']['url'] ) ? $settings['image']['url'] : '';
		$layout		 	= 	( $settings['layout'] ) ? $settings['layout'] : 'default';
		if( $settings['layout'] == 'default' ){
			include(WPBINGO_ELEMENTOR_TEMPLATE_PATH.'bwp-tab/default.php' );
		}
	}
}
