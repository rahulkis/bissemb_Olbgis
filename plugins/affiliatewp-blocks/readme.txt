=== AffiliateWP - Blocks ===
Contributors:      sumobi
Tags:              AffiliateWP, affiliate, block, blocks, gutenberg, gutenberg blocks, editor
Requires at least: 5.4
Tested up to:      5.4.2
Stable tag:        1.0.1
Requires PHP:      7.3.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Blocks for AffiliateWP

== Description ==

> This plugin requires [AffiliateWP](https://affiliatewp.com/ "AffiliateWP") in order to function.

**What is AffiliateWP?**

[AffiliateWP](https://affiliatewp.com/ "AffiliateWP") provides a complete affiliate management system for your WordPress website that seamlessly integrates with all major WordPress e-commerce and membership platforms. It aims to provide everything you need in a simple, clean, easy to use system that you will love to use.

**AffiliateWP Blocks**

This plugin for AffiliateWP provides 10 blocks, one for each of [AffiliateWP's shortcodes](https://docs.affiliatewp.com/category/65-short-codes "AffiliateWP's shortcodes").

**Affiliate Registration Block**

The Affiliate Registration block displays an affiliate registration form on your website, allowing users to register as affiliates. An optional URL where affiliates are redirected to after completing their application can also be configured. Similarly to the `[affiliate_registration]` shortcode, the form will change depending on AffiliateWP's settings.

- If the password fields are not required, the password fields will not show on the form (or within the editor view)
- If a "Terms of Use" page has been set, it will output a "Agree to our Terms of Use and Privacy Policy" checkbox to the form
- The terms of use text will change based on the "Terms of Use Label" setting (and also within the editor).
- If "Allow Affiliate Registration" is not enabled, the form will still show within the editor but will display a notice in the block's settings. The form however will not be displayed on the front-end of your website.

**Affiliate Login Block**
The Affiliate Login block displays an affiliate login form on your website, allowing affiliates to login and view their Affiliate Area. An optional URL where affiliates are redirected to after login can also be configured.

**Affiliate Area Block**
The Affiliate Area block displays an affiliate login form and an affiliate registration form for users on your website. If AffiliateWP's "Allow Affiliate Registration" option is not enabled, only the login form will show. Similarly to the `[affiliate_area]` shortcode, the form(s) shown are configured via AffiliateWP's "Affiliate Area Forms" setting.

**Affiliate Referral URL Block**
The Affiliate Referral URL block displays an affiliate's referral URL on your website. When a logged in (and active) affiliate views the page, they will see their own unique referral URL to share.

When viewed from within the WordPress editor, the user configuring the block can select between the affiliate's ID or username, show pretty affiliate URLs, set a custom URL, or use the default options configured from AffiliateWP's settings. If the user happens to also be an affiliate, they will be shown a real preview of their own affiliate ID or username.

**Affiliate Creative Block**
The Affiliate Creative block displays a single creative on your website. The selected creative will only be visible to a logged in (and active) affiliate.

**Affiliate Creatives Block**
The Affiliate Creatives block displays all of the creatives on your website. The creatives will only be visible to a logged in (and active) affiliate. The user configuring the block can set the number of creatives to show (default is 20) and toggle the display the image/text preview.

**Opt-In Form Block**
The Opt-in Form block displays an opt-in form on your website which integrates with either Mailchimp, ActiveCampaign or ConvertKit. An optional URL where affiliates are redirected to after opt-in can also be configured.

**Affiliate Content Block**
The Affiliate Content block makes any block placed inside it only visible to a logged-in (and active) affiliate.

**Non Affiliate Content**
The Non Affiliate Content block makes any block placed inside it only visible to non-affiliates.

**Affiliate Conversion Script**
The Affiliate Conversion Script block can be placed on your website's "success" page. When a user arrives on the website via an affiliate link, and makes it through to this page via the checkout, signup form, or any other conversion process, a referral is created for the tracked affiliate.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/affiliatewp-blocks` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. On any post or page, click the block inserter and insert the provided blocks from the "AffiliateWP" category

== Changelog ==

= 1.0.1 =
* Fixed a typo which prevented affiliate creatives from being displayed on the front-end.

= 1.0.0 =
* Release