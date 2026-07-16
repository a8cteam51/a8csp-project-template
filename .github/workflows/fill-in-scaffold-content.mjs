import { access, readFile, unlink, writeFile } from 'fs/promises';
import { join as joinPath } from 'path';
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
			"Generation disables the features mu-plugin by creating `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`, so an unattended site never registers the example content. Delete `.disabled` (at `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`) to start building the site's features -- the worked Book example (CPT, front-end styles, and block-editor panel) lights up on the next request, its pretty-permalink routes (`/book/...`) resolve after one rewrite flush (restarting wp-env re-runs the `afterStart` flush; production needs `wp rewrite flush` once, as documented in the CPT file), and the Book-specific tests in `tests/Integration/FeaturesLoaderTest.php`, `tests/Integration/AssetsTest.php`, and `tests/EndToEnd/site-smoke.spec.js` (self-skipped while `.disabled` is present) start running and passing. That's the fastest way to see a real feature move through this template end to end before writing your own.",
		),
		to: block(
			"Generation disables the features mu-plugin by creating `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`, so an unattended site never registers the example content. Delete `.disabled` (at `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`) to start building the site's features -- the Book feature (CPT, front-end styles, and block-editor panel) registers on the next request, its pretty-permalink routes (`/book/...`) resolve after one rewrite flush (restarting wp-env re-runs the `afterStart` flush; production needs `wp rewrite flush` once, as documented in the CPT file), and the Book-specific tests in `tests/Integration/FeaturesLoaderTest.php`, `tests/Integration/AssetsTest.php`, and `tests/EndToEnd/site-smoke.spec.js` self-skip while `.disabled` is present and run once it is removed.",
		),
	},
	{
		action: 'replace-exact',
		path: 'README.scaffold.md',
		from: block(
			"The theme also ships two worked examples that are safe to delete independently; follow the teardown lines co-located in `themes/EXAMPLE_REPO_SLUG/includes/plugin-woocommerce.php` and `themes/EXAMPLE_REPO_SLUG/includes/theme-dynamic-content.php`.",
		),
		to: block(
			"The theme's WooCommerce cart styling and dynamic-content binding are each independently removable; follow the teardown lines co-located in `themes/EXAMPLE_REPO_SLUG/includes/plugin-woocommerce.php` and `themes/EXAMPLE_REPO_SLUG/includes/theme-dynamic-content.php`.",
		),
	},
	{
		action: 'replace-exact',
		path: 'README.scaffold.md',
		from: block(
			"- **Book feature:** Follow the teardown recipe at the top of `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-post-type.php`; its removal instructions live beside the self-contained worked example they remove; follow the editor-JS companion's teardown recipe at `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-cover-reminder.php`.",
		),
		to: block(
			"- **Book feature:** Follow the teardown recipe at the top of `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-post-type.php`, then the editor-JS companion's teardown recipe at `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-cover-reminder.php`.",
		),
	},

	// tests/README.md ships as-is into generated repositories.
	{
		action: 'replace-exact',
		path: 'tests/README.md',
		from: block(
			"A plugin or theme package may ship to arbitrary third-party sites and therefore needs proof below its supported version floor. This site repository deploys only to hosts the team controls, so it has no dedicated below-floor wp-env configuration or Requirements suite. The features plugin's inexpensive floor gate in `mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php` is the only runtime below-floor protection -- the Quality workflow's below-floor syntax matrix covers parse-safety separately -- and is not a separate test tier.",
		),
		to: block(
			"This site repository deploys only to hosts the team controls, so it has no dedicated below-floor wp-env configuration or Requirements suite. The features plugin's inexpensive floor gate in `mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php` is the only runtime below-floor protection -- the Quality workflow's below-floor syntax matrix covers parse-safety separately -- and is not a separate test tier.",
		),
	},
	{
		action: 'replace-exact',
		path: 'tests/README.md',
		from: block(
			"## Why plain `TestCase`, not `WP_UnitTestCase`",
			"",
			"The integration bootstrap loads WordPress itself, which is enough for this suite's boot, registration, and enqueue promises. Using `WP_UnitTestCase` would add `$this->factory` fixtures, `go_to()` routing simulation, and per-test transaction rollback, but the current tests neither build content-heavy scenarios nor persist database fixtures. Giving up those helpers costs little here and keeps the rig aligned with the narrower site contracts it exercises.",
		),
		to: block(
			"## Plain `TestCase`, not `WP_UnitTestCase`",
			"",
			"The integration bootstrap loads real WordPress, which satisfies this suite's boot, registration, and enqueue promises. The suite needs none of `WP_UnitTestCase`'s fixture factories, routing simulation, or per-test transaction rollback.",
		),
	},
	{
		action: 'replace-exact',
		path: 'tests/README.md',
		from: block(
			"Generated repositories get their own two-port block, derived from the repository name at",
			"generation, so projects started side by side rarely contend for the same host ports -- the",
			"hash is collision-reducing, not unique. If two environments collide on one machine, wp-env's",
			"untracked override files take local precedence (`.wp-env.override.json`; the tests config pairs with `.wp-env.tests.override.json`).",
		),
		to: block(
			"This repository's two-port block is derived from the repository name, so projects started side by",
			"side rarely contend for the same host ports -- the hash is collision-reducing, not unique. If two",
			"environments collide on one machine, wp-env's untracked override files take local precedence",
			"(`.wp-env.override.json`; the tests config pairs with `.wp-env.tests.override.json`).",
		),
	},
	{
		action: 'replace-exact',
		path: 'tests/README.md',
		from: block(
			"Generated repositories additionally ship `mu-plugins/<slug>-features/.disabled`, which disables the whole features plugin -- Book included -- until it's deleted. The Book-specific assertions above and the End-to-End Book smoke describe self-skip with a named message while that file is present, and resume automatically once it's removed; this template's own tree ships without the file, so its CI proves the enabled path on every run.",
		),
		to: block(
			"This repository ships `mu-plugins/<slug>-features/.disabled`, which disables the whole features plugin -- Book included -- until it's deleted. While that file is present, the Book-specific assertions above and the End-to-End Book smoke self-skip with a named message, and they resume automatically once it's removed.",
		),
	},
	{
		action: 'replace-exact',
		path: 'tests/README.md',
		from: block(
			"No Unit suite ships at generation because no shipped site code is pure logic yet; the filesystem-bound asset metadata helpers are already integration-covered through real enqueues. When the first pure function appears, add `tests/Unit/` and a `Unit` testsuite entry in `phpunit.dist.xml` for it.",
		),
		to: block(
			"No Unit suite ships yet because no shipped site code is pure logic; the filesystem-bound asset metadata helpers are integration-covered through real enqueues. Add `tests/Unit/` and a `Unit` testsuite entry in `phpunit.dist.xml` when the first pure function appears.",
		),
	},

	// The features bootstrap entry file.
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php',
		from: block(
			"// Generation creates the .disabled file so an unattended site never registers the example content.",
			"// Delete .disabled to start building the site's features.",
		),
		to: block(
			"// A .disabled file beside this bootstrap short-circuits the features plugin so an unattended site",
			"// registers nothing; delete it to enable the site's features.",
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php',
		from: block(
			"\t// Misconfiguration speaks: the notice names the component, its floors, and the actual versions.",
			"\tadd_action(",
		),
		to: block(
			"\tadd_action(",
		),
	},

	// The underscore opt-out sample and its coupled test.
	{ action: 'delete', path: 'mu-plugins/a8csp-project-template-features/includes/_loader-opt-out-example.php' },
	{
		action: 'replace-exact',
		path: 'tests/Integration/FeaturesLoaderTest.php',
		from: block(
			" * The project features loader registers enabled features and skips disabled fixtures.",
		),
		to: block(
			" * The project features loader registers the site's enabled features.",
		),
	},
	{
		action: 'replace-exact',
		path: 'tests/Integration/FeaturesLoaderTest.php',
		from: block(
			"\t/**",
			"\t * Confirms underscore-prefixed fixtures are not loaded.",
			"\t *",
			"\t * @since   1.0.0",
			"\t * @version 1.0.0",
			"\t *",
			"\t * @return  void",
			"\t */",
			"\tpublic function test_underscore_prefixed_fixture_is_not_loaded(): void {",
			"\t\t$this->skip_when_features_plugin_disabled();",
			"",
			"\t\t// The fixture's presence is this test's own validity precondition.",
			"\t\tself::assertFileExists( __DIR__ . '/../../mu-plugins/a8csp-project-template-features/includes/_loader-opt-out-example.php' );",
			"\t\tself::assertFalse( \\function_exists( 'a8csp_template_features_add_example_body_class' ) );",
			"\t}",
			"",
			"",
		),
		to: "",
	},

	// mu-plugins Book feature files.
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-post-type.php',
		from: block(
			" * Registers the Book post type and its front-end styles as a worked feature.",
		),
		to: block(
			" * Registers the Book post type and its front-end styles.",
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-post-type.php',
		from: block(
			" * removing the registration; unregistered content stays in the database but becomes unreachable.",
			" *",
			" * This file registers the Book CPT on the real `init` action with `show_in_rest => true`, and the",
			" * end-to-end test tier depends on both of those, so removing either is a test-visible break.",
			" *",
		),
		to: block(
			" * removing the registration; unregistered content stays in the database but becomes unreachable.",
			" *",
		),
	},
	{
		action: 'replace-exact',
		path: 'mu-plugins/a8csp-project-template-features/includes/book-cover-reminder.php',
		from: block(
			" *",
			" * This editor-side JavaScript counterpart to `book-post-type.php`'s front-end assets depends on",
			" * the Book post type. Removing the Book CPT removes this file's reason to exist, while this file",
			" * remains independently deletable when only the editor reminder is unwanted.",
			" *",
			" * To remove this worked example, delete this file; delete `assets/js/src/book-cover-reminder.js`",
		),
		to: block(
			" *",
			" * To remove this worked example, delete this file; delete `assets/js/src/book-cover-reminder.js`",
		),
	},

	// Theme includes.
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/includes/plugin-woocommerce.php',
		from: block(
			" * This is the theme's worked example of plugin-conditional code: the theme's own styling",
			" * (theme-setup.php) and small theme-owned dynamic content (theme-dynamic-content.php) never guard",
			" * on another plugin's presence, but this file exists only because WooCommerce might be. The guard",
			" * makes WooCommerce's absence silent -- no notice, no fallback markup -- so this file is safe to",
			" * leave in a site that never installs the plugin.",
		),
		to: block(
			" * The class-exists guard makes WooCommerce's absence silent -- no notice, no fallback markup -- so",
			" * this file is safe to leave in a site that never installs the plugin.",
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/includes/theme-dynamic-content.php',
		from: block(
			" * This is the theme's dynamic-content worked example. A v1 scaffold would have reached for a",
			" * shortcode here; a block theme reaches for a block binding instead, because it binds straight to a",
			" * block attribute in the editor rather than adding a separate shortcode parse pass. The",
			" * `patterns/footer-default.php` pattern binds a paragraph to this source.",
		),
		to: block(
			" * The `patterns/footer-default.php` pattern binds a paragraph to this source.",
		),
	},

	// Theme styles.
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/assets/css/src/cart.scss',
		from: block(
			"// A worked example of per-purpose CSS that reacts to a plugin: the two declarations stand in for a",
			"// real project's cart styles. Imitate the shape, or delete this file with the WooCommerce example",
			"// per the teardown note in includes/plugin-woocommerce.php.",
			".woocommerce-cart {",
		),
		to: block(
			".woocommerce-cart {",
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/assets/sass/base/_motion.scss',
		from: block(
			"// A worked example of motion paired with its reduced-motion escape hatch — imitate the pairing",
			"// for any transition or animation the project adds.",
		),
		to: block(
			"// Any motion the theme adds must ship with a prefers-reduced-motion override.",
		),
	},
	{
		action: 'replace-exact',
		path: 'themes/a8csp-project-template/assets/sass/style.scss',
		from: block(
			"Description: A minimal block theme proving the sass build pipeline, not a design system — replace its contents freely; keep the directory name and text domain.",
		),
		to: block(
			"Description: The site's block theme. Replace its contents freely; the directory name and text domain are permanent.",
		),
	},
];

const checkOnly = process.argv.includes( '--check' );

// Group the manifest by target file so each file is read once and every entry against it is
// applied to one working buffer in manifest order — the same buffer whether checking or writing,
// so `--check` and the default apply never diverge.
const entriesByPath = new Map();
for ( const entry of MANIFEST ) {
	if ( ! entriesByPath.has( entry.path ) ) {
		entriesByPath.set( entry.path, [] );
	}
	entriesByPath.get( entry.path ).push( entry );
}

const fileExists = async ( path ) => {
	try {
		await access( path );
		return true;
	} catch {
		return false;
	}
};

const errors = [];
const pendingWrites = [];
const pendingDeletes = [];

for ( const [ path, entries ] of entriesByPath ) {
	const absolutePath = joinPath( '.', path );

	const deleteEntries  = entries.filter( ( entry ) => 'delete' === entry.action );
	const replaceEntries = entries.filter( ( entry ) => 'replace-exact' === entry.action );

	for ( const entry of deleteEntries ) {
		if ( await fileExists( absolutePath ) ) {
			pendingDeletes.push( absolutePath );
		} else {
			errors.push( `delete: ${ path } does not exist` );
		}
	}

	if ( 0 === replaceEntries.length ) {
		continue;
	}

	if ( ! ( await fileExists( absolutePath ) ) ) {
		for ( const entry of replaceEntries ) {
			errors.push( `replace-exact: ${ path } does not exist for span starting "${ entry.from.split( '\n' )[0] }"` );
		}
		continue;
	}

	let buffer = await readFile( absolutePath, 'utf-8' );
	for ( const entry of replaceEntries ) {
		const occurrences = buffer.split( entry.from ).length - 1;
		if ( 1 !== occurrences ) {
			errors.push( `replace-exact: ${ path } — span occurs ${ occurrences } times (want exactly 1): "${ entry.from.split( '\n' )[0] }"` );
			continue;
		}
		// A function replacer inserts the text verbatim; a string replacement would interpret $-patterns inside it.
		buffer = buffer.replace( entry.from, () => entry.to );
	}

	pendingWrites.push( { absolutePath, buffer } );
}

if ( 0 !== errors.length ) {
	console.error( 'fill-in-scaffold-content: manifest failed with %d violation(s):', errors.length );
	for ( const error of errors ) {
		console.error( '  - %s', error );
	}
	process.exit( 1 );
}

if ( checkOnly ) {
	console.log( 'fill-in-scaffold-content: --check passed; every span matches exactly once.' );
	process.exit( 0 );
}

for ( const { absolutePath, buffer } of pendingWrites ) {
	console.log( 'Stripping teaching content from %s', absolutePath );
	await writeFile( absolutePath, buffer );
}
for ( const absolutePath of pendingDeletes ) {
	console.log( 'Deleting %s', absolutePath );
	await unlink( absolutePath );
}
