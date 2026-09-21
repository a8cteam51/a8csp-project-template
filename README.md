# A8CSP Project Template

A template for A8C Special Projects full-site builds.

It holds the `wp-content` a new site repository starts from: a block theme for how the site looks, a
features mu-plugin for what it does, the MU loader, whole-site tests, and CI. Everything else in
`wp-content` is installed, not tracked.

## Generating a new site repository

A repository created from this template runs `.github/workflows/fill-in-scaffold.yml` from its
default branch, on a `fill_scaffold` repository dispatch or manually. It reads the repository name,
description, and homepage and the `human-title` and `php-globals-short-prefix` custom properties,
then renames the README, theme, features plugin, title, slugs, Composer package, `@package`
identifier, and PHP prefixes, derives a per-repository wp-env port block, disables the features
plugin with a `.disabled` marker, resolves both lockfiles afresh, and deletes itself. The
`strip-teaching-content` input also rewrites the teaching prose into production documentation.
`README.scaffold.md` becomes the site's README.

## Working on the template

The template never versions itself: a repository ruleset blocks tags, and generated sites deploy
their tree as-is, so there is no release or changelog machinery.

`README.scaffold.md` documents a site's layout, deploy, and recipes, and
[`tests/README.md`](tests/README.md) covers the test suites. The teaching-content strip matches each
passage in `fill-in-scaffold-content.mjs` by its exact text, before the rename, and
`template-guard.yml` fails any change that leaves a passage matching zero or several times; its
generation contract also renders representative fixtures and checks the result.

### Layout

- `themes/a8csp-project-template/` is the worked block theme. Its slug getter leads `functions.php`'s `META` region, followed by a shared asset-metadata helper and a `sort()`-ordered, underscore-opt-out loader for `includes/`; `includes/theme-setup.php` registers theme support and enqueues `style.css` (built from `assets/sass/`) plus the built JS entry in `assets/js/`. `includes/plugin-woocommerce.php` is the plugin-conditional worked example, with a WooCommerce cart stylesheet built from `assets/css/src/`; `includes/theme-dynamic-content.php` is the block-binding worked example consumed by `patterns/footer-default.php`. The theme has two independent CSS pipelines: `assets/sass/` for its main and editor stylesheets and `assets/css/` for per-purpose plugin-conditional stylesheets.
- `mu-plugins/mu-loader.php` discovers must-use plugins by scanning `mu-plugins/*/*.php` and reading only the `Plugin Name` header through `get_file_data()` -- no admin bootstrap on the frontend. Every file with a non-empty header loads once per request; full `get_plugin_data()` metadata is parsed lazily, only inside the admin plugin-list callback.
- `mu-plugins/a8csp-project-template-features/` is the worked features mu-plugin. It's procedural, not class-based: site repos gitignore `vendor/`, so there is no Composer autoloader at runtime in production. The entry file gates on the WordPress/PHP floor before loading `includes/`; `includes/book-post-type.php` registers the Book post type for real on `init` with `show_in_rest`, enqueues per-purpose archive/singular stylesheets built from `assets/css/src/`, and documents its own teardown at the top of the file. `includes/book-cover-reminder.php` is the plugin's first editor-side JavaScript worked example: it is Book-scoped, warns before publishing a Book without a cover image, and documents its own teardown the same way.
- `plugins/` carries only `.gitkeep`. Every content path under it is gitignored; the generated README's "Tracked custom plugin" and "Off-the-shelf plugin" recipes cover what goes there.
- `tests/` is one whole-site test suite, not one suite per component: integration tests against a real WordPress (`tests/Integration/`, plain PHPUnit `TestCase`, run inside the tests wp-env instance) plus one end-to-end smoke (`tests/EndToEnd/`, Playwright, against the dev wp-env instance). See `tests/README.md`.
- `.github/workflows/` holds the CI workflows, plus `template-guard.yml` and the self-deleting
  `fill-in-scaffold.yml`, which generation removes.
- Deploys target the tree as-is; `.deployignore` is the only filter.

### Development

Install dependencies:

```sh
composer run-script packages-install
npm ci
```

Build the theme and features assets:

```sh
npm run build
```

Run watch builds:

```sh
npm start
```

`npm start` watches the theme and features Sass and scripts, but the styles are compiled by Sass only -- the PostCSS vendor-prefix pass and the RTL stylesheet come from `npm run build`, so the watched CSS differs from a production build.

Run the local WordPress environment:

```sh
npm run wp-env:start
```

wp-env publishes the site on all network interfaces with fixed development credentials -- treat the dev site as visible to your local network, not just localhost.

### Quality checks

```sh
composer validate --strict
composer run-script lint:php
npm run lint
```

`npm run lint` runs the JavaScript, style, `package.json`, and README lint leaves in sequence.
`lint:php` runs three PHPCS rulesets -- theme, features mu-plugin, `tests/` -- plus one PHPStan
pass over both tracked components.

### Tests

```sh
composer test:integration
npm run test:e2e
```
