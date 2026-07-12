<?php declare( strict_types=1 );
/**
 * Enqueues a WooCommerce-specific stylesheet when WooCommerce is active.
 *
 * This is the theme's worked example of plugin-conditional code: the theme's own styling
 * (theme-setup.php) and small theme-owned dynamic content (theme-dynamic-content.php) never guard
 * on another plugin's presence, but this file exists only because WooCommerce might be. The guard
 * makes WooCommerce's absence silent -- no notice, no fallback markup -- so this file is safe to
 * leave in a site that never installs the plugin.
 *
 * To remove this worked example, delete this file, delete `assets/css/src/cart.scss` and its built
 * counterparts `assets/css/build/cart.css` and `assets/css/build/cart.css.map`, and delete
 * `test_woocommerce_conditional_style_is_not_registered_without_woocommerce` from
 * `tests/Integration/AssetsTest.php`.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

if ( ! \class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Enqueues the cart-page stylesheet when WooCommerce's cart page is being viewed.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
function a8csp_template_theme_enqueue_woocommerce_cart_style(): void {
	if ( ! \function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}

	$theme_slug = a8csp_template_theme_get_slug();

	// The null guard lets a component whose build output was removed degrade to unstyled instead of fataling.
	$style_meta = a8csp_template_theme_get_asset_meta( 'assets/css/build/cart.css', array( 'woocommerce-general' ) );

	if ( null === $style_meta ) {
		return;
	}

	wp_enqueue_style(
		"{$theme_slug}-woocommerce-cart",
		get_theme_file_uri( 'assets/css/build/cart.css' ),
		$style_meta['dependencies'],
		$style_meta['version']
	);
}
add_action( 'wp_enqueue_scripts', 'a8csp_template_theme_enqueue_woocommerce_cart_style' );
