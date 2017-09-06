<?php
/**
 * This template is used to display a single ticket for a customer.
 *
 * @shortcode	[kbs_view_ticket]
 */
global $current_user;

$singular = kbs_get_ticket_label_singular();
$plural   = kbs_get_ticket_label_plural();
$visible  = false;

if ( is_numeric( $_GET['ticket'] ) )	{
	$field   = 'id';
	if ( is_user_logged_in() )	{
		$visible = true;
	}
} else	{
	$visible = true;
	$field   = 'key';
}

$ticket = kbs_get_ticket_by( $field, $_GET['ticket'] );

if ( $visible && ! empty( $ticket->ID ) ) :

	$ticket       = new KBS_Ticket( $ticket->ID );
	$use_user_id  = false;
	$customer_id  = $ticket->customer_id;
	$status_class = '';

	if ( is_user_logged_in() ) :
		$use_user_id = true;
		$customer_id = $current_user->ID;
	endif;

	$customer = new KBS_Customer( $customer_id, $use_user_id ); ?>

	<?php if ( empty( $customer->id ) || $customer->id != $ticket->customer_id ) : ?>

    	<?php echo kbs_display_notice( 'invalid_customer' ); ?>

    <?php else : ?>

		<?php do_action( 'kbs_notices' ); ?>
        <div id="kbs_item_wrapper" class="kbs_ticket_wrapper" style="float: left">
            <div class="ticket_info_wrapper data_section">

                <?php do_action( 'kbs_before_single_ticket_form', $ticket ); ?>

                <form<?php kbs_maybe_set_enctype(); ?> id="kbs_ticket_reply_form" class="kbs_form" action="" method="post">

                    <div class="kbs_item_info ticket_info">
                        <fieldset id="kbs_ticket_info_details">
                        <legend><?php printf( __( 'Support %s Details # %s', 'kb-support' ), $singular, kbs_format_ticket_number( kbs_get_ticket_number( $ticket->ID ) ) ); ?></legend>
                            <div class="ticket_files_wrapper right">

                                <?php do_action( 'kbs_before_single_ticket_form_files', $ticket ); ?>

                                <?php if ( ! empty( $ticket->files ) ) : ?>
                                    <strong><?php _e( 'File Attachments', 'kb-support' ); ?></strong>
                                    <span class="ticket_files info_item">

                                        <?php foreach( $ticket->files as $file ) : ?>
                                            <span class="info_item" data-key="file<?php echo $file->ID; ?>">
                                                <a href="<?php echo wp_get_attachment_url( $file->ID ); ?>" target="_blank"><?php echo basename( get_attached_file( $file->ID ) ); ?></a>
                                            </span>
                                        <?php endforeach; ?>

                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="ticket_main_wrapper left">

								<?php do_action( 'kbs_single_ticket_before_details', $ticket ); ?>

                                <span class="ticket_date info_item">
                                    <label><?php _e( 'Date', 'kb-support' ); ?>:</label> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->date ) ); ?>
                                </span>

								<?php do_action( 'kbs_single_ticket_after_date', $ticket ); ?>

                                <span class="ticket_customer_name info_item">
                                    <label><?php _e( 'Logged by', 'kb-support' ); ?>:</label> <?php echo kbs_email_tag_fullname( $ticket->ID ); ?>
                                </span>

								<?php do_action( 'kbs_single_ticket_after_logged_by', $ticket ); ?>

                                <span class="ticket_status info_item">
                                    <label><?php _e( 'Status', 'kb-support' ); ?>:</label> <?php echo $ticket->status_nicename; ?>
                                </span>

								<?php do_action( 'kbs_single_ticket_after_status', $ticket ); ?>

                                <span class="ticket_agent info_item">
                                	<?php if ( ! empty( $ticket->agent_id ) ) :
										$agent = get_userdata( $ticket->agent_id )->display_name;

										if ( kbs_display_agent_status() ) :
											$status_class = 'kbs_agent_status_' . kbs_get_agent_online_status( $ticket->agent_id );
										endif;
									else :
										$agent = __( 'No Agent Assigned', 'kb-support' );
									endif; ?>

                                    <label><?php _e( 'Agent', 'kb-support' ); ?>:</label> <span class="<?php echo $status_class; ?>"><?php echo $agent; ?></span>
                                </span>

								<?php do_action( 'kbs_single_ticket_after_agent', $ticket ); ?>

								<?php do_action( 'kbs_single_ticket_before_major_items', $ticket ); ?>

                                <div class="major_ticket_items">

                                    <span class="ticket_subject info_item">
                                        <label><?php _e( 'Subject', 'kb-support' ); ?>:</label> <?php echo esc_attr( $ticket->ticket_title ); ?>
                                    </span>

									<?php do_action( 'kbs_single_ticket_after_subject', $ticket ); ?>

                                    <span class="ticket_content info_item">
                                        <label><?php _e( 'Content', 'kb-support' ); ?>:</label> <?php echo $ticket->get_content(); ?>
                                    </span>

									<?php do_action( 'kbs_single_ticket_after_content', $ticket ); ?>

                                </div>

                            </div>

                        </fieldset>

                        <?php do_action( 'kbs_before_single_ticket_form_replies', $ticket ); ?>

                        <fieldset id="kbs_ticket_replies">
                        <legend><?php _e( 'Replies', 'kb-support' ); ?></legend>
                        <?php if ( ! empty( $ticket->replies ) ) : ?>
                            <ul>
                            <?php foreach( $ticket->replies as $reply ) : ?>

                                <?php $reply_content = apply_filters( 'the_content', $reply->post_content );
                                $reply_content = str_replace( ']]>', ']]&gt;', $reply_content ); ?>
    
                                <li id="kbs_ticket_reply-<?php echo $reply->ID; ?>" class="kbs-ticket-reply-head" data-item="reply-<?php echo $reply->ID; ?>">
                                    <span class="ticket_reply info-item">
                                        <a class="ticket_reply_content" data-key="<?php echo $reply->ID; ?>"><?php echo date_i18n( get_option( 'time_format' ) . ' \o\n ' . get_option( 'date_format' ), strtotime(  $reply->post_date ) ); ?> 
                                        <?php _e( 'by', 'kb-support' ); ?>  
                                        <?php echo kbs_get_reply_author_name( $reply->ID, true ); ?></a>
                                        <div id="ticket_response_<?php echo $reply->ID; ?>" class="single_reply kbs_hidden">
                                            <?php echo $reply_content; ?>
                                        </div>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                            </ul>

                        <?php else : ?>
                            <span class="ticket-no-replies info_item">
                                <?php _e( 'No replies yet', 'kb-support' ); ?>
                            </span>
                        <?php endif; ?>

                        <?php if( is_ssl() ) : ?>
                            <div id="kbs_secure_site_wrapper">
                                <span class="padlock"></span>
                                <span><?php _e( 'This form is secured and encrypted via SSL', 'kb-support' ); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php do_action( 'kbs_before_single_ticket_reply', $ticket ); ?>

                        <?php if ( 'closed' != $ticket->status ) : ?>

                            <div class="kbs_alert kbs_alert_error kbs_hidden"></div>
                            <div class="ticket_reply_fields">

                                <strong><?php _e( 'Add a Reply', 'kb-support' ); ?></strong>

                                <?php $wp_settings  = apply_filters( 'kbs_ticket_reply_editor_settings', array(
                                    'media_buttons' => false,
                                    'textarea_rows' => get_option( 'default_post_edit_rows', 10 ),
                                    'teeny'         => true,
                                    'quicktags'     => false
                                ) );
                                echo wp_editor( '', 'kbs_reply', $wp_settings ); ?>

                                <?php if ( kbs_file_uploads_are_enabled() ) : ?>
                                    <?php do_action( 'kbs_before_single_ticket_files', $ticket ); ?>
                                    <div class="reply_files">
                                        <p>
                                            <label for="kbs_files"><?php _e( 'Attach Files', 'kb-support' ); ?></label><br />
                                            <?php for ( $i = 1; $i <= kbs_get_max_file_uploads(); $i++ ) : ?>
                                                <input type="file" class="kbs-input" name="kbs_files[]" />
                                            <?php endfor; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php do_action( 'kbs_before_single_ticket_email', $ticket ); ?>

                                <?php if ( ! is_user_logged_in() ) : ?>

                                    <div class="reply_confirm_email">
                                        <p><label for="kbs_confirm_email"><?php _e( 'Confirm your Email Address', 'kb-support' ); ?></label>
                                            <span class="kbs-description"><?php _e( 'So we can verify your identity', 'kb-support' ); ?></span>
                                            <input type="email" class="kbs-input" name="kbs_confirm_email" id="kbs-confirm-email" />
                                        </p>
                                    </div>

                                <?php endif; ?>

                                <?php do_action( 'kbs_before_single_ticket_close', $ticket ); ?>

                                <div class="reply_close">
                                    <p><input type="checkbox" name="kbs_close_ticket" id="kbs-close-ticket" /> 
                                        <?php printf( __( 'This %s can be closed', 'kb-support' ), strtolower( $singular ) ); ?>
                                    </p>
                                </div>

                                <?php kbs_render_hidden_reply_fields( $ticket->ID ); ?>
                                <?php do_action( 'kbs_before_single_ticket_reply_submit', $ticket ); ?>
                                <input class="button" name="kbs_ticket_reply" id="kbs_reply_submit" type="submit" value="<?php _e( 'Reply', 'kb-support' ); ?>" />

                            </div>

                        <?php else : ?>
                            <div class="kbs_alert kbs_alert_info"><?php printf( __( 'This %s is closed.', 'kb-support' ), strtolower( $singular ) ); ?></div>
                        <?php endif; ?>

                    </fieldset>

                    </div>

                </form>

                <?php do_action( 'kbs_after_single_ticket_form', $ticket ); ?>

            </div>
        </div>

	<?php endif; ?>

<?php elseif ( ! $visible ) : ?>
	<?php
	$args = array();
	if ( isset( $_GET['ticket'] ) )	{
		$args = array( 'ticket' => $_GET['ticket'] );
	}
    $redirect  = add_query_arg( $args, get_permalink( kbs_get_option( 'tickets_page' ) ) );
	
	?>
	<?php echo kbs_display_notice( 'ticket_login' ); ?>
    <?php echo kbs_login_form( $redirect ); ?>
<?php else : ?>
	<?php echo kbs_display_notice( 'no_ticket' ); ?>
<?php endif; ?>
