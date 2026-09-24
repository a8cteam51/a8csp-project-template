/**
 * Registers the Book Count block in the editor, where a placeholder stands in for the live count
 * that `render.php` draws on the front end.
 *
 * To remove this block, follow the recipe in `includes/blocks.php`.
 */

import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

const Edit = () => (
	<p { ...useBlockProps() }>
		{ __(
			'Book Count: the number of published Books, linked to the Book archive.',
			'a8csp-project-template-features'
		) }
	</p>
);

// A dynamic block saves no markup, which registerBlockType's default save() already returns.
registerBlockType( metadata.name, { edit: Edit } );
