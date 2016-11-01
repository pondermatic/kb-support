<?php
/**
 * This template is used to display the customer profile editor form with [kbs_profile_editor]
 *
 * @uses	$current_user	The user object of the currently logged in user.
 */
if ( ! is_user_logged_in() ) : ?>

	<?php echo kbs_display_notice( 'profile_login' ); ?>
	<?php echo kbs_login_form(); ?>

<?php else :
	global $current_user;

	do_action( 'kbs_notices' ); ?>

	<div id="kbs_item_wrapper" class="kbs_profile_wrapper" style="float: left">
		<div class="profile_info_wrapper data_section">

			<?php do_action( 'kbs_profile_editor_before' ); ?>

			<form id="kbs_profile_editor_form" class="kbs_form" action="" method="post">
            	<div class="kbs_item_info customer_info">
                	<fieldset id="kbs_ticket_info_details">
                        <legend><?php _e( 'Update Name, Email and Password', 'kb-support' ); ?></legend>

						<div class="kbs_profile_editor_firstname">
                            <p>
                            	<label for="kbs_first_name"><?php _e( 'First Name', 'kb-support' ); ?></label>
                                <input type="text" class="kbs-input" name="kbs_first_name" id="kbs-first-name" value="<?php echo esc_attr( $current_user->first_name ); ?>" />
                            </p>
                        </div>

                        <div class="kbs_profile_editor_lastname">
                            <p>
                            	<label for="kbs_last_name"><?php _e( 'Last Name', 'kb-support' ); ?></label>
                                <input type="text" class="kbs-input" name="kbs_last_name" id="kbs-last-name" value="<?php echo esc_attr( $current_user->last_name ); ?>" />
                            </p>
                        </div>

						<div class="kbs_profile_editor_displayname">
                        	<p>
                                <label for="kbs_display_name"><?php _e( 'Display Name', 'kb-support' ); ?></label>
                                <select name="kbs_display_name" id="kbs_display_name" class="select kbs-select">

								<?php if ( ! empty( $current_user->first_name ) ): ?>
	                                <option <?php selected( $current_user->display_name, $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->first_name ); ?>"><?php echo esc_html( $current_user->first_name ); ?></option>
                                <?php endif; ?>

                                    <option <?php selected( $current_user->display_name, $current_user->user_nicename ); ?> value="<?php echo esc_attr( $current_user->user_nicename ); ?>"><?php echo esc_html( $current_user->user_nicename ); ?></option>

                                    <?php if ( ! empty( $current_user->last_name ) ): ?>
                                        <option <?php selected( $current_user->display_name, $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->last_name ); ?>"><?php echo esc_html( $current_user->last_name ); ?></option>
                                    <?php endif; ?>

                                    <?php if ( ! empty( $current_user->first_name ) && ! empty( $current_user->last_name ) ): ?>
                                        <option <?php selected( $current_user->display_name, $current_user->first_name . ' ' . $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->first_name . ' ' . $current_user->last_name ); ?>"><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></option>
                                        <option <?php selected( $current_user->display_name, $current_user->last_name . ' ' . $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->last_name . ' ' . $current_user->first_name ); ?>"><?php echo esc_html( $current_user->last_name . ' ' . $current_user->first_name ); ?></option>
                                    <?php endif; ?>

                                </select>

                                <?php do_action( 'kbs_profile_editor_name' ); ?>
                            </p>
                        </div>

						<?php do_action( 'kbs_profile_editor_after_name' ); ?>

						<div class="kbs_profile_editor_email">
                            <p>
                                <label for="kbs_email"><?php _e( 'Primary Email', 'kb-support' ); ?></label>

                                <?php $customer = new KBS_Customer( $current_user->ID, true ); ?>

                                <?php if ( $customer->id > 0 ) : ?>
                
                                    <?php if ( 1 === count( $customer->emails ) ) : ?>
                                        <input name="kbs_email" id="kbs_email" class="text kbs-input required" type="email" value="<?php echo esc_attr( $customer->email ); ?>" />
                                    <?php else: ?>
                                        <?php
                                            $emails           = array();
                                            $customer->emails = array_reverse( $customer->emails, true );
                
                                            foreach ( $customer->emails as $email ) {
                                                $emails[ $email ] = $email;
                                            }
                
                                            $email_select_args = array(
                                                'options'          => $emails,
                                                'name'             => 'kbs_email',
                                                'id'               => 'kbs_email',
                                                'selected'         => $customer->email,
                                                'show_option_none' => false,
                                                'show_option_all'  => false,
                                            );
                
                                            echo KBS()->html->select( $email_select_args );
                                        ?>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <input name="kbs_email" id="kbs_email" class="text kbs-input " type="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" />
                                <?php endif; ?>
                
                                <?php do_action( 'kbs_profile_editor_email' ); ?>
                
                            </p>
						</div>

						<?php if ( $customer->id > 0 && count( $customer->emails ) > 1 ) : ?>

							<div class="kbs_profile_editor_additional_emails">
                                <p>
                                	<label for="kbs_emails"><?php _e( 'Additional Emails', 'kb-support' ); ?></label>
                                    <ul class="kbs-profile-emails">
										<?php foreach ( $customer->emails as $email ) : ?>

                                            <?php if ( $email === $customer->email ) { continue; } ?>

                                            <li class="kbs-profile-email">
                                                <?php echo $email; ?>
                                                <span class="actions">
                                                    <?php
                                                        $remove_url = wp_nonce_url(
                                                            add_query_arg(
                                                                array(
                                                                    'email'      => $email,
                                                                    'kbs_action' => 'profile-remove-email',
                                                                    'redirect'   => esc_url( kbs_get_current_page_url() ),
                                                                )
                                                            ),
                                                            'kbs-remove-customer-email'
                                                        );
                                                    ?>
                                                    <a href="<?php echo $remove_url ?>" class="delete"><?php _e( 'Remove', 'kb-support' ); ?></a>
                                                </span>
                                            </li>

                                        <?php endforeach; ?>
                                    </ul>
                                </p>
                            </div>

                        <?php endif; ?>

						<?php do_action( 'kbs_profile_editor_after_email' ); ?>

						<div class="kbs_profile_editor_password">
                            <p>
                                <label for="kbs_new_user_pass1"><?php _e( 'New Password', 'kb-support' ); ?></label>
                                <input name="kbs_new_user_pass1" id="kbs_new_user_pass1" class="password kbs-input" type="password"/>
                            </p>
						</div>

						<div class="kbs_profile_editor_password_confirm">
							<p>
                                <label for="kbs_new_user_pass2"><?php _e( 'Re-enter Password', 'kb-support' ); ?></label>
                                <input name="kbs_new_user_pass2" id="kbs_new_user_pass2" class="password kbs-input" type="password"/>
								<?php do_action( 'kbs_profile_editor_password' ); ?>
                            </p>
                        </div>

						<?php do_action( 'kbs_profile_editor_after_password' ); ?>

						<div class="kbs_profile_editor_save">
                            <p id="kbs_profile_submit_wrap">
                                <input type="hidden" name="kbs_profile_editor_nonce" value="<?php echo wp_create_nonce( 'kbs-profile-editor-nonce' ); ?>"/>
                                <input type="hidden" name="kbs_action" value="edit_user_profile" />
                                <input type="hidden" name="kbs_redirect" value="<?php echo esc_url( kbs_get_current_page_url() ); ?>" />
                                <input name="kbs_profile_editor_submit" id="kbs_profile_editor_submit" type="submit" class="kbs_submit" value="<?php _e( 'Save Changes', 'kb-support' ); ?>"/>
                            </p>
                        </div>

                    </fieldset>
                </div>
            </form>

        </div>
    </div>

	<?php do_action( 'kbs_profile_editor_after' ); ?>

<?php endif; ?>
