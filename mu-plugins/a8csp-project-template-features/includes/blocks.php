<?php declare( strict_types=1 );
/**
 * Registers the features plugin's blocks from the build's metadata manifest.
 *
 * Blocks belong in the A8C Special Projects blocks monorepo. A block that belongs to this site only
 * lives in `blocks/src/<name>/`, lists its source and built `block.json` paths in
 * `.github/blocks-allowlist`, and registers here with everything else in `blocks/build/`.
 *
 * To remove the Book Count block, delete `blocks/src/book-count/` and `blocks/build/book-count/`;
 * delete its two lines from `.github/blocks-allowlist`; delete
 * `tests/Integration/BookCountBlockTest.php`; and run `npm run build` to regenerate
 * `blocks/build/blocks-manifest.php`. With no block left in `blocks/src/`, skip that build and
 * instead delete this file and the whole `blocks/` directory; delete the `build:features:blocks`
 * and `start:features:blocks` npm scripts (with no `build:features:*` script left, also drop
 * `build:features:**` from `build` and `start:features:**` from `start`); drop the `blocks/src`
 * path from the `format:scripts` and `lint:scripts` npm scripts; and delete the `@wordpress/blocks`
 * and `@wordpress/block-editor` devDependencies, then run `npm install`. Then rewrite the prose the
 * removal leaves false, which `git grep -n -i -E 'book.?count|blocks-manifest'` finds.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Registers every block built into `blocks/build/`, reading their metadata from the generated
 * manifest instead of from one `block.json` per block.
 *
 * @return  void
 */
function a8csp_template_features_register_blocks(): void {
	wp_register_block_types_from_metadata_collection( \constant( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH' ) . 'blocks/build', \constant( 'A8CSP_TEMPLATE_FEATURES_DIR_PATH' ) . 'blocks/build/blocks-manifest.php' );
}
add_action( 'init', 'a8csp_template_features_register_blocks' );
