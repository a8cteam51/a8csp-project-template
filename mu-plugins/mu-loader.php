<?php declare( strict_types=1 );
/**
 * Loads directory-shaped must-use plugins.
 *
 * WordPress runs the PHP files sitting directly in `mu-plugins/` and ignores its subdirectories.
 * This file loads every `mu-plugins/<dir>/<file>.php` that declares a `Plugin Name` header, and
 * lists what it loaded on the admin plugins screen — except plugins gated off by a sibling
 * `.disabled` file, which load and self-gate but are not running anything worth listing. It skips
 * the directories an environment places there and loads itself.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Loads eligible MU plugins and registers their admin-list metadata.
 *
 * @return  void
 */
( static function (): void {
	$candidate_files = \glob( WPMU_PLUGIN_DIR . '/*/*.php' ) ?: array();
	\sort( $candidate_files );

	// Directory mu-plugins something else loads: Studio's SQLite driver (via db.php) and the Studio
	// preview host's companion. Loading them here as well redeclares their functions.
	$foreign_dirs = array( 'sqlite-database-integration', 'studio-demo-site-companion' );

	$plugin_files = array();
	foreach ( $candidate_files as $candidate_file ) {
		$candidate_dir = \basename( \dirname( $candidate_file ) );
		foreach ( $foreign_dirs as $foreign_dir ) {
			// A prefix match also covers a host's suffixed variants; strpos() because this file runs below the PHP floor.
			if ( 0 === \strpos( $candidate_dir, $foreign_dir ) ) {
				continue 2;
			}
		}

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
			if ( is_multisite() && ! is_network_admin() ) {
				return $plugins;
			}

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
