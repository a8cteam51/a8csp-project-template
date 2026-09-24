# EXAMPLE_REPO_NAME

This repository is the `wp-content` for EXAMPLE_REPO_NAME: the theme owns how the site looks and the features mu-plugin owns what it does. Everything else is installed, not tracked.

## Structure

| Path | Purpose |
| --- | --- |
| `themes/EXAMPLE_REPO_SLUG` | How the site looks (the theme). |
| `mu-plugins/EXAMPLE_REPO_SLUG-features` | What the site does (the features mu-plugin). |
| `plugins/` | Tracked exceptions only; everything else there is installed, not tracked. |

## Deploy

`trunk` deploys to production; `develop` deploys to staging.

Production URL: EXAMPLE_REPO_PROD_URL

Deploys target `/wp-content/` and ship the tree as-is; `.deployignore` is the only filter.

## Quick start

Install dependencies and start the development site:

```sh
composer install
npm install
npm run wp-env:start
```

The site is available on port `8894`; `tests/README.md` documents the dedicated test environment. wp-env publishes the site on all network interfaces with fixed development credentials — treat the dev site as visible to your local network, not just localhost.

`composer packages-update` and `npm run packages-update` update every dependency within the range its manifest declares. `npm run packages-update:wp` moves the `@wordpress/*` packages, across major versions, to their latest releases, since the site runs the latest WordPress.

## Multisite

This site targets a single-site install by default. A multisite project adapts three things by hand: flush rewrite rules per site (not once) after any change to the Book CPT's — or an added CPT's — rewrite arguments; network-enable the theme instead of the single-site `wp theme activate` in `afterStart`; and, if the build adds options or an uninstall routine, scope and sweep them per site.

## Recipes

### Your first feature

Generation disables the features mu-plugin by creating `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`, so an unattended site never registers the example content. Delete that file to start building the site's features — the worked Book example (CPT, front-end styles, block-editor panel, and Book Count block) lights up on the next request, its pretty-permalink routes (`/book/...`) resolve after one rewrite flush (restarting wp-env re-runs the `afterStart` flush; production needs `wp rewrite flush` once, as documented in the CPT file), and the Book-specific tests in `tests/Integration/FeaturesLoaderTest.php`, `tests/Integration/AssetsTest.php`, `tests/Integration/BookCountBlockTest.php`, and `tests/EndToEnd/site-smoke.spec.js` (self-skipped while `.disabled` is present, apart from the block's disabled-state check) start running and passing. That's the fastest way to see a real feature move through the site end to end before writing your own.

To remove Book entirely instead of enabling it, follow the teardown recipe below.

### Replace the theme

The directory name `themes/EXAMPLE_REPO_SLUG` and text domain `EXAMPLE_REPO_SLUG` are permanent. PHPStan paths, wp-env mappings and `afterStart` activation, tests, Composer scripts, and `.gitignore` depend on them. Replace the theme's contents; never rename its directory or text domain.

Replacement content must also keep the contract the test suite encodes: a `body_class` filter that adds the `EXAMPLE_REPO_SLUG` class, a `wp_enqueue_scripts` callback that registers an `EXAMPLE_REPO_SLUG-style` handle (with an editor stylesheet added via `add_editor_style()`) and an `EXAMPLE_REPO_SLUG-script` handle, and an asset-metadata helper the theme's `includes/theme-setup.php` calls to derive each handle's version. A theme missing any of these fails a test: the End-to-End theme smoke in `tests/EndToEnd/site-smoke.spec.js` checks the body class and the stylesheet on the home page, `tests/Integration/SiteBootTest.php` checks the editor stylesheet and both handles, and `tests/Integration/AssetsTest.php` checks each handle's version and hard-errors on a missing `index.asset.php`. While the features plugin is enabled, its Book styles also expect the theme to render the Book archive through a Query Loop and a Book singular view through the Post Content block; a theme that renders either another way leaves those styles unapplied and fails the Book End-to-End assertions.

The theme also ships two worked examples that are safe to delete independently; follow the teardown lines co-located in `themes/EXAMPLE_REPO_SLUG/includes/plugin-woocommerce.php` and `themes/EXAMPLE_REPO_SLUG/includes/theme-dynamic-content.php`.

### Tracked custom plugin

To track a custom plugin in `plugins/<name>`:

- Add `!plugins/<name>` to `.gitignore`, mirroring the existing mu-plugin and theme exceptions.
- A plugin whose runtime needs its `vendor/` tree commits it (drop any `vendor/` rule from the plugin's own `.gitignore`); the site ignores and withholds from the deploy only its own root `vendor/`, so the plugin's tree deploys with it.
- Add `%currentWorkingDirectory%/plugins/<name>` to the root `.phpstan.neon` file's `parameters.paths` list. Do not use `scanDirectories`; that is for installed dependency code, not tracked first-party code. For a plugin generated from `a8cteam51/a8csp-plugin-template`, also add `%currentWorkingDirectory%/plugins/<name>/tests` to `parameters.excludePaths.analyseAndScan`: its unit-test stubs redeclare WordPress functions and would skew the analysis of the plugin's own code.
- Give the plugin a thin `plugins/<name>/.phpcs.xml` based on `themes/EXAMPLE_REPO_SLUG/.phpcs.xml`: extend the shared ruleset and declare the plugin's own text domain and prefix properties. Then add `phpcs --standard=./plugins/<name>/.phpcs.xml --basepath=. ./plugins/<name> -v` to the `lint:php:phpcs` Composer script, and the matching `phpcbf` line to `format:php`; the Quality workflow runs only those scripts, so a ruleset nothing invokes checks nothing.
- Add a named `"wp-content/plugins/<name>": "./plugins/<name>"` mapping to both `.wp-env.json` and `.wp-env.tests.json`, and add `wp plugin activate <name>` to each file's `afterStart` script next to the theme's `wp theme activate`, so it runs before the rewrite flush. A mapping only mounts the plugin, and CI starts every run from a fresh environment, so the activation is what loads it. The configs deliberately omit a generic `wp-content/plugins` mapping because an empty mount would shadow environment-installed plugins.

### Off-the-shelf plugin

Require it from WP Packages as a development dependency with `composer require --dev wp-plugin/<slug>` — installer-paths route it to `plugins/<slug>/`, gitignored like every other Composer or npm dependency. It never reaches the deployed tree (deploy is git-tree-as-is; production never runs `composer install`), so it's for local development and CI tooling only; to load it there, map it as `"wp-content/plugins/<slug>": "./plugins/<slug>"` and activate it in both `.wp-env.json` and `.wp-env.tests.json`, as for a tracked plugin. When tracked code uses its classes or functions, also add `%currentWorkingDirectory%/plugins/<slug>` to the root `.phpstan.neon` file's `parameters.scanDirectories` list, so PHPStan knows those symbols without analysing the plugin. The live site's off-the-shelf plugins are installed and updated on the host itself (Pressable or WPCOM plugin management), not through this repo.

### Blocks

Does this block really belong to this one site only? If another site could use it, build it in the A8C Special Projects [blocks monorepo](https://github.com/a8cteam51/special-projects-blocks-monorepo) instead, where every site can reuse it. The Quality workflow fails on any tracked `block.json` whose path is not listed in `.github/blocks-allowlist` (one path per line, `#` for comments), and its error points to the monorepo.

When the answer is yes, the features mu-plugin already has the build wiring. Put the block in `mu-plugins/EXAMPLE_REPO_SLUG-features/blocks/src/<name>/`: `npm run build` compiles it into `blocks/build/<name>/` and regenerates `blocks/build/blocks-manifest.php`, and `includes/blocks.php` registers everything in that manifest. Then list both copies of its `block.json`, the source and the built one, in `.github/blocks-allowlist`.

The Book Count block (`blocks/src/book-count/`) shows that exception end to end: it's an example to learn from, not an invitation to keep blocks here. It goes with the rest of Book (see the teardown one-liners below).

### Teardown one-liners

- **RTL:** Delete the `build:theme:style-rtl` npm script and the `rtlcssConfig` key that serves only it, the `wp_style_add_data( ..., 'rtl', ... )` call in `themes/EXAMPLE_REPO_SLUG/includes/theme-setup.php`, `themes/EXAMPLE_REPO_SLUG/style-rtl.css`, and the `rtl` assertion (with its docblock's RTL clause) in `tests/Integration/SiteBootTest.php`; then `npm uninstall rtlcss`.
- **i18n:** Delete both `languages/` directories, the `internationalize` and `i18n:*` Composer scripts, the `Text Domain` and `Domain Path` header lines, the `load_muplugin_textdomain()` call in the features entry file, the `wp-cli/i18n-command` Composer dev dependency, and the `WordPress.WP.I18n` `text_domain` property block in each of the three PHPCS rulesets (`themes/EXAMPLE_REPO_SLUG/.phpcs.xml`, `mu-plugins/EXAMPLE_REPO_SLUG-features/.phpcs.xml`, and the root `.phpcs.tests.xml`). The site's own `__()`/`_e()` calls survive this teardown — deleting the i18n plumbing removes the catalog machinery, not the translatable strings themselves.
- **Book feature:** Follow the teardown recipes at the top of `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-post-type.php`, of its editor-JS companion, `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-cover-reminder.php`, and of `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/blocks.php` for the Book Count block.
