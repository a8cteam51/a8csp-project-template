<?php declare( strict_types=1 );
/**
 * Demonstrates the feature loader's underscore opt-out mechanism.
 *
 * The features bootstrap automatically loads PHP files in `includes/` unless their basenames
 * start with an underscore. This file stays disabled because its basename starts with `_`.
 * Renaming this file to drop the leading underscore is what makes it load.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 * @author   A8C Special Projects
 * @license  GPL-2.0-or-later
 */

\defined( 'ABSPATH' ) || exit;

/**
 * Adds a feature marker to the front-end body classes.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   array<string> $classes Body class names.
 *
 * @return  array<string> Filtered body class names.
 */
function a8csp_template_features_disabled_example( array $classes ): array {
	$classes[] = 'has-a8csp-template-features';

	return $classes;
}
add_filter( 'body_class', 'a8csp_template_features_disabled_example' );
