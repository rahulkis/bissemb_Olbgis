/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/editor';
import {
	PanelBody,
	TextControl,
} from '@wordpress/components';

function OptInForm( {
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

		<div id="affwp-login-form" className="affwp-form">

			<p>
				<label htmlFor="affwp-opt-in-name">{ __( 'First Name', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-opt-in-name"
					className="required"
					type="text"
					name="affwp_first_name"
					title={ __( 'First Name', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-opt-in-name">{ __( 'Last Name', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-opt-in-name"
					className="required"
					type="text"
					name="affwp_last_name"
					title={ __( 'Last Name', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-opt-in-email">{ __( 'Email Address', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-opt-in-email"
					className="required"
					type="text"
					name="affwp_email"
					title={ __( 'Email Address', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<input
					className="button"
					type="submit"
					value={ __( 'Subscribe', 'affiliatewp-blocks' ) }
				/>
			</p>

		</div>

	</>
	);
}
export default OptInForm;