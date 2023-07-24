import getReferralUrl from '../../utils/referral-url';

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { URLInput } from '@wordpress/block-editor';
import { InspectorControls } from '@wordpress/editor';
import {
	PanelBody,
	RadioControl
} from '@wordpress/components';

function AffiliateReferralUrl( {
	attributes,
	setAttributes,
	className
}) {

	const { url, format, pretty } = attributes;

	const referralUrl = getReferralUrl( {
		url,
		format,
		pretty
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody>
					<RadioControl
						label={ __( 'Pretty Affiliate URLs', 'affiliatewp-blocks' ) }
						selected={ pretty }
						options={ [
							{ label: __( 'Site Default', 'affiliatewp-blocks' ), value: 'default' },
							{ label: __( 'Yes', 'affiliatewp-blocks' ), value: 'yes' },
							{ label: __( 'No', 'affiliatewp-blocks' ), value: 'no' },
						] }
						onChange={ ( option ) => { setAttributes( { pretty: option } ) } }
					/>
					<RadioControl
						label={ __( 'Referral Format', 'affiliatewp-blocks' ) }
						selected={ format }
						options={ [
							{ label: __( 'Site Default', 'affiliatewp-blocks' ), value: 'default' },
							{ label: __( 'ID', 'affiliatewp-blocks' ), value: 'id' },
							{ label: __( 'Username', 'affiliatewp-blocks' ), value: 'username' },
						] }
						onChange={ ( option ) => { setAttributes( { format: option } ) } }
					/>
					<URLInput
						label={ __( 'Custom URL', 'affiliatewp-blocks' ) }
						className={ 'components-text-control__input is-full-width' }
						value={ url }
						onChange={ ( url, post ) => setAttributes( { url } ) }
						disableSuggestions={ true }
						placeholder={''}
					/>
				</PanelBody>
			</InspectorControls>

			<p className={className}>{referralUrl}</p>
		</>
	);
}
export default AffiliateReferralUrl;