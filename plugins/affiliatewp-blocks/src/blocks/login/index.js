import icon from '../../components/icon';
import edit from './edit';
import { __ } from '@wordpress/i18n';

const name = 'affiliatewp/login';

const settings = {
	title: __( 'Affiliate Login', 'affiliatewp-blocks' ),
	description: __(
		'Allow your affiliates to login.',
		'affiliatewp-blocks'
	),
	keywords: [
		__( 'Login', 'affiliatewp-blocks' ),
		__( 'Form', 'affiliatewp-blocks' ),
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