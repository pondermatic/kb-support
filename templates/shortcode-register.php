<?php
/**
 * This template is used to display the registration form with [kbs_register]
 */
global $kbs_register_redirect; ?>
<?php if ( ! is_user_logged_in() ) : ?>

    <div id="kbs_register_form_wrap">
        <?php do_action( 'kbs_notices' ); ?>
        <form id="kbs_register_form" class="kbs_form" action="" method="post">
            <?php do_action( 'kbs_register_form_fields_top' ); ?>
    
            <fieldset id="kbs_register_form_fields">
                <legend><?php _e( 'Register New Account', 'kb-support' ); ?></legend>
    
                <?php do_action( 'kbs_register_form_fields_before' ); ?>
    
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kbs-first-name"><?php _e( 'First Name', 'kb-support' ); ?></label>
                            <input type="text" name="kbs_user_first_name" class="required kbs-input" id="kbs-user-first-name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kbs-last-name"><?php _e( 'Last Name', 'kb-support' ); ?></label>
                            <input type="text" name="kbs_user_last_name" class="required kbs-input" id="kbs-user-last-name" required>
                        </div>
                    </div><!-- .row -->

                    <div class="mb-3">
                        <label for="kbs-email"><?php _e( 'Email Address', 'kb-support' ); ?></label>
                        <input type="email" name="kbs_user_email" class="required kbs-input" id="kbs-email">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kbs-user-pass"><?php _e( 'Password', 'kb-support' ); ?></label>
                            <input type="password" name="kbs_user_pass" class="required kbs-input" id="kbs-user-pass" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kbs-user-pass2"><?php _e( 'Confirm Password', 'kb-support' ); ?></label>
                            <input type="password" name="kbs_user_pass2" class="required kbs-input" id="kbs-user-pass2" required>
                        </div>
                    </div><!-- .row -->

                    <?php do_action( 'kbs_register_form_fields_before_submit' ); ?>
                    <input type="hidden" name="kbs_honeypot" value="" />
                    <input type="hidden" name="kbs_action" value="user_register" />
                    <input type="hidden" name="kbs_redirect" value="<?php echo esc_url( $kbs_register_redirect ); ?>"/>
                    <input class="button" name="kbs_register_submit" type="submit" value="<?php esc_attr_e( 'Register', 'kb-support' ); ?>" />
                
                    <?php do_action( 'kbs_register_form_fields_after' ); ?>
                </div><!-- .container -->
    
                <?php do_action( 'kbs_register_form_fields_after' ); ?>
            </fieldset>
    
            <?php do_action( 'kbs_register_form_fields_bottom' ); ?>
        </form>
    </div>
<?php else : ?>

	<div class="kbs_alert kbs_alert_warn">
    	<?php _e( 'You are already logged in. No registration is required.', 'kb-support' ); ?>
    </div>

<?php endif; ?>
