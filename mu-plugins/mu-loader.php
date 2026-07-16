<?php declare( strict_types=1 );
/**
 * Loads directory-shaped must-use plugins.
 *
 * WordPress runs the PHP files sitting directly in `mu-plugins/` and ignores its subdirectories.
 * This file loads every `mu-plugins/<dir>/<file>.php` that declares a `Plugin Name` header, and
 * lists what it loaded on the admin plugins screen -- except plugins gated off by a sibling
 * `.disabled` file, which load and self-gate but are not running anything worth listing.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Loads eligible MU plugins and registers their admin-list metadata.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
( static function (): void {
	$candidate_files = \glob( WPMU_PLUGIN_DIR . '/*/*.php' ) ?: array();
	\sort( $candidate_files );

	$plugin_files = array();
	foreach ( $candidate_files as $candidate_file ) {
		$headers = get_file_data( $candidate_file, array( 'Name' => 'Plugin Name' ) );
		if ( '' !== $headers['Name'] ) {
			$plugin_files[] = $candidate_file;
		}
	}

	foreach ( $plugin_files as $plugin_file ) {
		require_once $plugin_file;
	}

	add_filter(
		'plugins_list',
		static function ( array $plugins ) use ( $plugin_files ): array {
			if ( ! isset( $plugins['mustuse'] ) || ! \is_array( $plugins['mustuse'] ) ) {
				$plugins['mustuse'] = array();
			}

			unset( $plugins['mustuse'][ plugin_basename( __FILE__ ) ] );

			if ( ! \function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( $plugin_files as $plugin_file ) {
				// A `.disabled` plugin still loads and self-gates; listing it as an active
				// must-use plugin would misreport what the site is running.
				if ( \file_exists( \dirname( $plugin_file ) . '/.disabled' ) ) {
					continue;
				}

				$plugins['mustuse'][ plugin_basename( $plugin_file ) ] = get_plugin_data( $plugin_file, false, false );
			}

			return $plugins;
		}
	);
} )();
