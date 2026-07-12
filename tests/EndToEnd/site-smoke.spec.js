const { test, expect } = require('@wordpress/e2e-test-utils-playwright');

let bookId;
let bookLink;

test.describe('Site smoke', () => {
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
