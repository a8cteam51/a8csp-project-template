<?php declare( strict_types=1 );
/**
 * The A8CSP Project Template Features bootstrap file.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:             A8CSP Project Template Features
 * Description:             Houses the custom functionality that makes this site more than a WordPress theme -- the features that outlive any redesign.
 * Version:                 1.0.0
 * Requires at least:       7.0
 * Requires PHP:            8.5
 * Author:                  A8C Special Projects
 * Author URI:              https://specialprojects.automattic.com/
 * License:                 GPL-2.0-or-later
 * License URI:             https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:             a8csp-project-template-features
 * Domain Path:             /languages
 */

\defined( 'ABSPATH' ) || exit;

\define( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH', plugin_dir_path( __FILE__ ) );
\define( 'A8CSP_TEMPLATE_FEATURES_DIR_URL', plugin_dir_url( __FILE__ ) );
\define( 'A8CSP_TEMPLATE_FEATURES_REQUIRED_WP_VERSION', '7.0' );
\define( 'A8CSP_TEMPLATE_FEATURES_REQUIRED_PHP_VERSION', '8.5' );

require_once __DIR__ . '/functions.php';

// Core registers the active theme's languages/ path for just-in-time loading but no mu-plugin path.
// These committed translations would otherwise never load; this call only registers their path,
// while actual translation loading stays just-in-time.
load_muplugin_textdomain( 'a8csp-project-template-features', 'a8csp-project-template-features/languages' );

if (
	! is_wp_version_compatible( A8CSP_TEMPLATE_FEATURES_REQUIRED_WP_VERSION )
	|| ! is_php_version_compatible( A8CSP_TEMPLATE_FEATURES_REQUIRED_PHP_VERSION )
) {
	/**
	 * Displays the component requirements when the current site is below a version floor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	$a8csp_template_features_requirements_notice = static function (): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$message = wp_sprintf(
			/* translators: 1: Component name, 2: required WordPress version, 3: required PHP version, 4: current WordPress version, 5: current PHP version. */
			__(
				'%1$s requires WordPress %2$s and PHP %3$s at minimum. Current: WordPress %4$s, PHP %5$s.',
				'a8csp-project-template-features'
			),
			'A8CSP Project Template Features',
			A8CSP_TEMPLATE_FEATURES_REQUIRED_WP_VERSION,
			A8CSP_TEMPLATE_FEATURES_REQUIRED_PHP_VERSION,
			get_bloginfo( 'version' ),
			PHP_VERSION
		);

		wp_admin_notice(
			$message,
			array(
				'type'        => 'error',
				'dismissible' => false,
			)
		);
	};

	add_action( 'admin_notices', $a8csp_template_features_requirements_notice );

	return;
}

// Include the rest of the plugin's files.
$a8csp_template_features_include_files = \glob( __DIR__ . '/includes/*.php' ) ?: array();
\sort( $a8csp_template_features_include_files );

foreach ( $a8csp_template_features_include_files as $a8csp_template_features_include_file ) {
	// An underscore reserves a support file for explicit inclusion without complicating the loader.
	if ( \str_starts_with( \basename( $a8csp_template_features_include_file ), '_' ) ) {
		continue;
	}

	require_once $a8csp_template_features_include_file;
}
