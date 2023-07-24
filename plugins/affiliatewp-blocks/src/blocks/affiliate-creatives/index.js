import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/affiliate-creatives';

const settings = {
	title: __( 'Affiliate Creatives', 'affiliatewp-blocks' ),
	description: __(
		'Show creatives to your affiliates.',
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