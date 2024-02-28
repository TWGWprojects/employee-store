<?php
namespace ElementorWpbingo\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
if ( ! defined( 'ABSPATH' ) ) exit;
class Bwp_Instagram extends Widget_Base {
	public function get_name() {
		return 'bwp_instagram';
	}
	public function get_title() {
		return __( 'Wpbingo Instagram', 'wpbingo' );
	}
	public function get_icon() {
		return 'fa fa-instagram';
	}	
	public function get_instagram() {
		return [ 'general' ];
	}
	protected function register_controls() {
		$number = array('1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6);
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'wpbingo' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
			'subtitle',
			[
				'label' => __( 'Sub Title', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your Sub Title here', 'wpbingo' ),
			]
		);
		$this->add_control(
			'description',
			[
				'label' => __( 'Description', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your Description here', 'wpbingo' ),
			]
		);		
		$this->add_control(
			'user_id',
			[
				'label' => __( 'User Id', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your User Id here', 'wpbingo' ),
			]
		);
		$this->add_control(
			'access_token',
			[
				'label' => __( 'Access Token', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your Access Token here', 'wpbingo' ),
			]
		);
		$this->add_control(
			'width',
			[
				'label' => __( 'Width', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your Width here', 'wpbingo' ),
			]
		);
		$this->add_control(
			'height',
			[
				'label' => __( 'Height', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your Height here', 'wpbingo' ),
			]
		);
		$this->add_control(
			'limit',
			[
				'label' => __( 'Number of Instagrams', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => 6,
				'description' => __( 'Type your Number of Posts', 'wpbingo' ),
			]
		);
		$this->add_control(
			'item_row',
			[
				'label' => __( 'Number row of Instagrams', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => __( 'Type your title here', 'wpbingo' ),
				'default' => 1,
			]
		);		
		$this->add_control(
			'columns',
			[
				'label' => __( 'Number of Columns >1200px', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $number,
				'default' => 1
			]
		);
		$this->add_control(
			'columns1',
			[
				'label' => __( 'Number of Columns on 992px to 1199px', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $number,
				'default' => 1
			]
		);
		$this->add_control(
			'columns2',
			[
				'label' => __( 'Number of Columns on 768px to 991px', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $number,
				'default' => 1
			]
		);
		$this->add_control(
			'columns3',
			[
				'label' => __( 'Number of Columns on 480px to 767px', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $number,
				'default' => 1
			]
		);
		$this->add_control(
			'columns4',
			[
				'label' => __( 'Number of Columns in 480px or less than', 'wpbingo' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => $number,
				'default' => 1
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
					'slider'  => __( 'Slider', 'wpbingo' )
				],
			]
		);		
		$this->end_controls_section();
	}
	protected function render() {
		$settings = $this->get_settings_for_display();
		extract( shortcode_atts(
			array(
				'title1' 	=> '',
				'subtitle' 	=> '',
				'description' 	=> '',
				'user_id' 	=> '',
				'access_token' => '',
				'limit' 	=> 9,
				'width' 	=> '180px',
				'height' 	=>'200px',
				'item_row'=> 1,
				'columns' 	=> 3,
				'columns1' 	=> 3,
				'columns2' 	=> 3,
				'columns3' 	=> 1,
				'columns4' 	=> 1,
				'padding'   => '',
				'layout'  => 'default',
			), $settings )
		);
		$widget_id = 'bwp_instagram_'.rand().time();
		if( $layout == 'slider' ){
			include(WPBINGO_ELEMENTOR_TEMPLATE_PATH.'bwp-instagram/slider.php' );
		}else{
			include(WPBINGO_ELEMENTOR_TEMPLATE_PATH.'bwp-instagram/default.php' );
		}
	}
}