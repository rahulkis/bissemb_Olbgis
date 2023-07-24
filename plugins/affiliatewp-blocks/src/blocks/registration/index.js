import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/registration';

const settings = {
	title: __( 'Affiliate Registration', 'affiliatewp-blocks' ),
	description: __(
		'Allow your affiliates to register.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Registration', 'affiliatewp-blocks' ),
		__( 'Form', 'affiliatewp-blocks' ),
		__( 'Register', 'affiliatewp-blocks' ),
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