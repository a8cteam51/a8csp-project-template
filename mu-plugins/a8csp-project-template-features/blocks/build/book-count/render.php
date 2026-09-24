<?php declare( strict_types=1 );
/**
 * Renders the Book Count block: the number of published Books, linked to the Book archive.
 *
 * WordPress runs this file on every render, so the count, its translation, and the archive URL
 * never freeze into post content.
 *
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

$a8csp_template_features_book_count = (int) ( wp_count_posts( 'a8csp_template_book' )->publish ?? 0 );

\printf(
	'<p %1$s><a href="%2$s">%3$s</a></p>',
	wp_kses_data( get_block_wrapper_attributes() ),
	esc_url( (string) get_post_type_archive_link( 'a8csp_template_book' ) ),
	esc_html(
		\sprintf(
			/* translators: %s: Number of published Books. */
			_n( '%s book', '%s books', $a8csp_template_features_book_count, 'a8csp-project-template-features' ),
			number_format_i18n( $a8csp_template_features_book_count )
		)
	)
);
