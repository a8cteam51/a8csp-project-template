<?php declare( strict_types=1 );
/**
 * Integration coverage for the must-use plugin loader.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * The mu-loader loads header-bearing entries and reports exactly the running ones.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
final class MuLoaderTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Confirms the admin plugins list reports the features plugin with its parsed header data.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
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
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_the_loader_does_not_report_itself(): void {
		$plugins = apply_filters( 'plugins_list', array( 'mustuse' => array() ) );

		self::assertArrayNotHasKey( 'mu-loader.php', $plugins['mustuse'] );
	}

	/**
	 * Confirms headerless support files are never reported as must-use plugins.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_headerless_support_files_are_not_reported(): void {
		$plugins = apply_filters( 'plugins_list', array( 'mustuse' => array() ) );

		$support_files = \array_filter( \array_keys( $plugins['mustuse'] ), static fn ( string $file ): bool => \str_contains( $file, 'functions.php' ) );
		self::assertSame( array(), $support_files );
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
