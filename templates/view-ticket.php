<?php
/**
 * This template is used to display a single ticket for a customer.
 *
 * @shortcode	[kbs_view_ticket]
 */
$singular = kbs_get_ticket_label_singular();
$plural   = kbs_get_ticket_label_plural();

if ( is_numeric( $_GET['ticket'] ) )	{
	$field = 'id';
} else	{
	$field = 'key';
}

$ticket = kbs_get_ticket_by( $field, $_GET['ticket'] );

if ( ! empty( $ticket->ID ) ) : ?>
	<?php
	$ticket = new KBS_Ticket( $ticket->ID );
	$customer = new KBS_Customer( $ticket->customer_id );
	?>
	<div id="kbs-ticket-container">
		<?php do_action( 'kbs_notices' ); ?>
        <?php do_action( 'kbs_before_single_ticket_form' ); ?>
	
        <div class="kbs_alert kbs_alert_error kbs_hidden"></div>
        <?php do_action( 'kbs_single_ticket_form_top' ); ?>

        <fieldset id="kbs_single_ticket_form_fields">
            <legend><?php printf( __( 'Support %s Details', 'kb-support' ), $singular ); ?></legend>
            <span class="right">
				<?php do_action( 'kbs_before_single_ticket_form_date' ); ?>
    
                <p class="ticket-date"><span class="data-label"><?php _e( 'Date', 'kb-support' ); ?>:</span> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->date ) ); ?></p>
    
                <p class="ticket-logged"><span class="data-label"><?php _e( 'Logged by', 'kb-support' ); ?>:</span> <?php echo $customer->name; ?></p>
    
                <p class="ticket-status"><span class="data-label"><?php _e( 'Status', 'kb-support' ); ?>:</span> <?php echo $ticket->status_nicename; ?></p>
    
                <p class="ticket-agent"><span class="data-label"><?php _e( 'Agent', 'kb-support' ); ?>:</span> <?php echo get_userdata( $ticket->agent_id )->display_name; ?></p>
    
                <?php do_action( 'kbs_before_single_ticket_form_subject' ); ?>
    
                <p class="ticket-subject"><span class="post-data-label"><?php _e( 'Subject', 'kb-support' ); ?>:</span> <?php esc_attr_e( $ticket->ticket_title ); ?></p>
    
                <p class="ticket-content"><span class="post-data-label"><?php _e( 'Content', 'kb-support' ); ?>:</span> 
                    <span class="ticket-content"><?php echo $ticket->get_content(); ?></a></span>
                </p>
            </span>

            <?php do_action( 'kbs_before_single_ticket_form_files' ); ?>

            <?php if ( ! empty( $ticket->files ) ) : ?>
                <p class="ticket-files"><span class="post-data-label"><?php _e( 'Attachments', 'kb-support' ); ?>:</span>
                    <ul>
                    <?php foreach( $ticket->files as $file ) : ?>
                        <li><a href="<?php echo wp_get_attachment_url( $file->ID ); ?>" target="_blank"><?php echo basename( get_attached_file( $file->ID ) ); ?></a></li>
                    <?php endforeach; ?>
                    </ul>
                </p>
            <?php endif; ?>
            <div id="kbs_secure_site_wrapper" class="right">
                <span class="id"># <?php echo kbs_get_ticket_id( $ticket->ID ); ?></span>
            </div>
        </fieldset>

				<?php do_action( 'kbs_before_single_ticket_form_replies' ); ?>

                <fieldset id="kbs_single_ticket_form_replies">
                    <legend><?php _e( 'Replies', 'kb-support' ); ?></legend>
                    <?php if ( ! empty( $ticket->replies ) ) : ?>

						<?php foreach( $ticket->replies as $reply ) : ?>
							<div id="kbs-ticket-reply-<?php echo $reply->ID; ?>" class="kbs-ticket-reply-head" data-item="reply-<?php echo $reply->ID; ?>">
                            	<p>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime(  $reply->post_date ) ); ?> 
									<?php _e( 'by', 'kb-support' ); ?>  
									<?php echo kbs_get_reply_author_name( $reply, true ); ?></p>
                            </div>
						<?php endforeach; ?>

					<?php else : ?>
						<p class="ticket-no-replies"><?php _e( 'No replies yet', 'kb-support' ); ?></p>
                    <?php endif; ?>

					<?php do_action( 'kbs_before_single_ticket_reply' ); ?>

					<?php do_action( 'kbs_after_single_ticket_reply' ); ?>

                </fieldset>

                <?php do_action( 'kbs_after_single_ticket_form_replies' ); ?>

				<?php do_action( 'kbs_single_ticket_form_bottom' ); ?>
			</form>
			<?php do_action( 'kbs_after_single_ticket_form' ); ?>
		</div>
	</div>
<?php else : ?>
	<?php echo kbs_display_notice( 'no_ticket' ); ?>
<?php endif; ?>
