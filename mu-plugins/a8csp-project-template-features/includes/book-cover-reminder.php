<?php declare( strict_types=1 );
/**
 * Enqueues the Book cover-image reminder in the Book block editor.
 *
 * This editor-side JavaScript counterpart to `book-post-type.php`'s front-end assets depends on
 * the Book post type. Removing the Book CPT removes this file's reason to exist, while this file
 * remains independently deletable when only the editor reminder is unwanted.
 *
 * To remove this worked example, delete this file; delete `assets/js/src/book-cover-reminder.js`
 * and its built counterparts, `assets/js/build/book-cover-reminder.js` and
 * `assets/js/build/book-cover-reminder.asset.php`; delete the `build:features:scripts` and
 * `start:features:scripts` npm scripts; and delete
 * `test_book_cover_reminder_enqueues_on_the_book_editor_screen` and
 * `test_book_cover_reminder_metadata_comes_from_generated_asset_file` from
 * `tests/Integration/AssetsTest.php`.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the Book cover-reminder script in the block editor.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
function a8csp_template_features_enqueue_book_cover_reminder_script(): void {
	$screen = get_current_screen();
	if ( $screen instanceof \WP_Screen && 'a8csp_template_book' === $screen->post_type ) {
		$script_meta = a8csp_template_features_get_asset_meta( 'assets/js/build/book-cover-reminder.js' );
		if ( null !== $script_meta ) {
			$slug = a8csp_template_features_get_slug();
			wp_enqueue_script(
				"{$slug}-book-cover-reminder",
				\constant( 'A8CSP_TEMPLATE_FEATURES_DIR_URL' ) . 'assets/js/build/book-cover-reminder.js',
				$script_meta['dependencies'],
				$script_meta['version'],
				false
			);
			wp_set_script_translations( "{$slug}-book-cover-reminder", 'a8csp-project-template-features' );
		}
	}
}
add_action( 'enqueue_block_editor_assets', 'a8csp_template_features_enqueue_book_cover_reminder_script' );
