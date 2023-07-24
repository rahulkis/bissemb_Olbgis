import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/affiliate-creative';

const settings = {
	title: __( 'Affiliate Creative', 'affiliatewp-blocks' ),
	description: __(
		'Show an affiliate creative.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Creative', 'affiliatewp-blocks' ),
		__( 'Banner', 'affiliatewp-blocks' ),
	],
	category: 'affiliatewp',
	icon,
	supports: {
		html: false,
	},
	edit,
	save() {
		return null;
	},
}
export { name, settings };