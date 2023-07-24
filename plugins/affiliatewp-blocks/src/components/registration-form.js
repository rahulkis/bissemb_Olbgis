/**
 * External dependencies
 */
const { __ } = wp.i18n;

const RegistrationForm = () => {

	const hasTermsOfUse = affwp_blocks.terms_of_use;
	const requiredFields = affwp_blocks.required_registration_fields;
	const termsOfUseLabel = affwp_blocks.terms_of_use_label;

	return (
		<div id="affwp-register-form" className="affwp-form">

			<h3>{ __( 'Register a new affiliate account', 'affiliatewp-blocks' ) }</h3>

			<p>
				<label htmlFor="affwp-user-name">{ __( 'Your Name', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-user-name"
					className="required"
					type="text"
					name="affwp_user_name"
					title={ __( 'Your Name', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-user-login">{ __( 'Username', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-user-login"
					className="required"
					type="text"
					name="affwp_user_login"
					title={ __( 'Username', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-user-email">{ __( 'Account Email', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-user-email"
					className="required"
					type="email"
					name="affwp_user_email"
					title={ __( 'Email Address', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-payment-email">{ __( 'Payment Email', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-payment-email"
					className=""
					type="email"
					name="affwp_payment_email"
					title={ __( 'Payment Email Address', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-user-url">{ __( 'Website URL', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-user-url"
					className="required"
					type="text"
					name="affwp_user_url"
					title={ __( 'Website URL', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-promotion-method">{ __( 'How will you promote us?', 'affiliatewp-blocks' ) }</label>
				<textarea
					id="affwp-promotion-method"
					className="required"
					rows="5"
					cols="30"
					name="affwp_promotion_method"
				/>
			</p>

			{ requiredFields.password &&
			<p>
				<label htmlFor="affwp-user-pass">{ __( 'Password', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-user-pass"
					className="password"
					type="password"
					name="affwp_user_pass"
				/>
			</p>
			}

			{ requiredFields.password &&
			<p>
				<label htmlFor="affwp-user-pass2">{ __( 'Confirm Password', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-user-pass2"
					className="password"
					type="password"
					name="affwp_user_pass2"
				/>
			</p>
			}

			{ hasTermsOfUse &&
			<p>
				<label
					className="affwp-tos"
					htmlFor="affwp-tos"
				>
					<input
						id="affwp-tos"
						className=""
						type="checkbox"
						name="affwp_tos"
					/>
					{ termsOfUseLabel }
				</label>
			</p>
			}

			<p>
				<input
					className="button"
					type="submit"
					value={ __( 'Register', 'affiliatewp-blocks' ) }
				/>
			</p>

		</div>
	);
}
export default RegistrationForm;