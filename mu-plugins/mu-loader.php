<?php declare( strict_types=1 );
/**
 * Loads directory-shaped must-use plugins.
 *
 * Only `.php` files at `mu-plugins/<dir>/<file>.php` whose top comment block declares a non-empty
 * `Plugin Name` header are candidates; nothing else is scanned or loaded.
 * Discovery reads only the `Plugin Name` header with get_file_data(), avoiding the admin-only
 * metadata parser on ordinary requests.
 * The candidate list is sorted before being scanned once per request and shared by the load loop
 * and admin-list callback through closure capture.
 * Full metadata parsing with get_plugin_data() happens only inside the admin plugins-list callback,
 * which loads the admin parser lazily when needed.
 * Admin-list entries use plugin_basename() keys so identically named files in different plugin
 * directories cannot collide.
 * Every discovered candidate is loaded once, and the loader replaces its own admin-list entry with
 * the discovered plugin entries.
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
	$candidate_files = \glob( WPMU_PLUGIN_DIR . '/*/*.php' );

	if ( false === $candidate_files ) {
		$candidate_files = array();
	}

	\sort( $candidate_files );

	$plugin_files = array();

	foreach ( $candidate_files as $candidate_file ) {
		$headers = get_file_data(
			$candidate_file,
			array( 'Name' => 'Plugin Name' )
		);

		if (
			! isset( $headers['Name'] )
			|| ! \is_string( $headers['Name'] )
			|| '' === $headers['Name']
		) {
			continue;
		}

		$plugin_files[] = $candidate_file;
	}

	foreach ( $plugin_files as $plugin_file ) {
		require_once $plugin_file;
	}

	/**
	 * Replaces the loader entry with metadata for the discovered MU plugins.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array $plugins Plugin groups keyed by status and plugin basename.
	 *
	 * @return  array Filtered plugin groups.
	 */
	$add_plugins_to_list = static function ( array $plugins ) use ( $plugin_files ): array {
		if ( ! isset( $plugins['mustuse'] ) || ! \is_array( $plugins['mustuse'] ) ) {
			$plugins['mustuse'] = array();
		}

		unset( $plugins['mustuse'][ plugin_basename( __FILE__ ) ] );

		if ( ! \function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $plugin_files as $plugin_file ) {
			$plugins['mustuse'][ plugin_basename( $plugin_file ) ] = get_plugin_data(
				$plugin_file,
				false,
				false
			);
		}

		return $plugins;
	};

	add_filter( 'plugins_list', $add_plugins_to_list );
} )();
