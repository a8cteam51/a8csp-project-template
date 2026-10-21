<?php declare( strict_types=1 );
/**
 * Integration coverage for the must-use plugin loader.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * The mu-loader loads header-bearing entries and reports exactly the running ones.
 */
final class MuLoaderTest extends \PHPUnit\Framework\TestCase {
	// region TESTS.

	/**
	 * Confirms the admin plugins list reports the features plugin with its parsed header data.
	 *
	 * @return  void
	 */
	public function test_features_plugin_is_reported_on_the_plugins_screen(): void {
		$this->skip_when_features_plugin_disabled();

		$plugins = apply_filters( 'plugins_list', array( 'mustuse' => array() ) );

		self::assertArrayHasKey( 'a8csp-project-template-features/a8csp-project-template-features.php', $plugins['mustuse'] );
		self::assertSame( 'A8CSP Project Template Features', $plugins['mustuse']['a8csp-project-template-features/a8csp-project-template-features.php']['Name'] );
	}

	/**
	 * Confirms the loader does not report itself as a must-use plugin.
	 *
	 * @return  void
	 */
	public function test_the_loader_does_not_report_itself(): void {
		// Core's list holds every PHP file in mu-plugins/, the loader included.
		$plugins = apply_filters( 'plugins_list', array( 'mustuse' => array( 'mu-loader.php' => array() ) ) );

		self::assertArrayNotHasKey( 'mu-loader.php', $plugins['mustuse'] );
	}

	/**
	 * Confirms headerless support files are never reported as must-use plugins.
	 *
	 * @return  void
	 */
	public function test_headerless_support_files_are_not_reported(): void {
		$plugins = apply_filters( 'plugins_list', array( 'mustuse' => array() ) );

		$support_files = \array_filter( \array_keys( $plugins['mustuse'] ), static fn ( string $file ): bool => \str_contains( $file, 'functions.php' ) );
		self::assertSame( array(), $support_files );
	}

	/**
	 * Confirms a must-use plugin carrying a `.disabled` marker is left off the plugins screen. The
	 * loader reads the marker when the list is filtered, so the test can place it for its own run;
	 * it removes the marker only if it created it, leaving a generated site's marker in place.
	 *
	 * @return  void
	 */
	public function test_disabled_plugin_is_not_reported(): void {
		$marker  = WPMU_PLUGIN_DIR . '/a8csp-project-template-features/.disabled';
		$created = ! \file_exists( $marker );
		if ( $created ) {
			\touch( $marker ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch -- a marker file on the local mount; WP_Filesystem's credential handling has no role in a test run.
		}

		try {
			$plugins = apply_filters( 'plugins_list', array( 'mustuse' => array() ) );

			self::assertArrayNotHasKey( 'a8csp-project-template-features/a8csp-project-template-features.php', $plugins['mustuse'] );
		} finally {
			if ( $created ) {
				wp_delete_file( $marker );
			}
		}
	}

	/**
	 * Confirms a directory without the site's prefix is not loaded, even with a `Plugin Name`
	 * header: its owner loads it, and a second load fatals on redeclared functions. The loader ran
	 * at boot, before the fixture existed, so the test runs it again and then restores the plugins
	 * list filter that the second run registers.
	 *
	 * @return  void
	 */
	public function test_directory_without_the_site_prefix_is_not_loaded(): void {
		// No hyphen, so no `<repository-name>-*` scan can match it, whatever the site is called.
		$fixture_dir  = WPMU_PLUGIN_DIR . '/mu_loader_test_unprefixed';
		$fixture_file = $fixture_dir . '/mu_loader_test_unprefixed.php';
		wp_mkdir_p( $fixture_dir );
		\file_put_contents( $fixture_file, "<?php\n/**\n * Plugin Name: MU Loader Test Unprefixed\n */\n\\define( 'A8CSP_TEMPLATE_MU_LOADER_TEST_UNPREFIXED_LOADED', true );\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- a fixture on the local mount; WP_Filesystem's credential handling has no role in a test run.

		$plugins_list_hook = clone $GLOBALS['wp_filter']['plugins_list'];
		try {
			require WPMU_PLUGIN_DIR . '/mu-loader.php';

			self::assertFalse( \defined( 'A8CSP_TEMPLATE_MU_LOADER_TEST_UNPREFIXED_LOADED' ) );
		} finally {
			$GLOBALS['wp_filter']['plugins_list'] = $plugins_list_hook; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restores the filter the boot-time run registered.
			wp_delete_file( $fixture_file );
			\rmdir( $fixture_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- removes the fixture directory created above.
		}
	}

	// endregion.

	// region HELPERS.

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

	// endregion.
}
