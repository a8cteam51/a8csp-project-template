/**
 * Registers a Book cover-image pre-publish panel that renders only when the current post is a Book
 * without a featured image.
 *
 * To remove this worked example, delete this file and its built counterparts,
 * `assets/js/build/editor.js` and `assets/js/build/editor.asset.php`; delete the
 * `build:features:scripts` and `start:features:scripts` npm scripts; delete
 * `includes/book-editor.php`; and delete `test_book_editor_enqueue_callback_is_registered` and
 * `test_book_editor_script_dependencies_come_from_generated_asset_file` from
 * `tests/Integration/AssetsTest.php`.
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginPrePublishPanel, store as editorStore } from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';

const BookCoverReminder = () => {
	const { featuredImageId, postType } = useSelect(
		(select) => ({
			featuredImageId:
				select(editorStore).getEditedPostAttribute('featured_media'),
			postType: select(editorStore).getCurrentPostType(),
		}),
		[]
	);

	if ('book' !== postType || featuredImageId) {
		return null;
	}

	return (
		<PluginPrePublishPanel
			title={__('Book cover', 'a8csp-project-template-features')}
			initialOpen
		>
			<Notice status="warning" isDismissible={false}>
				{__(
					'Add a cover image before publishing this Book.',
					'a8csp-project-template-features'
				)}
			</Notice>
		</PluginPrePublishPanel>
	);
};

registerPlugin('a8csp-project-template-features-book-cover-reminder', {
	render: BookCoverReminder,
});
