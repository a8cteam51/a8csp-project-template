<?php declare( strict_types=1 );
/**
 * Provides theme helpers and deterministic include loading.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

// Sharing this helper would couple the replaceable theme to the features plugin, so each component
// carries a copy that disappears with it.

/**
 * Gets an asset's generated metadata and optional extra dependencies.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string             $asset_path         Asset path relative to the stylesheet directory,
 *                                                 or an absolute path within it.
 * @param   array<string>|null $extra_dependencies Optional extra dependency handles.
 *
 * @return  array{version:string,dependencies:array<string>}|null Asset metadata, or null if missing.
 */
function a8csp_template_theme_get_asset_meta(
	string $asset_path,
	?array $extra_dependencies = null
): ?array {
	$stylesheet_directory = trailingslashit( get_stylesheet_directory() );
	$asset_path           = \str_starts_with( $asset_path, $stylesheet_directory )
		? $asset_path
		: get_theme_file_path( $asset_path );

	if ( ! \file_exists( $asset_path ) ) {
		return null;
	}

	$asset_meta = array(
		'dependencies' => array(),
		'version'      => (string) \filemtime( $asset_path ),
	);

	$asset_pathinfo              = \pathinfo( $asset_path );
	$asset_pathinfo['dirname'] ??= '';

	$asset_meta_file = "{$asset_pathinfo['dirname']}/{$asset_pathinfo['filename']}.asset.php";

	if ( \file_exists( $asset_meta_file ) ) {
		$asset_meta_generated = require $asset_meta_file;

		if ( \is_array( $asset_meta_generated ) ) {
			if ( \is_string( $asset_meta_generated['version'] ?? null ) ) {
				$asset_meta['version'] = $asset_meta_generated['version'];
			}

			if ( \is_array( $asset_meta_generated['dependencies'] ?? null ) ) {
				$asset_meta['dependencies'] = \array_values(
					\array_filter( $asset_meta_generated['dependencies'], 'is_string' )
				);
			}
		}
	}

	if ( \is_array( $extra_dependencies ) ) {
		$asset_meta['dependencies'] = \array_merge( $asset_meta['dependencies'], $extra_dependencies );
		$asset_meta['dependencies'] = \array_unique( $asset_meta['dependencies'] );
	}

	return $asset_meta;
}

/**
 * Gets the theme slug.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string Theme slug.
 */
function a8csp_template_theme_get_slug(): string {
	return 'a8csp-project-template';
}

$a8csp_template_theme_include_files = \glob( __DIR__ . '/includes/*.php' );

if ( false === $a8csp_template_theme_include_files ) {
	$a8csp_template_theme_include_files = array();
}

// Byte sorting lets include filenames define a predictable bootstrap order on every locale.
\sort( $a8csp_template_theme_include_files );

foreach ( $a8csp_template_theme_include_files as $a8csp_template_theme_include_file ) {
	// An underscore reserves a support file for explicit inclusion without complicating the loader.
	if ( \str_starts_with( \basename( $a8csp_template_theme_include_file ), '_' ) ) {
		continue;
	}

	require_once $a8csp_template_theme_include_file;
}
