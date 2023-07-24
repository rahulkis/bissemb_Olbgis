/**
 * Internal dependencies
 */
import RegistrationForm from '../../components/registration-form';

/**
 * External dependencies
 */

import {
	PanelBody,
	TextControl,
	Notice,
} from '@wordpress/components';

import { __ } from '@wordpress/i18n';

import { InspectorControls } from '@wordpress/editor';

function AffiliateRegistration( {
	attributes,
	setAttributes,
}) {

	const { redirect } = attributes;
	const allowAffiliateRegistration = affwp_blocks.allow_affiliate_registration;

	return (
		<>
			<InspectorControls>
				{ ! allowAffiliateRegistration &&
				<Notice
					className={"affwp-block-inspector-notice"}
					isDismissible={ false }
					status="warning"
				>
					{ __( 'Affiliates will not see this form as "Allow Affiliate Registration" is disabled.', 'affiliatewp-blocks' ) }
				</Notice>
				}

				<PanelBody>
					<TextControl
						label={ __( 'Redirect' ) }
						value={ redirect }
						onChange={ ( redirect ) => setAttributes({ redirect }) }
					/>
				</PanelBody>
			</InspectorControls>

			<RegistrationForm />
		</>
	);
}
export default AffiliateRegistration;