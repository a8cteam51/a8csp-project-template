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
	 * Confirms the theme registers its block editor stylesheet.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_editor_style_registered(): void {
		self::assertTrue( current_theme_supports( 'editor-style' ) );
		self::assertContains( 'style-editor.css', $GLOBALS['editor_styles'] ?? array() );
	}

	/**
	 * Confirms the front-end hook enqueues the project theme stylesheet and script, with RTL support.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_assets_enqueued_on_wp_enqueue_scripts(): void {
		do_action( 'wp_enqueue_scripts' );

		self::assertTrue( wp_style_is( 'a8csp-project-template-style', 'enqueued' ) );
		self::assertTrue( wp_script_is( 'a8csp-project-template-script', 'enqueued' ) );
		self::assertSame( 'replace', wp_styles()->get_data( 'a8csp-project-template-style', 'rtl' ) );
	}

	/**
	 * Confirms the theme's current-year block binding source is registered.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_dynamic_content_binding_is_registered(): void {
		self::assertTrue(
			\WP_Block_Bindings_Registry::get_instance()->is_registered( 'a8csp-project-template/current-year' )
		);
	}
}
