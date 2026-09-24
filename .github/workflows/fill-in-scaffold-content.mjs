import { access, readFile, unlink, writeFile } from 'fs/promises';
import process from 'process';

// A newline-joined block of source lines. Manifest spans are authored line by line so leading tabs
// and blank comment lines (`\t *`) are unambiguous in this file's own source.
const block = ( ...lines ) => lines.join( '\n' );

// The teaching-content strip manifest. It runs as the FIRST scaffold phase, BEFORE fill-in-scaffold.mjs
// renames the tree and substitutes every identifier, so each `from` is matched against the pristine
// template source. Spans therefore carry template-time paths (themes/a8csp-project-template/...,
// mu-plugins/a8csp-project-template-features/...) and may freely contain substitutable tokens
// (a8csp-project-template, a8csp_template, A8CSP Project Template, EXAMPLE_REPO_*); the later
// substitution pass rewrites those tokens in whatever text survives here, replacement prose included.
// Two entry shapes:
//   { action: 'replace-exact', path, from, to } — `from` must occur EXACTLY ONCE in `path`.
//   { action: 'delete', path }                  — `path` must exist.
// Each replacement rewrites a teaching passage into the contract-level documentation a production
// site repo would carry; load-bearing constraint one-liners are left untouched by omission.
const MANIFEST = [
	// README.scaffold.md becomes the generated repo's README.
	{
		action: 'replace-exact',
		path: 'README.scaffold.md',
		from: block(
			"Generation disables the features mu-plugin by creating `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`, so an unattended site never registers the example content. Delete that file to start building the site's features — the worked Book example (CPT, front-end styles, block-editor panel, and Book Count block) lights up on the next request, its pretty-permalink routes (`/book/...`) resolve after one rewrite flush (restarting wp-env re-runs the `afterStart` flush; production needs `wp rewrite flush` once, as documented in the CPT file), and the Book-specific tests in `tests/Integration/FeaturesLoaderTest.php`, `tests/Integration/AssetsTest.php`, `tests/Integration/BookCountBlockTest.php`, and `tests/EndToEnd/site-smoke.spec.js` (self-skipped while `.disabled` is present, apart from the block's disabled-state check) start running and passing. That's the fastest way to see a real feature move through the site end to end before writing your own."
		),
		to: block(
			"Generation disables the features mu-plugin by creating `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`, so an unattended site never registers the example content. Delete that file to start building the site's features — the Book feature (CPT, front-end styles, block-editor panel, and Book Count block) registers on the next request, its pretty-permalink routes (`/book/...`) resolve after one rewrite flush (restarting wp-env re-runs the `afterStart` flush; production needs `wp rewrite flush` once, as documented in the CPT file), and the Book-specific tests in `tests/Integration/FeaturesLoaderTest.php`, `tests/Integration/AssetsTest.php`, `tests/Integration/BookCountBlockTest.php`, and `tests/EndToEnd/site-smoke.spec.js` self-skip while `.disabled` is present and run once it is removed, apart from the block's disabled-state check, which runs only while the file is present."
		),
	},
	{
		action: 'replace-exact',
		path: 'README.scaffold.md',
		from: block(
			'The theme also ships two worked examples that are safe to delete independently; follow the teardown lines co-located in `themes/EXAMPLE_REPO_SLUG/includes/plugin-woocommerce.php` and `themes/EXAMPLE_REPO_SLUG/includes/theme-dynamic-content.php`.'
		),
		to: block(
			"The theme's WooCommerce cart styling and dynamic-content binding are each independently removable; follow the teardown lines co-located in `themes/EXAMPLE_REPO_SLUG/includes/plugin-woocommerce.php` and `themes/EXAMPLE_REPO_SLUG/includes/theme-dynamic-content.php`."
		),
	},
	{
		action: 'replace-exact',
		path: 'README.scaffold.md',
		from: block(
			"The Book Count block (`blocks/src/book-count/`) shows that exception end to end: it's an example to learn from, not an invitation to keep blocks here. It goes with the rest of Book (see the teardown one-liners below)."
		),
		to: block(
			'The Book Count block (`blocks/src/book-count/`) is listed there and goes with the rest of the Book feature (see the teardown one-liners below).'
		),
	},

	// tests/README.md ships as-is into generated repositories.
	{
		action: 'replace-exact',
		path: 'tests/README.md',
		from: block(
			'Generated repositories get their own two-port block, derived from the repository name at',
			'generation, so projects started side by side rarely contend for the same host ports — the',
			"hash is collision-reducing, not unique. If two environments collide on one machine, wp-env's",
			'untracked override files take local precedence (`.wp-env.override.json`; the tests config pairs with `.wp-env.tests.override.json`).'
		),
		to: block(
			"This repository's two-port block is derived from the repository name, so projects started side by",
			'side rarely contend for the same host ports — the hash is collision-reducing, not unique. If two',
			"environments collide on one machine, wp-env's untracked override files take local precedence",
			'(`.wp-env.override.json`; the tests config pairs with `.wp-env.tests.override.json`).'
		),
	},

	// The features bootstrap entry file.
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php',
		from: block(
			'// Generation creates the .disabled file so an unattended site never registers the example content.',
			"// Delete .disabled to start building the site's features."
		),
		to: block(
			'// A .disabled file beside this bootstrap short-circuits the features plugin so an unattended site',
			"// registers nothing; delete it to enable the site's features."
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php',
		from: block(
			'\t// Misconfiguration speaks: the notice names the component, its floors, and the actual versions.',
			'\tadd_action('
		),
		to: block( '\tadd_action(' ),
	},

	// The underscore opt-out sample and its coupled test.
	{
		action: 'delete',
		path: 'mu-plugins/a8csp-project-template-features/includes/_loader-opt-out-example.php',
	},
	{
		action: 'replace-exact',
		path: 'tests/Integration/FeaturesLoaderTest.php',
		from: block(
			' * The project features loader registers enabled features and skips disabled fixtures.'
		),
		to: block(
			" * The project features loader registers the site's enabled features."
		),
	},
	{
		action: 'replace-exact',
		path: 'tests/Integration/FeaturesLoaderTest.php',
		from: block(
			'\t/**',
			'\t * Confirms underscore-prefixed fixtures are not loaded.',
			'\t *',
			'\t * @return  void',
			'\t */',
			'\tpublic function test_underscore_prefixed_fixture_is_not_loaded(): void {',
			'\t\t$this->skip_when_features_plugin_disabled();',
			'',
			"\t\t// The fixture's presence is this test's own validity precondition.",
			"\t\tself::assertFileExists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/includes/_loader-opt-out-example.php' );",
			"\t\tself::assertFalse( \\function_exists( 'a8csp_template_features_add_example_body_class' ) );",
			'\t}',
			'',
			''
		),
		to: '',
	},

	// mu-plugins Book feature files.
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-post-type.php',
		from: block(
			' * Registers the Book post type and its front-end styles as a worked feature.'
		),
		to: block(
			' * Registers the Book post type and its front-end styles.'
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-post-type.php',
		from: block(
			' * removing the registration; unregistered content stays in the database but becomes unreachable.',
			' *',
			' * This file registers the Book CPT on the real `init` action with `show_in_rest => true`, and the',
			' * end-to-end test tier depends on both of those, so removing either breaks those tests whenever they run.',
			' *'
		),
		to: block(
			' * removing the registration; unregistered content stays in the database but becomes unreachable.',
			' *'
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-cover-reminder.php',
		from: block(
			' *',
			" * This editor-side JavaScript counterpart to `book-post-type.php`'s front-end assets depends on",
			" * the Book post type. Removing the Book CPT removes this file's reason to exist, while this file",
			' * remains independently deletable when only the editor reminder is unwanted.',
			' *',
			' * To remove this worked example, delete this file; delete `assets/js/src/book-cover-reminder.js`'
		),
		to: block(
			' *',
			' * To remove this feature, delete this file; delete `assets/js/src/book-cover-reminder.js`'
		),
	},

	// Theme includes.
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/includes/plugin-woocommerce.php',
		from: block(
			" * This is the theme's worked example of plugin-conditional code: the theme's own styling",
			' * (theme-setup.php) and small theme-owned dynamic content (theme-dynamic-content.php) never guard',
			" * on another plugin's presence, but this file exists only because WooCommerce might be. The guard",
			" * makes WooCommerce's absence silent — no notice, no fallback markup — so this file is safe to",
			' * leave in a site that never installs the plugin.'
		),
		to: block(
			" * The class-exists guard makes WooCommerce's absence silent — no notice, no fallback markup — so",
			' * this file is safe to leave in a site that never installs the plugin.'
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/includes/theme-dynamic-content.php',
		from: block(
			" * This is the theme's dynamic-content worked example: a block binding rather than a shortcode,",
			' * because a binding attaches straight to a block attribute in the editor and needs no separate',
			' * shortcode parse pass. The `patterns/footer-default.php` pattern binds a paragraph to this source.'
		),
		to: block(
			' * The `patterns/footer-default.php` pattern binds a paragraph to this source.'
		),
	},

	// Theme styles.
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/assets/css/src/cart.scss',
		from: block(
			'// A worked example of per-purpose CSS that reacts to a plugin: the two declarations stand in for a',
			"// real project's cart styles. Imitate the shape, or delete this file with the WooCommerce example",
			'// per the teardown note in includes/plugin-woocommerce.php.',
			'.woocommerce-cart {'
		),
		to: block( '.woocommerce-cart {' ),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/assets/sass/base/_motion.scss',
		from: block(
			'// A worked example of motion paired with its reduced-motion escape hatch — imitate the pairing',
			'// for any transition or animation the project adds.'
		),
		to: block(
			'// Any motion the theme adds must ship with a prefers-reduced-motion override.'
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/assets/sass/style.scss',
		from: block(
			'Description: A minimal block theme proving the sass build pipeline, not a design system — replace its contents freely; keep the directory name and text domain.'
		),
		to: block(
			"Description: The site's block theme. Replace its contents freely; the directory name and text domain are permanent."
		),
	},

	// A stripped docblock names a feature, not a worked example.
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-post-type.php',
		from: block(
			' * To remove this worked example, delete this file; delete `assets/css/src/book-archive.scss`'
		),
		to: block(
			' * To remove this feature, delete this file; delete `assets/css/src/book-archive.scss`'
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/assets/js/src/book-cover-reminder.js',
		from: block(
			' * To remove this worked example, follow the recipe in `includes/book-cover-reminder.php`.'
		),
		to: block(
			' * To remove this feature, follow the recipe in `includes/book-cover-reminder.php`.'
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/includes/plugin-woocommerce.php',
		from: block(
			' * To remove this worked example, delete this file, delete `assets/css/src/cart.scss` and its built'
		),
		to: block(
			' * To remove this feature, delete this file, delete `assets/css/src/cart.scss` and its built'
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/includes/theme-dynamic-content.php',
		from: block(
			' * To remove this worked example, delete this file, remove the bound paragraph from'
		),
		to: block(
			' * To remove this feature, delete this file, remove the bound paragraph from'
		),
	},
];

const checkOnly = process.argv.includes( '--check' );

const fileExists = async ( path ) => {
	try {
		await access( path );
		return true;
	} catch {
		return false;
	}
};

// Every entry is checked before anything is written, so a drifted span fails the run with the tree
// untouched; `--check` runs the same pass and stops there. Entries against one file apply in
// manifest order to one buffer.
const errors = [];
const buffers = new Map();
const deletes = [];
for ( const { action, path, from, to } of MANIFEST ) {
	if ( 'delete' === action ) {
		if ( await fileExists( path ) ) {
			deletes.push( path );
		} else {
			errors.push( `delete: ${ path } does not exist` );
		}
		continue;
	}

	const span = from.split( '\n' )[ 0 ];
	if ( ! buffers.has( path ) ) {
		if ( ! ( await fileExists( path ) ) ) {
			errors.push(
				`replace-exact: ${ path } does not exist for span starting "${ span }"`
			);
			continue;
		}
		buffers.set( path, await readFile( path, 'utf-8' ) );
	}

	const buffer = buffers.get( path );
	const occurrences = buffer.split( from ).length - 1;
	if ( 1 !== occurrences ) {
		errors.push(
			`replace-exact: ${ path } — span occurs ${ occurrences } times (want exactly 1): "${ span }"`
		);
		continue;
	}
	// A function replacer inserts the text verbatim; a string replacement would interpret $-patterns inside it.
	buffers.set(
		path,
		buffer.replace( from, () => to )
	);
}

if ( 0 !== errors.length ) {
	console.error(
		'fill-in-scaffold-content: manifest failed with %d violation(s):',
		errors.length
	);
	for ( const error of errors ) {
		console.error( '  - %s', error );
	}
	process.exit( 1 );
}

if ( ! checkOnly ) {
	for ( const [ path, buffer ] of buffers ) {
		await writeFile( path, buffer );
	}
	for ( const path of deletes ) {
		await unlink( path );
	}
}
