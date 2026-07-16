# Tests

This repository uses integration tests against a real WordPress plus one end-to-end smoke, rooted at `tests/`. The theme and features mu-plugin are tracked components, but they do not get separate suites: the promises under test belong to the site as a whole—whether it boots, loads its features, enqueues its assets, and renders in a browser.

## Suites

### Integration

`tests/Integration/` contains PHPUnit tests that run inside the tests wp-env instance. They extend plain PHPUnit `TestCase`, while `tests/bootstrap.php` loads the real WordPress installation so the mu-plugin and active theme follow their production boot paths.

- `SiteBootTest` protects the promise that the site starts with the project theme active and its front-end stylesheet available.
- `FeaturesLoaderTest` protects the promise that enabled site features, including the public Book archive, load while disabled examples stay absent.
- `AssetsTest` protects the promise that front-end assets are wired into the site and theme asset updates receive deploy-specific cache versions.

### End-to-End

`tests/EndToEnd/` contains Playwright coverage. It drives a real browser against the development wp-env instance on port `8894`.

## No below-floor / Requirements tier at site tier

A plugin or theme package may ship to arbitrary third-party sites and therefore needs proof below its supported version floor. This site repository deploys only to hosts the team controls, so it has no dedicated below-floor wp-env configuration or Requirements suite. The features plugin's inexpensive floor gate in `mu-plugins/a8csp-project-template-features/a8csp-project-template-features.php` is the only runtime below-floor protection -- the Quality workflow's below-floor syntax matrix covers parse-safety separately -- and is not a separate test tier.

## Why plain `TestCase`, not `WP_UnitTestCase`

The integration bootstrap loads WordPress itself, which is enough for this suite's boot, registration, and enqueue promises. Using `WP_UnitTestCase` would add `$this->factory` fixtures, `go_to()` routing simulation, and per-test transaction rollback, but the current tests neither build content-heavy scenarios nor persist database fixtures. Giving up those helpers costs little here and keeps the rig aligned with the narrower site contracts it exercises.

## Running the suites

Start the dedicated tests environment, then run the Integration suite:

```sh
npm run wp-env:tests:start
npm run test:integration
```

The npm test script runs `vendor/bin/phpunit --testsuite=Integration` inside that environment's CLI container. The `wp-content/project` wp-env mapping exists solely so that run finds `vendor/` inside the container; WordPress itself never reads that path. `testsEnvironment: false` in both env files disables wp-env's redundant second container per environment — this repo dedicates a whole config file to each environment instead. `composer test:integration` is an equivalent entry point: its Composer script delegates to `npm run test:integration` rather than maintaining a second test command.

When finished, stop the environment if you want to retain its containers, or destroy it for a clean reset:

```sh
npm run wp-env:tests:stop
npm run wp-env:tests:destroy
```

Run End-to-End coverage with:

```sh
npm run test:e2e
```

Playwright manages the development wp-env instance for that run; its `webServer.command` starts it with `npm run wp-env:start` and stops the instance it started when the run ends. An instance that was already running is reused and left running afterward.

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

Both wp-env configurations activate the project theme, set pretty permalinks, and flush rewrite rules in `afterStart`. That lifecycle is why `FeaturesLoaderTest` can resolve the Book archive link and the End-to-End Book singular route can resolve without either test performing a manual flush. Removing the Book worked example also means removing its test traces: the Book-specific assertions in `FeaturesLoaderTest` (the loader coverage is otherwise independent of Book) and the Book archive and singular assertions in the End-to-End spec. A production deployment that changes the CPT rewrite arguments—including adding or removing the CPT—needs one production `wp rewrite flush`, as documented in `mu-plugins/a8csp-project-template-features/includes/book-post-type.php`.

Generated repositories additionally ship `mu-plugins/<slug>-features/.disabled`, which disables the whole features plugin -- Book included -- until it's deleted. The Book-specific assertions above and the End-to-End Book smoke describe self-skip with a named message while that file is present, and resume automatically once it's removed; this template's own tree ships without the file, so its CI proves the enabled path on every run.

## Unit tier

No Unit suite ships at generation because no shipped site code is pure logic yet; the filesystem-bound asset metadata helpers are already integration-covered through real enqueues. When the first pure function appears, add `tests/Unit/` and a `Unit` testsuite entry in `phpunit.dist.xml` for it.
