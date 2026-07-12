<?php declare( strict_types=1 );
/**
 * Integration coverage for the project features loader.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * The project features loader registers enabled features and skips disabled fixtures.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
final class FeaturesLoaderTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Confirms the Book post type is registered.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_post_type_is_registered(): void {
		self::assertTrue( post_type_exists( 'book' ) );
	}

	/**
	 * Confirms the Book post type archive link resolves.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_post_type_archive_link_resolves(): void {
		self::assertNotFalse( get_post_type_archive_link( 'book' ) );
	}

	/**
	 * Confirms underscore-prefixed fixtures are not loaded.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_underscore_prefixed_fixture_is_not_loaded(): void {
		self::assertFalse( \function_exists( 'a8csp_template_features_disabled_example' ) );
	}
}
