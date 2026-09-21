<?php declare( strict_types=1 );
/**
 * Registers a block binding source exposing small theme-owned dynamic content.
 *
 * This is the theme's dynamic-content worked example: a block binding rather than a shortcode,
 * because a binding attaches straight to a block attribute in the editor and needs no separate
 * shortcode parse pass. The `patterns/footer-default.php` pattern binds a paragraph to this source.
 *
 * To remove this worked example, delete this file, remove the bound paragraph from
 * `patterns/footer-default.php`, and delete `test_theme_dynamic_content_binding_is_registered` from
 * `tests/Integration/SiteBootTest.php`.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the current-year block binding source.
 *
 * The source namespace is the theme slug, not the PHP prefix: core rejects any binding source
 * name outside lowercase alphanumerics and dashes.
 *
 * @return  void
 */
function a8csp_template_theme_register_block_bindings(): void {
	register_block_bindings_source(
		'a8csp-project-template/current-year',
		array(
			'label'              => __( 'Current year', 'a8csp-project-template' ),
			'get_value_callback' => static fn () => wp_date( 'Y' ),
		)
	);
}
add_action( 'init', 'a8csp_template_theme_register_block_bindings' );
