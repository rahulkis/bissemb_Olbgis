/**
 * Internal dependencies
 */
import AffiliateCreative from '../../components/affiliate-creative';
import icon from '../../components/icon';

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	ToggleControl,
	TextControl,
	Placeholder,
	Spinner,
	Icon
} from '@wordpress/components';

import { useState, useEffect } from '@wordpress/element';
import { InspectorControls } from '@wordpress/editor';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

function AffiliateCreatives( {
	attributes,
	setAttributes,
}) {
	const [ creatives, setCreatives ] = useState([]);
	const [ error, setError ] = useState();

	const { preview, number } = attributes;

	const CREATIVES_QUERY = {
		number,
		status: 'active'
	};

	useEffect(() => {
		let ignore = false;

		async function fetchData() {

			try {
				const result = await apiFetch( { path: addQueryArgs( `/affwp/v1/creatives/`, CREATIVES_QUERY ) } );

				if (!ignore) {
					setCreatives(result);
				}

			  } catch (error) {
				setError(error);
			  }

		}

		fetchData();

		return () => { ignore = true; }
	}, [number]);

	const hasCreatives = Array.isArray( creatives ) && creatives.length;

	const affiliateWpIcon = () => {
		return (
			<Icon
				icon={ icon }
				color={ true }
			/>
		);
	}

	const inspectorControls = (
		<InspectorControls>
			<PanelBody>

				<ToggleControl
					label={ __( 'Creative preview', 'affiliatewp-blocks' ) }
					checked={ !! preview }
					onChange={ ( value ) => setAttributes( { preview: value } ) }
					help={ __( 'Displays an image or text preview above the HTML code.', 'affiliatewp-blocks' ) }
				/>

				<TextControl
					label={ __( 'Number', 'affiliatewp-blocks' ) }
					type='number'
					value={ number }
					onChange={ ( number ) => setAttributes({ number }) }
					help={ __( 'The number of affiliate creatives to show.', 'affiliatewp-blocks' ) }
				/>

			</PanelBody>
		</InspectorControls>
	);

	if ( ! hasCreatives ) {

		const ErrorMessage = () => {
			if ( error ) {
				const message = error.message;

				return (
					<>
						{message}
					</>
				);

			}
			return false;
		}

		return (
			<>
				{ inspectorControls }

				<Placeholder icon={ affiliateWpIcon } label={ __( 'Affiliate Creatives', 'affiliatewp-blocks' ) }>
					{ ! Array.isArray( creatives ) ? (
						<Spinner />
					) : (
						<ErrorMessage />
					) }
				</Placeholder>
			</>
		);
	}

	return (
		<>
			{ inspectorControls }

			{ creatives.map(creative => (
				<AffiliateCreative
					key={creative.creative_id}
					id={creative.creative_id}
					name={creative.name}
					description={creative.description}
					image={creative.image}
					url={creative.url}
					text={creative.text}
					preview={preview}
				/>
			)) }
		</>
	);
}
export default AffiliateCreatives;