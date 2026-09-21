/**
 * Registers a Book cover-image pre-publish panel that renders only when the current post is a Book
 * without a featured image.
 *
 * To remove this worked example, follow the recipe in `includes/book-cover-reminder.php`.
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginPrePublishPanel, store as editorStore } from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';

const BookCoverReminder = () => {
	const { featuredImageId, postType } = useSelect(
		( select ) => ( {
			featuredImageId:
				select( editorStore ).getEditedPostAttribute(
					'featured_media'
				),
			postType: select( editorStore ).getCurrentPostType(),
		} ),
		[]
	);

	if ( 'a8csp_template_book' !== postType || featuredImageId ) {
		return null;
	}

	return (
		<PluginPrePublishPanel
			title={ __( 'Book cover', 'a8csp-project-template-features' ) }
			initialOpen
		>
			<Notice status="warning" isDismissible={ false }>
				{ __(
					'Add a cover image before publishing this Book.',
					'a8csp-project-template-features'
				) }
			</Notice>
		</PluginPrePublishPanel>
	);
};

registerPlugin( 'a8csp-project-template-features-book-cover-reminder', {
	render: BookCoverReminder,
} );
