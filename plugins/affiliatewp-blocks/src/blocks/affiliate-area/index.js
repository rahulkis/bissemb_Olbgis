import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/affiliate-area';

const settings = {
	title: __( 'Affiliate Area', 'affiliatewp-blocks' ),
	description: __(
		'Displays the Affiliate Registration and Login forms.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Affiliate Area', 'affiliatewp-blocks' ),
		__( 'Area', 'affiliatewp-blocks' ),
		__( 'Dashboard', 'affiliatewp-blocks' )
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