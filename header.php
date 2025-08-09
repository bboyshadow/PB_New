<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package creativoypunto
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'creativoypunto' ); ?></a>

	<header id="site-header" class="site-header main_header fixed-top">
		<div class="container-fluid p-0">
			<nav class="navbar navbar-expand-xl navbar-dark bg-dark">
				<div class="container-fluid justify-content-between">
					<a class="brand header_logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img src="<?php echo get_template_directory_uri(); ?>/images/logo_blanco_creativoypunto.svg" alt="Logo yacht">
					</a>
					<button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#main-menu">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse mx-auto" id="main-menu">
						<?php
							wp_nav_menu(
								array(
									'theme_location' => 'main-menu',
									'container'      => false,
									'menu_class'     => 'navbar-nav mx-auto',
									'fallback_cb'    => '__return_false',
									'items_wrap'     => '<div id="%1$s" class="%2$s">%3$s</div>',
									'depth'          => 2,
									'walker'         => new bootstrap_5_menu_creativoypunto(),
								)
							);
							?>
						<ul class="navbar-nav ms-auto d-flex flex-row">
							<li class="nav-item me-3 me-lg-0">
								<a class="nav-link p-0 m-0" href="#">
									<img class="social_nav_icon" src="<?php echo get_template_directory_uri(); ?>/images/ws.svg" alt="WhatsApp">
								</a>
							</li>
							<li class="nav-item me-3 me-lg-0">
								<a class="nav-link p-0 m-0" href="#">
									<img class="social_nav_icon" src="<?php echo get_template_directory_uri(); ?>/images/ig.svg" alt="Instagram">
								</a>
							</li>
							<li class="nav-item me-3 me-lg-0">
								<a class="nav-link p-0 m-0" href="#">
									<img class="social_nav_icon" src="<?php echo get_template_directory_uri(); ?>/images/yt.svg" alt="YouTube">
								</a>
							</li>
						</ul>
					</div>
				</div>
			</nav>
		</div>
	</header>
	<?php // get_footer(); ?>
