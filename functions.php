<?php


if ( ! defined( '_S_VERSION' ) ) {
		
		define( '_S_VERSION', '1.0.0' );
}


function creativoypunto_setup() {
		
		load_theme_textdomain( 'creativoypunto', get_template_directory() . '/languages' );

		
		add_theme_support( 'automatic-feed-links' );

		
		add_theme_support( 'title-tag' );

		
		add_theme_support( 'post-thumbnails' );

		
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'creativoypunto' ),
			)
		);
		register_nav_menu( 'main-menu', esc_html__( 'Main menu', 'creativoypunto' ) );

		
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		
		add_theme_support(
			'custom-background',
			apply_filters(
				'creativoypunto_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		
		add_theme_support( 'customize-selective-refresh-widgets' );

		
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
}
add_action( 'after_setup_theme', 'creativoypunto_setup' );


function creativoypunto_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'creativoypunto_content_width', 640 );
}
add_action( 'after_setup_theme', 'creativoypunto_content_width', 0 );


function creativoypunto_widgets_init() {
		register_sidebar(
			array(
				'name'          => esc_html__( 'Sidebar', 'creativoypunto' ),
				'id'            => 'sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'creativoypunto' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
}
add_action( 'widgets_init', 'creativoypunto_widgets_init' );


require_once get_template_directory() . '/inc/menu-creativoypunto.php';

function enqueue_custom_scripts() {
	wp_enqueue_script( 'auto-hide-menu', get_template_directory_uri() . '/js/menu-creativoypunto.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );

function menu_creativoypunto() {
	wp_enqueue_style( 'menu_creativoypunto', get_template_directory_uri() . '/css/menu-creativoypunto.css' );
}
add_action( 'wp_enqueue_scripts', 'menu_creativoypunto' );



function enqueue_bootstrap_local() {
	
	wp_enqueue_style( 'bootstrap-css', get_template_directory_uri() . '/bootstrap/css/bootstrap.min.css' );
	wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/bootstrap/js/bootstrap.bundle.min.js', array( 'jquery' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_bootstrap_local' );

function enqueue_font_awesome_local() {
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/fontawesome/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_font_awesome_local' );

function creativoypunto_scripts() {
	wp_enqueue_style( 'creativoypunto-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'creativoypunto-style', 'rtl', 'replace' );
	wp_enqueue_script( 'creativoypunto-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'creativoypunto_scripts' );

function enqueue_construction_styles() {
	if ( is_page_template( 'under-construction.php' ) ) {
		wp_enqueue_style( 'construction-style', get_template_directory_uri() . '/css/construction.css', array(), '1.0', 'all' );
	}
}
add_action( 'wp_enqueue_scripts', 'enqueue_construction_styles' );



require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/customizer.php';
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}



require_once get_template_directory() . '/app_yacht/core/bootstrap.php';


add_action(
	'init',
	function() {
		if ( ! class_exists( 'AppYachtBootstrap' ) ) {
			error_log( 'Error: AppYachtBootstrap class not found' );
			return;
		}
	
		try {
			AppYachtBootstrap::init();
		} catch ( Exception $e ) {
			error_log( 'Error inicializando App Yacht: ' . $e->getMessage() );
		}
	}
);


$legacy_yacht_functions = get_template_directory() . '/app_yacht/core/yacht-functions.php';
if ( file_exists( $legacy_yacht_functions ) ) {
	require_once $legacy_yacht_functions;
}




function remove_jquery_migrate_script( &$scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];
		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
add_action( 'wp_default_scripts', 'remove_jquery_migrate_script' );
