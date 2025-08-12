<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <main id="main">
 *
 * @package Pluto
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<meta name="pinterest" content="nopin"/>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<?php do_action( 'pluto-before' ); ?>

	<nav id="site-navigation" class="main-navigation" role="navigation">
		<div id="nav-container">
			<p class="menu-toggle"><?php _e( 'Menu', 'pluto' ); ?></p>

			<div class="screen-reader-text skip-link">
				<a href="#content" title="<?php esc_attr_e( 'Skip to content', 'pluto' ); ?>">
					<?php _e( 'Skip to content', 'pluto' ); ?>
				</a>
			</div>

			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
				) );
			}
			?>
		</div>
	</nav><!-- #site-navigation -->

	<header id="masthead" class="site-header" role="banner">
		<div class="site-branding">
			<?php
			$option = 'logo';
			if ( is_page_template( 'default' ) ) {
				$option = 'logo_home';
			}
			if( (of_get_option($option, true) != "") && (of_get_option($option, true) != 1) ) : ?>
				<p class="site-title logo-container">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
	 					<?php echo "<img class='main_logo' src='".of_get_option($option, true)."' title='".esc_attr(get_bloginfo( 'name','display' ) )."' />"; ?>
					</a>
				</p>
			<?php else : ?>
				<p class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>

		<?php get_template_part('social', 'sociocon'); ?>
	</header><!-- #masthead -->

	<div id="content" class="site-content">

	<?php get_template_part('slider', 'bx'); ?>
