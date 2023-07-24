/**
 * Internal dependencies
 */
import icon from '../../components/icon';
import edit from './edit';

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';

const name = 'affiliatewp/affiliate-content';

const settings = {
	title: __( 'Affiliate Content', 'affiliatewp-blocks' ),
	description: __(
		'Restrict content to logged-in affiliates.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Content', 'affiliatewp-blocks' ),
		__( 'Restrict', 'affiliatewp-blocks' ),
	],
	category: 'affiliatewp',
	icon,
	supports: {
		html: false,
	},
	edit,
	save( { className } ) {
		return (
			<div className={ className }>
				<InnerBlocks.Content />
			</div>
		);
	}
}
export { name, settings };