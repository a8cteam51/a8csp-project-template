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
		$this->skip_when_features_plugin_disabled();

		self::assertTrue( post_type_exists( 'book' ) );
	}

	/**
	 * Confirms the Book post type archive link uses the `/book/` pretty-permalink path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_post_type_archive_link_uses_book_path(): void {
		$this->skip_when_features_plugin_disabled();

		self::assertMatchesRegularExpression( '#/book/?$#', (string) get_post_type_archive_link( 'book' ) );
	}

	/**
	 * Confirms the underscore-prefixed fixture file still exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_underscore_prefixed_fixture_file_exists(): void {
		self::assertFileExists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/includes/_loader-opt-out-example.php' );
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
		$this->skip_when_features_plugin_disabled();

		self::assertFalse( \function_exists( 'a8csp_template_features_add_example_body_class' ) );
	}

	/**
	 * Skips the calling test when the features plugin is disabled.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	private function skip_when_features_plugin_disabled(): void {
		if ( ! \function_exists( 'a8csp_template_features_get_slug' ) ) {
			self::markTestSkipped( 'The features plugin is disabled (mu-plugins/a8csp-project-template-features/.disabled); delete that file to enable it.' );
		}
	}
}
