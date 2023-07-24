/**
 * External dependencies
 */
const { __ } = wp.i18n;

const LoginForm = () => {

	return (
		<div id="affwp-login-form" className="affwp-form">

			<h3>{ __( 'Log into your account', 'affiliatewp-blocks' ) }</h3>

			<p>
				<label htmlFor="affwp-login-user-login">{ __( 'Username', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-login-user-login"
					className="required"
					type="text"
					name="affwp_user_login"
					title={ __( 'Username', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p>
				<label htmlFor="affwp-login-user-pass">{ __( 'Password', 'affiliatewp-blocks' ) }</label>
				<input
					id="affwp-login-user-pass"
					className="password required"
					type="password"
					name="affwp_user_pass"
				/>
			</p>

			<p>
				<label
					className="affwp-user-remember"
					htmlFor="affwp-user-remember"
				>
						<input
							id="affwp-user-remember"
							className="required"
							type="checkbox"
							name="affwp_user_remember"
						/>
						{ __( 'Remember Me', 'affiliatewp-blocks' ) }
				</label>
			</p>

			<p>
				<input
					className="button"
					type="submit"
					value={ __( 'Log In', 'affiliatewp-blocks' ) }
				/>
			</p>

			<p className="affwp-lost-password">
				<a>{ __( 'Lost your password?', 'affiliatewp-blocks' ) }</a>
			</p>

		</div>
	);
}
export default LoginForm;