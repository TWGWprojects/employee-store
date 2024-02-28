<?php
    /*
    *
    *	Wpbingo Framework Menu Functions
    *	------------------------------------------------
    *	Wpbingo Framework v3.0
    * 	Copyright Wpbingo Ideas 2017 - http://wpbingosite.com/
    *
    *	wicky_setup_menus()
    *
    */
    /* CUSTOM MENU SETUP
    ================================================== */
    register_nav_menus( array(
        'main_navigation' => esc_html__( 'Main Menu', 'wicky' ),
		'vertical_menu'     => esc_html__( 'Vertical Menu', 'wicky' ),
		'currency_menu'     => esc_html__( 'Currency Menu', 'wicky' ),   
        'language_menu'     => esc_html__( 'Language Menu', 'wicky' )
    ) );
?>