<?php declare( strict_types=1 );
/**
 * Integration coverage for the project components' asset contracts.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

/**
 * The project theme and features plugin expose their asset contracts.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
final class AssetsTest extends \PHPUnit\Framework\TestCase {
	/**
	 * Confirms a Book archive request enqueues the archive stylesheet and a non-archive request does not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_archive_request_enqueues_the_archive_style(): void {
		$this->skip_when_features_plugin_disabled();

		self::assertNotFalse( has_action( 'wp_enqueue_scripts', 'a8csp_template_features_enqueue_book_post_type_assets' ) );

		$previous_query = $GLOBALS['wp_query'];

		$GLOBALS['wp_query'] = new \WP_Query(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below; the callback reads the query context.
		a8csp_template_features_enqueue_book_post_type_assets();
		self::assertFalse( wp_style_is( 'a8csp-project-template-features-book-archive', 'enqueued' ) );

		$GLOBALS['wp_query'] = new \WP_Query( array( 'post_type' => 'a8csp_template_book' ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below; the callback reads the query context.
		a8csp_template_features_enqueue_book_post_type_assets();
		self::assertTrue( wp_style_is( 'a8csp-project-template-features-book-archive', 'enqueued' ) );

		$GLOBALS['wp_query'] = $previous_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restores the environment's own query.
	}

	/**
	 * Confirms the enqueued theme script carries the generated asset file's metadata.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_enqueued_theme_script_uses_the_generated_asset_metadata(): void {
		do_action( 'wp_enqueue_scripts' );

		$generated_asset_meta = require get_theme_file_path( 'assets/js/build/index.asset.php' );
		$registered_script    = wp_scripts()->registered['a8csp-project-template-script'] ?? null;

		self::assertInstanceOf( \_WP_Dependency::class, $registered_script );
		self::assertSame( $generated_asset_meta['version'], $registered_script->ver );
		self::assertSame( $generated_asset_meta['dependencies'], $registered_script->deps );
	}

	/**
	 * Confirms the enqueued theme stylesheet uses its modification time as the asset version.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_stylesheet_uses_file_modification_time_as_asset_version(): void {
		do_action( 'wp_enqueue_scripts' );

		$registered_style = wp_styles()->registered['a8csp-project-template-style'] ?? null;

		self::assertInstanceOf( \_WP_Dependency::class, $registered_style );
		self::assertSame( (string) \filemtime( get_theme_file_path( 'style.css' ) ), $registered_style->ver );
	}

	/**
	 * Confirms the WooCommerce-conditional style never reaches the registry without WooCommerce active.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_woocommerce_conditional_style_is_not_registered_without_woocommerce(): void {
		self::assertFalse( \class_exists( 'WooCommerce' ) );

		do_action( 'wp_enqueue_scripts' );

		self::assertFalse( wp_style_is( 'a8csp-project-template-woocommerce-cart', 'registered' ) );
		self::assertFalse( wp_style_is( 'a8csp-project-template-woocommerce-cart', 'enqueued' ) );
	}

	/**
	 * Confirms the Book cover reminder enqueues on the Book editor screen and stays off other screens.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_cover_reminder_enqueues_on_the_book_editor_screen(): void {
		$this->skip_when_features_plugin_disabled();

		self::assertNotFalse( has_action( 'enqueue_block_editor_assets', 'a8csp_template_features_enqueue_book_cover_reminder_script' ) );

		$this->set_editor_screen( 'post' );
		a8csp_template_features_enqueue_book_cover_reminder_script();
		self::assertFalse( wp_script_is( 'a8csp-project-template-features-book-cover-reminder', 'enqueued' ) );

		$this->set_editor_screen( 'a8csp_template_book' );
		a8csp_template_features_enqueue_book_cover_reminder_script();
		self::assertTrue( wp_script_is( 'a8csp-project-template-features-book-cover-reminder', 'enqueued' ) );

		$GLOBALS['current_screen'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restores the front-end context the suite boots in.
	}

	/**
	 * Confirms the Book cover-reminder script's metadata comes from its generated asset file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_cover_reminder_metadata_comes_from_generated_asset_file(): void {
		$this->skip_when_features_plugin_disabled();

		$this->set_editor_screen( 'a8csp_template_book' );
		a8csp_template_features_enqueue_book_cover_reminder_script();

		$generated_asset_meta = require \constant( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH' ) . 'assets/js/build/book-cover-reminder.asset.php';
		$registered_script    = wp_scripts()->registered['a8csp-project-template-features-book-cover-reminder'] ?? null;

		self::assertInstanceOf( \_WP_Dependency::class, $registered_script );
		self::assertSame( $generated_asset_meta['version'], $registered_script->ver );
		self::assertSame( $generated_asset_meta['dependencies'], $registered_script->deps );

		$GLOBALS['current_screen'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restores the front-end context the suite boots in.
	}

	/**
	 * Puts the test in an admin editor screen context for the given screen id.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string $screen_id Admin screen id; a post type key yields that type's editor screen.
	 *
	 * @return  void
	 */
	private function set_editor_screen( string $screen_id ): void {
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';

		set_current_screen( $screen_id );
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
