const { test, expect } = require('@wordpress/e2e-test-utils-playwright');
const fs = require('fs');
const path = require('path');

test.describe('Theme smoke', () => {
	test('the site serves its front-end routes and styles', async ({
		page,
	}) => {
		const homeResponse = await page.goto('/');

		expect(homeResponse.status()).toBe(200);
		await expect(page.locator('body')).toHaveClass(
			/\ba8csp-project-template\b/
		);
		await expect(
			page.locator('#a8csp-project-template-style-css')
		).toHaveCount(1);
	});
});

// Filesystem check, not a REST probe: it resolves at test-collection time, before any wp-env
// instance is guaranteed reachable, and mirrors the PHP integration suite's own disabled-plugin
// probe exactly.
const featuresPluginEnabled = !fs.existsSync(
	path.join(
		__dirname,
		'../../mu-plugins/a8csp-project-template-features/.disabled'
	)
);

test.describe('Book smoke', () => {
	test.skip(
		!featuresPluginEnabled,
		'The features plugin is disabled (mu-plugins/a8csp-project-template-features/.disabled); delete that file to enable it.'
	);

	let bookId;
	let bookLink;

	test.beforeAll(async ({ requestUtils }) => {
		const book = await requestUtils.createRecord('book', {
			title: 'End-to-end smoke test book',
			status: 'publish',
		});

		bookId = book.id;
		bookLink = book.link;
	});

	test.afterAll(async ({ requestUtils }) => {
		await requestUtils.rest({
			method: 'DELETE',
			path: '/wp/v2/book/' + bookId,
			params: { force: true },
		});
	});

	test('the Book archive and singular routes serve their per-purpose styles', async ({
		page,
	}) => {
		const archiveResponse = await page.goto('/book/');

		expect(archiveResponse.status()).toBe(200);
		await expect(
			page.locator('#a8csp-project-template-features-book-archive-css')
		).toHaveCount(1);

		const singularResponse = await page.goto(bookLink);

		expect(singularResponse.status()).toBe(200);
		await expect(
			page.locator('#a8csp-project-template-features-book-singular-css')
		).toHaveCount(1);
	});
});
