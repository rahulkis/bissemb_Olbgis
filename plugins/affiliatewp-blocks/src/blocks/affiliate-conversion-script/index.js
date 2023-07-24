import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/affiliate-conversion-script';

const settings = {
	title: __( 'Affiliate Conversion Script', 'affiliatewp-blocks' ),
	description: __(
		'Track referrals for successful conversions of custom success pages.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Conversion', 'affiliatewp-blocks' ),
		__( 'Script', 'affiliatewp-blocks' ),
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