<?php
/**
 * ProBroke functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ProBroke
 */

if ( ! defined( '_S_VERSION' ) ) {
		// Replace the version number of the theme on each release.
		define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
// ==================== APP mail ==================== //
add_action( 'wp_ajax_gmail_auth', 'handle_gmail_auth_request' );
function handle_gmail_auth_request() {
	check_ajax_referer( 'mail_nonce', 'security' );
	
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'Usuario no autenticado', 401 );
	}
	
	try {
		include get_template_directory() . '/app_mail/gmail/oauth.php';
		wp_die();
	} catch ( Exception $e ) {
		wp_send_json_error( $e->getMessage(), 500 );
	}
}

add_action( 'init', 'app_mail_custom_routes' );
function app_mail_custom_routes() {
	add_rewrite_rule(
		'^app-mail/gmail-oauth/?$',
		'index.php?gmail_oauth=1',
		'top'
	);
}

add_filter( 'query_vars', 'app_mail_query_vars' );
function app_mail_query_vars( $vars ) {
	$vars[] = 'gmail_oauth';
	return $vars;
}

add_action( 'template_include', 'app_mail_handle_routes' );
function app_mail_handle_routes( $template ) {
	if ( get_query_var( 'gmail_oauth' ) ) {
		return get_template_directory() . '/app_mail/gmail/oauth.php';
	}
	return $template;
}

// ==================== END APP mail ==================== //

function creativoypunto_setup() {
		/*
				* Make theme available for translation.
				* Translations can be filed in the /languages/ directory.
				* If you're building a theme based on ProBroke, use a find and replace
				* to change 'creativoypunto' to the name of your theme in all the template files.
				*/
		load_theme_textdomain( 'creativoypunto', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
				* Let WordPress manage the document title.
				* By adding theme support, we declare that this theme does not use a
				* hard-coded <title> tag in the document head, and expect WordPress to
				* provide it for us.
				*/
		add_theme_support( 'title-tag' );

		/*
				* Enable support for Post Thumbnails on posts and pages.
				*
				* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
				*/
		add_theme_support( 'post-thumbnails' );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__( 'Primary', 'creativoypunto' ),
			)
		);

		/*
				* Switch default core markup for search form, comment form, and comments
				* to output valid HTML5.
				*/
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

		// Set up the WordPress core custom background feature.
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

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
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

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function creativoypunto_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'creativoypunto_content_width', 640 );
}
add_action( 'after_setup_theme', 'creativoypunto_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
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

// ==================== AUTO HIDE MENU ====================//
require_once get_template_directory() . '/inc/menu-creativoypunto.php';

function enqueue_custom_scripts() {
	wp_enqueue_script( 'auto-hide-menu', get_template_directory_uri() . '/js/menu-creativoypunto.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );

function menu_creativoypunto() {
	wp_enqueue_style( 'menu_creativoypunto', get_template_directory_uri() . '/css/menu-creativoypunto.css' );
}
add_action( 'wp_enqueue_scripts', 'menu_creativoypunto' );
// ==================== END AUTO HIDE MENU ====================//

// ==================== SCRIPTS AND STYLES ====================//
function enqueue_bootstrap_local() {
	// Bootstrap local
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
// ==================== END SCRIPTS AND STYLES ====================//

// Additional includes
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

// ==================== APP YACHT v2.0 ====================//
// Nueva arquitectura refactorizada - carga bootstrap principal
require_once get_template_directory() . '/app_yacht/core/bootstrap.php';

// Inicializar App Yacht con nueva arquitectura
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

// Mantener compatibilidad con funciones legacy si existen
$legacy_yacht_functions = get_template_directory() . '/app_yacht/core/yacht-functions.php';
if ( file_exists( $legacy_yacht_functions ) ) {
	require_once $legacy_yacht_functions;
}
// ==================== APP YACHT v2.0 END====================//


/**
 * Deshabilitar jQuery Migrate en frontend si no se necesita
 */
function remove_jquery_migrate_script( &$scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];
		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
add_action( 'wp_default_scripts', 'remove_jquery_migrate_script' );
