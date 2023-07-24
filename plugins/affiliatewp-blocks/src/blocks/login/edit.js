/**
 * Internal dependencies
 */
import LoginForm from '../../components/login-form';

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/editor';
import {
	PanelBody,
	TextControl,
} from '@wordpress/components';

function AffiliateLogin( {
	attributes,
	setAttributes,
}) {

	const { redirect } = attributes;

	return (
		<>

		<InspectorControls>
			<PanelBody>

				<TextControl
					label={ __( 'Redirect' ) }
					value={ redirect }
					onChange={ ( redirect ) => setAttributes({ redirect }) }
				/>

			</PanelBody>
		</InspectorControls>

		<LoginForm />

	</>
	);
}
export default AffiliateLogin;