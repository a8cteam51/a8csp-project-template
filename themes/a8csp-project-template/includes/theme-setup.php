<?php declare( strict_types=1 );
/**
 * Registers theme editor styles and front-end assets.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the block editor stylesheet.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
function a8csp_template_theme_setup(): void {
	add_editor_style( 'style-editor.css' );
}
add_action( 'after_setup_theme', 'a8csp_template_theme_setup' );

/**
 * Enqueues the theme's front-end assets.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
function a8csp_template_theme_enqueue_assets(): void {
	$theme_slug = a8csp_template_theme_get_slug();

	// Generated files are optional because asset compilation is a separate deployment step.
	$style_meta = a8csp_template_theme_get_asset_meta( get_theme_file_path( 'style.css' ) );

	if ( null !== $style_meta ) {
		$style_handle = "{$theme_slug}-style";

		wp_enqueue_style(
			$style_handle,
			get_stylesheet_uri(),
			$style_meta['dependencies'],
			$style_meta['version']
		);
		wp_style_add_data( $style_handle, 'rtl', 'replace' );
	}

	$script_path = 'assets/js/build/index.js';
	$script_meta = a8csp_template_theme_get_asset_meta( get_theme_file_path( $script_path ) );

	if ( null !== $script_meta ) {
		wp_enqueue_script(
			"{$theme_slug}-script",
			get_theme_file_uri( $script_path ),
			$script_meta['dependencies'],
			$script_meta['version'],
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'a8csp_template_theme_enqueue_assets' );
