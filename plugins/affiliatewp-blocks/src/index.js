import { registerBlockType } from '@wordpress/blocks';

import * as affiliateArea from './blocks/affiliate-area';
import * as affiliateRegistration from './blocks/registration';
import * as affiliateLogin from './blocks/login';
import * as affiliateContent from './blocks/affiliate-content';
import * as nonAffiliateContent from './blocks/non-affiliate-content';
import * as optIn from './blocks/opt-in';
import * as affiliateReferralUrl from './blocks/affiliate-referral-url';
import * as affiliateCreatives from './blocks/affiliate-creatives';
import * as affiliateCreative from './blocks/affiliate-creative';
import * as affiliateConversionScript from './blocks/affiliate-conversion-script';

const registerBlocks = () => {
	[
		affiliateArea,
		affiliateRegistration,
		affiliateLogin,
		affiliateContent,
		nonAffiliateContent,
		optIn,
		affiliateReferralUrl,
		affiliateCreatives,
		affiliateCreative,
		affiliateConversionScript
	].forEach( ( { name, settings } ) => {
		registerBlockType( name, settings );
	} );

};
registerBlocks();