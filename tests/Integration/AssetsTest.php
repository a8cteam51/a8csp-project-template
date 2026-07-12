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
	 * Confirms both front-end enqueue callbacks are registered.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_front_end_enqueue_callbacks_are_registered(): void {
		self::assertNotFalse( has_action( 'wp_enqueue_scripts', 'a8csp_template_theme_enqueue_assets' ) );
		self::assertNotFalse( has_action( 'wp_enqueue_scripts', 'a8csp_template_features_enqueue_book_post_type_assets' ) );
	}

	/**
	 * Confirms the theme script uses its generated asset version.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_script_uses_generated_asset_version(): void {
		$script_path          = get_theme_file_path( 'assets/js/build/index.js' );
		$script_meta          = a8csp_template_theme_get_asset_meta( $script_path );
		$generated_asset_meta = require get_theme_file_path( 'assets/js/build/index.asset.php' );

		self::assertSame( $generated_asset_meta['version'], $script_meta['version'] );
	}

	/**
	 * Confirms the theme stylesheet uses its modification time as the asset version.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_theme_stylesheet_uses_file_modification_time_as_asset_version(): void {
		$style_path = get_theme_file_path( 'style.css' );
		$style_meta = a8csp_template_theme_get_asset_meta( $style_path );

		self::assertSame( (string) \filemtime( $style_path ), $style_meta['version'] );
	}

	/**
	 * Confirms the WooCommerce-conditional style enqueue never registers without WooCommerce active.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_woocommerce_conditional_style_is_not_registered_without_woocommerce(): void {
		self::assertFalse( \class_exists( 'WooCommerce' ) );

		// PHP binds the file's function at compile time despite the guard; its effect is the unwired hook.
		self::assertFalse( has_action( 'wp_enqueue_scripts', 'a8csp_template_theme_enqueue_woocommerce_cart_style' ) );
	}

	/**
	 * Confirms the Book editor enqueue callback is registered.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_editor_enqueue_callback_is_registered(): void {
		self::assertNotFalse( has_action( 'enqueue_block_editor_assets', 'a8csp_template_features_enqueue_book_post_type_editor_assets' ) );
	}

	/**
	 * Confirms the Book editor script's dependencies and version come from its generated asset file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public function test_book_editor_script_dependencies_come_from_generated_asset_file(): void {
		$script_meta          = a8csp_template_features_get_asset_meta( 'assets/js/build/editor.js' );
		$generated_asset_meta = require \constant( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH' ) . 'assets/js/build/editor.asset.php';

		self::assertSame( $generated_asset_meta['version'], $script_meta['version'] );
		self::assertSame( $generated_asset_meta['dependencies'], $script_meta['dependencies'] );
	}
}
