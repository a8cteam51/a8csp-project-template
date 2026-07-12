<?php declare( strict_types=1 );
/**
 * PHPUnit bootstrap. Inside wp-env's `cli` container, also loads WordPress; the mu-plugins and
 * theme under test load through WordPress itself (the mu-loader and the active theme), never
 * required directly here — requiring them would bypass the load path the suite exists to prove.
 *
 * @since    1.0.0
 * @version  1.0.0
 * @package  A8C\SpecialProjects\ProjectTemplate
 */

require_once __DIR__ . '/../vendor/autoload.php';

$a8csp_template_wp_load = '/var/www/html/wp-load.php';
if ( \file_exists( $a8csp_template_wp_load ) ) {
	require_once $a8csp_template_wp_load;
}
