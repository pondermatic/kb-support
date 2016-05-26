<?php
/**
 * This template is used to display the form for submitting a ticket [kbs_form]
 */
?>

<form id="kbs_ticket_form" class="kbs_form" action="" method="post">
	<?php do_action( 'kbs_ticket_form_fields_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Submit a Support Ticket', 'kb-support' ); ?></legend>

		<?php do_action( 'kbs_ticket_form_fields_before' ); ?>

		<p>
			<label for="kbs-firstname-ticket"><?php _e( 'Username', 'kb-support' ); ?></label>
			<input id="kbs-firstname-ticket" class="required kbs-input" type="text" name="kbs_firstname_ticket" title="<?php esc_attr_e( 'First Name', 'kb-support' ); ?>" />
		</p>
        
        <?php do_action( 'kbs_ticket_form_fields_before_lastname' ); ?>
        
        <p>
			<label for="kbs-lastname-ticket"><?php _e( 'Last Name', 'kb-support' ); ?></label>
			<input id="kbs-lastname-ticket" class="required kbs-input" type="text" name="kbs_lastname_ticket" title="<?php esc_attr_e( 'Last Name', 'kb-support' ); ?>" />
		</p>
        
        <?php do_action( 'kbs_ticket_form_fields_before_email' ); ?>

		<p>
			<label for="kbs-ticket-email"><?php _e( 'Email', 'kb-support' ); ?></label>
			<input id="kbs-ticket-email" class="required kbs-input" type="email" name="kbs_ticket_email" title="<?php esc_attr_e( 'Email Address', 'kb-support' ); ?>" />
		</p>

		<p>
			<label for="kbs-user-pass"><?php _e( 'Password', 'kb-support' ); ?></label>
			<input id="kbs-user-pass" class="password required kbs-input" type="password" name="kbs_user_pass" />
		</p>

		<p>
			<label for="kbs-user-pass2"><?php _e( 'Confirm Password', 'kb-support' ); ?></label>
			<input id="kbs-user-pass2" class="password required kbs-input" type="password" name="kbs_user_pass2" />
		</p>


		<?php do_action( 'kbs_register_form_fields_before_submit' ); ?>

		<p>
			<input type="hidden" name="kbs_honeypot" value="" />
			<input type="hidden" name="kbs_action" value="user_register" />
			<input type="hidden" name="kbs_redirect" value="<?php echo esc_url( $kbs_register_redirect ); ?>"/>
			<input class="button" name="kbs_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'kb-support' ); ?>" />
		</p>

		<?php do_action( 'kbs_register_form_fields_after' ); ?>
	</fieldset>

	<?php do_action( 'kbs_register_form_fields_bottom' ); ?>
</form>
