<?php declare( strict_types=1 );
/**
 * Integration coverage for the project features loader.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * The project features loader registers enabled features and skips disabled fixtures.
 */
final class FeaturesLoaderTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Confirms the Book post type is registered.
	 *
	 * @return  void
	 */
	public function test_book_post_type_is_registered(): void {
		$this->skip_when_features_plugin_disabled();

		self::assertTrue( post_type_exists( 'a8csp_template_book' ) );
	}

	/**
	 * Confirms the Book post type archive link uses the `/book/` pretty-permalink path.
	 *
	 * @return  void
	 */
	public function test_book_post_type_archive_link_uses_book_path(): void {
		$this->skip_when_features_plugin_disabled();

		self::assertMatchesRegularExpression( '#/book/?$#', (string) get_post_type_archive_link( 'a8csp_template_book' ) );
	}

	/**
	 * Confirms the Book archive rewrite rule is present in the site's rewrite table.
	 *
	 * The rule only lands after a rewrite flush that ran with the CPT registered; enabling the
	 * features plugin without that flush is the exact production 404 the CPT file's teardown
	 * recipe warns about.
	 *
	 * @return  void
	 */
	public function test_book_archive_rewrite_rule_is_in_the_rewrite_table(): void {
		$this->skip_when_features_plugin_disabled();

		$rewrite_rules = get_option( 'rewrite_rules' );

		self::assertIsArray( $rewrite_rules );
		self::assertArrayHasKey( 'book/?$', $rewrite_rules );
	}

	/**
	 * Confirms underscore-prefixed fixtures are not loaded.
	 *
	 * @return  void
	 */
	public function test_underscore_prefixed_fixture_is_not_loaded(): void {
		$this->skip_when_features_plugin_disabled();

		// The fixture's presence is this test's own validity precondition.
		self::assertFileExists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/includes/_loader-opt-out-example.php' );
		self::assertFalse( \function_exists( 'a8csp_template_features_add_example_body_class' ) );
	}

	/**
	 * Skips the calling test when the features plugin is disabled. The check reads the `.disabled`
	 * marker rather than a loaded function, so an enabled plugin that fails to load runs the tests.
	 *
	 * @return  void
	 */
	private function skip_when_features_plugin_disabled(): void {
		if ( \file_exists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/.disabled' ) ) {
			self::markTestSkipped( 'The features plugin is disabled (mu-plugins/a8csp-project-template-features/.disabled); delete that file to enable it.' );
		}
	}
}
