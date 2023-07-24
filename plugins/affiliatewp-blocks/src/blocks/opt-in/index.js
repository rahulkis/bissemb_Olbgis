import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/opt-in';

const settings = {
	title: __( 'Opt-in Form', 'affiliatewp-blocks' ),
	description: __(
		'Show an opt-in form that integrates with Mailchimp, ActiveCampaign, or ConvertKit.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Opt-in', 'affiliatewp-blocks' ),
		__( 'Form', 'affiliatewp-blocks' ),
		__( 'Sign Up', 'affiliatewp-blocks' ),
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