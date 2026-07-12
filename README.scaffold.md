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

The site is available on port `8894`; `tests/README.md` documents the dedicated test environment.

## Recipes

### Replace the theme

The directory name `themes/EXAMPLE_REPO_SLUG` and text domain `EXAMPLE_REPO_SLUG` are permanent. PHPStan paths, wp-env mappings and `afterStart` activation, tests, Composer scripts, and `.gitignore` depend on them. Replace the theme's contents; never rename its directory or text domain.

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
- **i18n:** Delete both `languages/` directories, the `internationalize` and `i18n:*` Composer scripts, and the `Text Domain` and `Domain Path` header lines.
- **Book feature:** Follow the teardown recipe at the top of `mu-plugins/EXAMPLE_REPO_SLUG-features/includes/book-post-type.php`; its removal instructions live beside the self-contained worked example they remove.
