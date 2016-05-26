<?php
/**
 * This template is used to display the login form with [kbs_login]
 */
global $kbs_login_redirect;
if ( ! is_user_logged_in() ) : ?>
	<form id="kbs_login_form" class="kbs_form" action="" method="post">
		<fieldset>
			<span><legend><?php _e( 'Log into Your Account', 'kb-support' ); ?></legend></span>
			<?php do_action( 'kbs_login_fields_before' ); ?>
			<p>
				<label for="kbs_user_login"><?php _e( 'Username or Email', 'kb-support' ); ?></label>
				<input name="kbs_user_login" id="kbs_user_login" class="required kbs-input" type="text" title="<?php _e( 'Username or Email', 'kb-support' ); ?>"/>
			</p>
			<p>
				<label for="kbs_user_pass"><?php _e( 'Password', 'kb-support' ); ?></label>
				<input name="kbs_user_pass" id="kbs_user_pass" class="password required kbs-input" type="password"/>
			</p>
			<p>
				<input type="hidden" name="kbs_redirect" value="<?php echo esc_url( $kbs_login_redirect ); ?>"/>
				<input type="hidden" name="kbs_login_nonce" value="<?php echo wp_create_nonce( 'kbs-login-nonce' ); ?>"/>
				<input type="hidden" name="kbs_action" value="user_login"/>
				<input id="kbs_login_submit" type="submit" class="kbs_submit" value="<?php _e( 'Log In', 'kb-support' ); ?>"/>
			</p>
			<p class="kbs-lost-password">
				<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'kb-support' ); ?>">
					<?php _e( 'Lost Password?', 'kb-support' ); ?>
				</a>
			</p>
			<?php do_action( 'kbs_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php else : ?>
	<p class="kbs-logged-in"><?php _e( 'You are already logged in', 'kb-support' ); ?></p>
<?php endif; ?>
