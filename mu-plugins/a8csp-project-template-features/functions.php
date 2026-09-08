<?php declare( strict_types=1 );
/**
 * Provides feature-plugin helper functions.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

\defined( 'ABSPATH' ) || exit;

// region META

/**
 * Gets the features plugin slug.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  string Features plugin slug.
 */
function a8csp_template_features_get_slug(): string {
	return \basename( __DIR__ );
}

// Sharing this helper would couple the replaceable theme to the features plugin, so each component
// carries a copy that disappears with it.

/**
 * Gets an asset's generated metadata and optional extra dependencies.
 *
 * A null return marks a missing asset file, letting a component whose build output was removed
 * degrade to unstyled output instead of fataling.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string                       $asset_path         Asset path relative to the plugin
 *                                                           directory, or an absolute path within it.
 * @param   array<non-empty-string>|null $extra_dependencies Optional extra dependency handles.
 *
 * @return  array{version:string,dependencies:array<non-empty-string>}|null Asset metadata, or null if missing.
 */
function a8csp_template_features_get_asset_meta(
	string $asset_path,
	?array $extra_dependencies = null
): ?array {
	$asset_path = \str_starts_with( $asset_path, \constant( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH' ) )
		? $asset_path
		: \constant( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH' ) . $asset_path;
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
					\array_filter( $asset_meta_generated['dependencies'], static fn ( mixed $dependency ): bool => \is_string( $dependency ) && '' !== $dependency )
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

// endregion
