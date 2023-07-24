/**
 * Internal dependencies
 */
import LoginForm from '../../components/login-form';
import RegistrationForm from '../../components/registration-form';

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { info } from '@wordpress/icons';
import { Placeholder } from '@wordpress/components';

function AffiliateArea( {
	attributes,
}) {

	const affiliateAreaForms = affwp_blocks.affiliate_area_forms || 'both';
	const allowAffiliateRegistration = affwp_blocks.allow_affiliate_registration;

	return (
	<>

		{ ( allowAffiliateRegistration && 'both' === affiliateAreaForms || 'registration' === affiliateAreaForms ) &&
		<RegistrationForm attributes={attributes} />
		}

		{ ( 'both' === affiliateAreaForms || 'login' === affiliateAreaForms ) &&
		<LoginForm attributes={attributes} />
		}

		{ 'none' === affiliateAreaForms &&
		<Placeholder icon={ info } label={ __( 'The "Affiliate Area Forms" setting is configured to show no forms.', 'affiliatewp-blocks' ) } />
		}

	</>
	);
}
export default AffiliateArea;
