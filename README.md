# A8CSP Project Template

A template for A8C Special Projects full-site builds.

This repository is a template site, not a finished site. Generate a site repository from it, and its `wp-content` follows one rule: the theme owns how the site looks, the features mu-plugin owns what it does, and the tests prove both survive a deploy. Everything else is installed, not tracked. This file documents that generation and the tooling used to maintain the template itself; the generated site's own README is a separate file, covered below.

## Trunk-only

This repository never versions itself: no tags and no releases -- its history lives in git, and a repository ruleset blocks tag creation outright. There is no release or changelog machinery here: generated site repositories deploy their tree as-is rather than shipping built release artifacts.

## What is in this repository

- `themes/a8csp-project-template/` is the worked block theme. Its slug getter leads `functions.php`'s `META` region, followed by a shared asset-metadata helper and a `sort()`-ordered, underscore-opt-out loader for `includes/`; `includes/theme-setup.php` registers theme support and enqueues `style.css` (built from `assets/sass/`) plus the built JS entry in `assets/js/`. `includes/plugin-woocommerce.php` is the plugin-conditional worked example, with a WooCommerce cart stylesheet built from `assets/css/src/`; `includes/theme-dynamic-content.php` is the block-binding worked example consumed by `patterns/footer-default.php`. Like the features mu-plugin, the theme has two independent CSS pipelines: `assets/sass/` for its main and editor stylesheets and `assets/css/` for per-purpose plugin-conditional stylesheets.
- `mu-plugins/mu-loader.php` discovers must-use plugins by scanning `mu-plugins/*/*.php` and reading only the `Plugin Name` header through `get_file_data()` -- no admin bootstrap on the frontend. Every file with a non-empty header loads once per request; full `get_plugin_data()` metadata is parsed lazily, only inside the admin plugin-list callback.
- `mu-plugins/a8csp-project-template-features/` is the worked features mu-plugin. It's procedural, not class-based: site repos gitignore `vendor/`, so there is no Composer autoloader at runtime in production. The entry file gates on the WordPress/PHP floor before loading `includes/`; `includes/book-post-type.php` registers the Book post type for real on `init` with `show_in_rest`, enqueues per-purpose archive/singular stylesheets built from `assets/css/src/`, and documents its own teardown at the top of the file. `includes/book-cover-reminder.php` is the plugin's first editor-side JavaScript worked example: it is Book-scoped, warns before publishing a Book without a cover image, and documents its own teardown the same way.
- `plugins/` carries only `.gitkeep`. Every content path under it is gitignored; the generated README's "Tracked custom plugin" and "Off-the-shelf plugin" recipes cover what goes there.
- `tests/` is one whole-site test suite, not one suite per component: integration tests against a real WordPress (`tests/Integration/`, plain PHPUnit `TestCase`, run inside the tests wp-env instance) plus one end-to-end smoke (`tests/EndToEnd/`, Playwright, against the dev wp-env instance). See `tests/README.md`.
- `.github/workflows/` runs PHP/JS/CSS quality, PHP syntax including a below-floor matrix on the mu-loader and the header-discovered features entry, a build-integrity check that the committed build output reproduces byte-for-byte, PHPUnit + Playwright, supply-chain audit, CodeQL, and `fill-in-scaffold.yml` -- the self-deleting generation workflow below.
- The root also carries the whole dev toolchain: `composer.json` / `package.json`, the wp-env pair, `playwright.config.js`, `phpunit.dist.xml`, the lint configs, `.deployignore`, and `LICENSE` (GPL-2.0-or-later). Both live deploy paths target this tree as-is; `.deployignore` is the only filter.

## Generating a new site repository

Repositories are generated from this template through GitHub's template mechanism, followed by a `fill-in-scaffold.yml` run (`repository_dispatch` with type `fill_scaffold`, or manually via `workflow_dispatch`). The workflow is guarded so it never runs on this template repository itself, and it requires its ref to be the new repository's default branch.

Before touching the checkout, it validates two repository custom properties:

- `human-title` -- the site's human-readable title.
- `php-globals-short-prefix` -- the PHP global function/constant prefix; must be a lowercase PHP identifier.

Both are required. A missing or malformed value fails the run with a per-property `::error` annotation before anything is checked out, so an invalid repository is left untouched. The repository description, when set, must be a single line without `*/`; the repository name must be lowercase-kebab.

Once validated, generation renames `README.scaffold.md` to `README.md` and the theme/features directories and entry file to the repository name, then runs `fill-in-scaffold.mjs` to replace every tracked template literal -- the Composer package name, the namespace-shaped `@package` identifier (no PHP `namespace` is declared anywhere in the site tree), both PHP global prefixes, the theme/features slugs, the README's `EXAMPLE_REPO_*` placeholders, and the wp-env ports `8894`/`8895` (matched only inside their `"port":`, `localhost:`, and backtick-wrapped anchors) -- with values derived from the repository's name, description, homepage, and the two custom properties above. The ports become a two-port block hashed from the repository name -- collision-reducing, not unique, since distinct names can hash to the same block -- so fleet projects started side by side rarely contend for the same host ports; a `.wp-env*.override.json` file takes local precedence when blocks do collide. The repository's `homepage` becomes the production URL documented in the generated README; when it's empty, the README renders a visible placeholder instead of a guessed or absent URL, since generation commonly precedes hosting provisioning. It then clears the template's own POT files, re-locks `composer.lock` and `package-lock.json` against the renamed identity, and finally deletes the spent `fill-in-scaffold.yml` and `.mjs` themselves before pushing everything as one push.

## What a generated repository contains

`README.scaffold.md` becomes the generated repository's actual `README.md`. It documents that site's structure, deploy story, and recipes -- replacing the theme, tracking a custom plugin, and tearing down RTL, i18n, or the Book worked example. Read it directly rather than here; duplicating it in this file would only let the two drift apart.

## Working on the template

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

Run the local WordPress environment:

```sh
npm run wp-env:start
```

wp-env publishes the site on all network interfaces with fixed development credentials -- treat the dev site as visible to your local network, not just localhost.

Quality checks:

```sh
composer validate
composer run-script lint:php
npm run lint:scripts
npm run lint:styles
npm run lint:readme-md
```

`lint:php` runs three PHPCS rulesets -- theme, features mu-plugin, `tests/` -- plus a single root `phpstan analyse` pass. A site has one WordPress floor for its whole tree, so PHPStan runs once against both tracked components rather than once per component, unlike a plugin or theme package.

Tests:

```sh
npm run wp-env:tests:start && npm run test:integration
npm run test:e2e
```

`tests/README.md` covers both tiers in full, including the dedicated test wp-env instance and its port. There is no below-floor or Requirements tier: a plugin or theme package ships to sites it doesn't control and needs proof below its supported floor, while this site deploys only to hosts the team controls. The features mu-plugin's inexpensive floor gate is the only runtime below-floor protection -- the Quality workflow's below-floor syntax matrix covers parse-safety separately -- and it isn't a separate test tier.

### Changing the generation machinery

`fill-in-scaffold.mjs` and `.yml` are template-only; neither ships in a generated repository. A change to the substitution table or the guard is proven by rendering the script against representative inputs before relying on a live scratch-repo dispatch: an aligned name and title, a diverging name and title, an empty description, an empty homepage, and a description containing hostile characters such as quotes or backslashes. Confirm the rendered output still passes `composer validate` and the PHPCS rulesets, and that a full-tree sweep afterward turns up no surviving template literal -- including the README's `EXAMPLE_REPO_*` placeholders and both PHP prefixes.
