<?php declare( strict_types=1 );
/**
 * Integration coverage for the project theme's WordPress bootstrap contract.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * WordPress boots with the project theme active and the theme's bootstrap contract holds.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
final class SiteBootTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Confirms both active theme identifiers name the project theme.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_active_theme_is_the_project_theme(): void {
		self::assertSame( 'a8csp-project-template', get_stylesheet() );
		self::assertSame( 'a8csp-project-template', get_template() );
	}

	/**
	 * Confirms the theme bootstrap functions and hooks are registered.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_setup_side_effects_registered(): void {
		self::assertTrue( \function_exists( 'a8csp_template_theme_get_asset_meta' ) );
		self::assertNotFalse( has_action( 'after_setup_theme', 'a8csp_template_theme_setup' ) );
		self::assertNotFalse( has_action( 'wp_enqueue_scripts', 'a8csp_template_theme_enqueue_assets' ) );
	}

	/**
	 * Confirms the front-end hook enqueues the project theme stylesheet.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_stylesheet_enqueued_on_wp_enqueue_scripts(): void {
		do_action( 'wp_enqueue_scripts' );

		self::assertTrue( wp_style_is( 'a8csp-project-template-style', 'enqueued' ) );
	}
}
