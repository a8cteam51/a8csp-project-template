<?php declare( strict_types=1 );
/**
 * Registers the Book post type and its front-end styles as a worked feature.
 *
 * To remove this worked example, delete this file; delete `assets/css/src/book-archive.scss`
 * and `assets/css/src/book-singular.scss` plus their built counterparts,
 * `assets/css/build/book-archive.css` and `assets/css/build/book-singular.css`; and delete the
 * Playwright book-specific assertions in `tests/EndToEnd/site-smoke.spec.js`. Because this changes
 * the site's rewrite rules, run `wp rewrite flush` once against production after deploying any
 * change to the CPT's rewrite args. Adding or removing this feature both qualify.
 *
 * This file registers the Book CPT on the real `init` action with `show_in_rest => true`, and the
 * end-to-end test tier depends on both of those, so removing either is a test-visible break.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Registers the Book post type.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
function a8csp_template_features_register_book_post_type(): void {
	$labels = array(
		'name'               => _x( 'Books', 'Post type general name', 'a8csp-project-template-features' ),
		'singular_name'      => _x( 'Book', 'Post type singular name', 'a8csp-project-template-features' ),
		'menu_name'          => _x( 'Books', 'Admin menu text', 'a8csp-project-template-features' ),
		'all_items'          => __( 'All Books', 'a8csp-project-template-features' ),
		'view_item'          => __( 'View Book', 'a8csp-project-template-features' ),
		'add_new_item'       => __( 'Add New Book', 'a8csp-project-template-features' ),
		'add_new'            => __( 'Add New', 'a8csp-project-template-features' ),
		'edit_item'          => __( 'Edit Book', 'a8csp-project-template-features' ),
		'update_item'        => __( 'Update Book', 'a8csp-project-template-features' ),
		'search_items'       => __( 'Search Books', 'a8csp-project-template-features' ),
		'not_found'          => __( 'No books found.', 'a8csp-project-template-features' ),
		'not_found_in_trash' => __( 'No books found in Trash.', 'a8csp-project-template-features' ),
	);

	register_post_type(
		'book',
		array(
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'book',
				'with_front' => false,
			),
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'capability_type'    => 'post',
			'publicly_queryable' => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'menu_icon'          => 'dashicons-book',
		)
	);
}
add_action( 'init', 'a8csp_template_features_register_book_post_type' );

/**
 * Enqueues the Book post type's front-end assets.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  void
 */
function a8csp_template_features_enqueue_book_post_type_assets(): void {
	$slug = a8csp_template_features_get_slug();

	// Generated files are optional because asset compilation is a separate deployment step.
	if ( is_post_type_archive( 'book' ) ) {
		$archive_style_meta = a8csp_template_features_get_asset_meta( 'assets/css/build/book-archive.css' );

		if ( null !== $archive_style_meta ) {
			wp_enqueue_style(
				"{$slug}-book-archive",
				\constant( 'A8CSP_TEMPLATE_FEATURES_DIR_URL' ) . 'assets/css/build/book-archive.css',
				$archive_style_meta['dependencies'],
				$archive_style_meta['version']
			);
		}
	}

	if ( is_singular( 'book' ) ) {
		$singular_style_meta = a8csp_template_features_get_asset_meta( 'assets/css/build/book-singular.css' );

		if ( null !== $singular_style_meta ) {
			wp_enqueue_style(
				"{$slug}-book-singular",
				\constant( 'A8CSP_TEMPLATE_FEATURES_DIR_URL' ) . 'assets/css/build/book-singular.css',
				$singular_style_meta['dependencies'],
				$singular_style_meta['version']
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'a8csp_template_features_enqueue_book_post_type_assets' );
