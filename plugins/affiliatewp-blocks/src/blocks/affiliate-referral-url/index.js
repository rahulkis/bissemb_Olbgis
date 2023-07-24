import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/affiliate-referral-url';

const settings = {
	title: __( 'Affiliate Referral URL', 'affiliatewp-blocks' ),
	description: __(
		'Display the referral URL of the currently logged in affiliate.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'URL', 'affiliatewp-blocks' ),
		__( 'Referral', 'affiliatewp-blocks' ),
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