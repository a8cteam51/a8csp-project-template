# Tests

This repository uses integration tests against a real WordPress plus one end-to-end smoke, rooted at `tests/`. The theme and features mu-plugin are tracked components, but they do not get separate suites: the promises under test belong to the site as a whole—whether it boots, loads its features, enqueues its assets, and renders in a browser.

## Suites

### Integration

`tests/Integration/` contains PHPUnit tests that run inside the tests wp-env instance. They extend plain PHPUnit `TestCase`, while `tests/bootstrap.php` loads the real WordPress installation so the mu-plugin and active theme follow their production boot paths.

- `SiteBootTest` protects the promise that the site starts with the project theme active and its front-end stylesheet available.
- `FeaturesLoaderTest` protects the promise that the site's enabled features, including the public Book archive, load.
- `AssetsTest` protects the promise that front-end assets are wired into the site and theme asset updates receive deploy-specific cache versions.
- `MuLoaderTest` protects the promise that the admin plugins screen reports exactly the loaded, non-gated must-use plugins.

### End-to-End

`tests/EndToEnd/` contains Playwright coverage. It drives a real browser against the development wp-env instance on port `8894`.

## No below-floor / Requirements tier at site tier

This site repository deploys only to hosts the team controls, so it has no dedicated below-floor wp-env configuration or Requirements suite. The features plugin's inexpensive floor gate in `mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php` is the only runtime below-floor protection -- the Quality workflow's below-floor syntax matrix covers parse-safety separately -- and is not a separate test tier.

## Why plain `TestCase`, not `WP_UnitTestCase`

WordPress core's PHPUnit scaffold supports PHPUnit through version 9. This rig runs current PHPUnit directly against plain `TestCase` inside wp-env, with `tests/bootstrap.php` loading the real WordPress installation, without depending on `WP_UnitTestCase` or core's PHPUnit compatibility range.

That trade gives up `$this->factory` fixture helpers, `go_to()` routing simulation, and per-test transaction rollback. The site's boot, registration, and enqueue promises need none of them: the tests build no content-heavy fixtures and persist no database state.

## Running the suites

Run the Integration suite; the verb starts the tests environment itself:

```sh
composer test:integration
```

The npm test script runs `vendor/bin/phpunit --testsuite=Integration` inside that environment's CLI container. The `wp-content/project` wp-env mapping exists solely so that run finds `vendor/` inside the container; WordPress itself never reads that path. `testsEnvironment: false` in both env files disables wp-env's redundant second container per environment — this repo dedicates a whole config file to each environment instead. `composer test:integration` is the fuller entry point: it starts the tests environment first — `start` recreates the containers when the resolved config moved, so it is what applies a `mappings` edit — and then delegates to `npm run test:integration` rather than maintaining a second test command. `npm run test:integration` alone attaches to whatever container is up.

When finished, stop the environment if you want to retain its containers, or destroy it for a clean reset:

```sh
npm run wp-env:tests:stop
npm run wp-env:tests:destroy
```

Run End-to-End coverage with:

```sh
npm run test:e2e
```

Playwright's `webServer` config starts the development wp-env instance with `npm run wp-env:start` when none is running and reuses one that is. `wp-env start` returns once the containers are up, so they keep running after the run; stop them with `npm run wp-env:stop`.

## Ports

| Purpose | Configuration | Port |
| --- | --- | ---: |
| Dev / E2E | `.wp-env.json` | `8894` |
| Tests | `.wp-env.tests.json` | `8895` |

Generated repositories get their own two-port block, derived from the repository name at
generation, so projects started side by side rarely contend for the same host ports -- the
hash is collision-reducing, not unique. If two environments collide on one machine, wp-env's
untracked override files take local precedence (`.wp-env.override.json`; the tests config pairs with `.wp-env.tests.override.json`).

## Book CPT test lifecycle

Both wp-env configurations activate the project theme, set pretty permalinks, and flush rewrite rules in `afterStart`. That lifecycle is why `FeaturesLoaderTest` can resolve the Book archive link and the End-to-End Book singular route can resolve without either test performing a manual flush. Removing the Book worked example also removes its test traces, which the recipe at the top of `mu-plugins/a8csp-project-template-features/includes/book-post-type.php` lists. A production deployment that changes the CPT rewrite arguments—including adding or removing the CPT—needs one production `wp rewrite flush`, as documented in `mu-plugins/a8csp-project-template-features/includes/book-post-type.php`.

Generation creates `mu-plugins/<slug>-features/.disabled`, which disables the whole features plugin -- Book included -- until it's deleted. While that file is present, the Book-specific assertions and the End-to-End Book smoke self-skip with a named message, and they resume automatically once it's removed.

## Unit tier

No Unit suite ships yet because no shipped site code is pure logic: the asset metadata helpers read the filesystem, so their tests are integration tests. Add `tests/Unit/` and a `Unit` testsuite entry in `phpunit.dist.xml` when the first pure function appears.
