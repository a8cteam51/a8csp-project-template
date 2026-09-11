# EXAMPLE_REPO_NAME

This repository is the `wp-content` for EXAMPLE_REPO_NAME: the theme owns how the site looks, the features mu-plugin owns what it does, and the tests prove both survive a deploy. Everything else is installed, not tracked.

## Structure

| Path | Purpose |
| --- | --- |
| `themes/EXAMPLE_REPO_SLUG` | How the site looks (the theme). |
| `mu-plugins/EXAMPLE_REPO_SLUG-features` | What the site does (the features mu-plugin). |
| `plugins/` | Tracked exceptions only; everything else there is installed, not tracked. |

## Deploy

`trunk` deploys to production; `develop` deploys to staging.

Production URL: EXAMPLE_REPO_PROD_URL

Pressable deployments run through DeployHQ and target `/wp-content/`. WPCOM deployments run through GitHub Deployments and target `/wp-content/`. On both paths, the tree deploys as-is; `.deployignore` is the only filter.

## Quick start

Install dependencies and start the development site:

```sh
composer install
npm install
npm run wp-env:start
```

The site is available on port `8894`; `tests/README.md` documents the dedicated test environment. wp-env publishes the site on all network interfaces with fixed development credentials -- treat the dev site as visible to your local network, not just localhost.

## Multisite

This template targets a single-site install by default. A multisite project adapts three things by hand: flush rewrite rules per site (not once) after any change to the Book CPT's -- or an added CPT's -- rewrite arguments; network-enable the theme instead of the single-site `wp theme activate` in `afterStart`; and, if the build adds options or an uninstall routine, scope and sweep them per site.

## Recipes

### Your first feature

Generation disables the features mu-plugin by creating `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`, so an unattended site never registers the example content. Delete `.disabled` (at `mu-plugins/EXAMPLE_REPO_SLUG-features/.disabled`) to start building the site's features -- the worked Book example (CPT, front-end styles, and block-editor panel) lights up on the next request, its pretty-permalink routes (`/book/...`) resolve after one rewrite flush (restarting wp-env re-runs the `afterStart` flush; production needs `wp rewrite flush` once, as documented in the CPT file), and the Book-specific tests in `tests/Integration/FeaturesLoaderTest.php`, `tests/Integration/AssetsTest.php`, and `tests/EndToEnd/site-smoke.spec.js` (self-skipped while `.disabled` is present) start running and passing. That's the fastest way to see a real feature move through this template end to end before writing your own.

To remove Book entirely instead of enabling it, follow the teardown recipe below.

### Replace the theme

The directory name `themes/EXAMPLE_REPO_SLUG` and text domain `EXAMPLE_REPO_SLUG` are permanent. PHPStan paths, wp-env mappings and `afterStart` activation, tests, Composer scripts, and `.gitignore` depend on them. Replace the theme's contents; never rename its directory or text domain.

Replacement content must also keep the contract the test suite encodes: a `body_class` filter that marks the active theme, a `wp_enqueue_scripts` callback that registers an `EXAMPLE_REPO_SLUG-style` handle (with an editor stylesheet added via `add_editor_style()`) and an `EXAMPLE_REPO_SLUG-script` handle, and an asset-metadata helper the theme's `includes/theme-setup.php` calls to derive each handle's version. A theme missing any of these reddens `tests/Integration/SiteBootTest.php`, hard-errors `tests/Integration/AssetsTest.php` on a missing `index.asset.php`, and breaks both End-to-End locator assertions in `tests/EndToEnd/site-smoke.spec.js`. While the features plugin is enabled, its Book styles also expect the theme to render the Book archive through a Query Loop and a Book singular view through the Post Content block; a theme that renders either another way leaves those styles unapplied and fails the Book End-to-End assertions.

The theme also ships two worked examples that are safe to delete independently; follow the teardown lines co-located in `themes/EXAMPLE_REPO_SLUG/includes/plugin-woocommerce.php` and `themes/EXAMPLE_REPO_SLUG/includes/theme-dynamic-content.php`.

### Tracked custom plugin

To track a custom plugin in `plugins/<name>`:

- Add `!plugins/<name>` to `.gitignore`, mirroring the existing mu-plugin and theme exceptions.
- Add `%currentWorkingDirectory%/plugins/<name>` to the root `.phpstan.neon` file's `parameters.paths` list. Do not use `scanDirectories`; that is for installed dependency code, not tracked first-party code.
- Give the plugin a thin `plugins/<name>/.phpcs.xml` based on `themes/EXAMPLE_REPO_SLUG/.phpcs.xml`: extend the shared ruleset and declare the plugin's own text domain and prefix properties.
- Add a named `"wp-content/plugins/<name>": "./plugins/<name>"` mapping to both `.wp-env.json` and `.wp-env.tests.json`. The configs deliberately omit a generic `wp-content/plugins` mapping because an empty mount would shadow environment-installed plugins.

### Off-the-shelf plugin

Require it from WP Packages as a development dependency with `composer require --dev wp-plugin/<slug>` — installer-paths route it to `plugins/<slug>/`, gitignored like every other Composer or npm dependency. It never reaches the deployed tree (deploy is git-tree-as-is; production never runs `composer install`), so it's for local development and CI tooling only. The live site's off-the-shelf plugins are installed and updated on the host itself (Pressable or WPCOM plugin management), not through this repo.

### Teardown one-liners

- **RTL:** Delete the `build:theme:style-rtl` npm script, the `wp_style_add_data( ..., 'rtl', ... )` call in `themes/EXAMPLE_REPO_SLUG/includes/theme-setup.php`, and `themes/EXAMPLE_REPO_SLUG/style-rtl.css`.
- **i18n:** Delete both `languages/` directories, the `internationalize` and `i18n:*` Composer scripts, the `Text Domain` and `Domain Path` header lines, the `load_muplugin_textdomain()` call in the features entry file, the `wp-cli/i18n-command` Composer dev dependency, and the `WordPress.WP.I18n` `text_domain` property block in each of the three PHPCS rulesets (`themes/EXAMPLE_REPO_SLUG/.phpcs.xml`, `mu-plugins/EXAMPLE_REPO_SLUG-features/.phpcs.xml`, and the root `.phpcs.tests.xml`). The site's own `__()`/`_e()` calls survive this teardown — deleting the i18n plumbing removes the catalog machinery, not the translatable strings themselves.
- **Book feature:** Follow the teardown recipe at the top of `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-post-type.php`; its removal instructions live beside the self-contained worked example they remove; follow the editor-JS companion's teardown recipe at `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-cover-reminder.php`.
