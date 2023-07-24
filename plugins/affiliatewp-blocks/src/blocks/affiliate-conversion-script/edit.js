/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import icon from '../../components/icon';

import { InspectorControls } from '@wordpress/editor';

import {
	PanelBody,
	TextControl,
	SelectControl,
	Icon,
	Placeholder
} from '@wordpress/components';

function AffiliateConversionScript( {
	attributes,
	setAttributes,
}) {

	const {
		amount,
		description,
		reference,
		context,
		campaign,
		status,
		type,
	} = attributes;

	const affiliateWpIcon = () => {
		return (
			<Icon
				icon={ icon }
				color={ true }
			/>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody>
					<TextControl
						label={ __( 'Amount', 'affiliatewp-blocks' ) }
						type={'number'}
						value={ amount }
						onChange={ ( amount ) => setAttributes( { amount } ) }
						help={ __( 'The purchase amount that the referral should be based on.', 'affiliatewp-blocks' ) }
					/>
					<SelectControl
						label={ __( 'Status', 'affiliatewp-blocks' ) }
						value={ status }
						options={
							[
								{
									label: __( 'Pending', 'affiliatewp-blocks' ),
									value: 'pending'
								},
								{
									label: __( 'Unpaid', 'affiliatewp-blocks' ),
									value: 'unpaid'
								},
								{
									label: __( 'Paid', 'affiliatewp-blocks' ),
									value: 'paid'
								},
								{
									label: __( 'Rejected', 'affiliatewp-blocks' ),
									value: 'rejected'
								},
							]
						}
						onChange={ ( status ) => setAttributes( { status } ) }
						help={ __( 'The status to give the referral.', 'affiliatewp-blocks' ) }
					/>
					<TextControl
						label={ __( 'Description', 'affiliatewp-blocks' ) }
						value={ description }
						onChange={ ( description ) => setAttributes( { description } ) }
						help={ __( 'A description logged with the referral.', 'affiliatewp-blocks' ) }
					/>
					<TextControl
						label={ __( 'Context', 'affiliatewp-blocks' ) }
						value={ context }
						onChange={ ( context ) => setAttributes( { context } ) }
						help={ __( 'A context for the referral.', 'affiliatewp-blocks' ) }
					/>
					<TextControl
						label={ __( 'Reference', 'affiliatewp-blocks' ) }
						value={ reference }
						onChange={ ( reference ) => setAttributes( { reference } ) }
						help={ __( 'A unique reference variable for the affiliate.', 'affiliatewp-blocks' ) }
					/>
					<TextControl
						label={ __( 'Campaign', 'affiliatewp-blocks' ) }
						value={ campaign }
						onChange={ ( campaign ) => setAttributes( { campaign } ) }
						help={ __( 'The referral\'s  campaign name.', 'affiliatewp-blocks' ) }
					/>
					<SelectControl
						label={ __( 'Type', 'affiliatewp-blocks' ) }
						value={ type }
						options={
							[
								{
									label: __( 'Sale', 'affiliatewp-blocks' ),
									value: 'sale'
								},
								{
									label: __( 'Opt-In', 'affiliatewp-blocks' ),
									value: 'opt-in'
								},
								{
									label: __( 'Lead', 'affiliatewp-blocks' ),
									value: 'lead'
								},
							]
						}
						onChange={ ( type ) => setAttributes( { type } ) }
						help={ __( 'The type of referral.', 'affiliatewp-blocks' ) }
					/>
				</PanelBody>
			</InspectorControls>

			<>
				<Placeholder
					icon={ affiliateWpIcon }
					label={ __( 'Affiliate Conversion Script', 'affiliatewp-blocks' ) }
				>
				</Placeholder>
			</>

		</>
	);
}
export default AffiliateConversionScript;