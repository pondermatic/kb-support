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
$ticket = new KBS_Ticket( $ticket->ID );

if ( ! empty( $ticket->ID ) ) : ?>
	<?php $customer = new KBS_Customer( $ticket->customer_id ); ?>
	<div id="kbs_ticket_wrap">
		<?php do_action( 'kbs_notices' ); ?>
		<div id="kbs_ticket_form_wrap" class="kbs_clearfix">
			<?php do_action( 'kbs_before_single_ticket_form' ); ?>
	
			<form<?php kbs_maybe_set_enctype(); ?> id="kbs_single_ticket_form" class="kbs_form" action="" method="post">
				<div class="kbs_alert kbs_alert_error kbs_hidden"></div>
				<?php do_action( 'kbs_single_ticket_form_top' ); ?>
	
				<fieldset id="kbs_single_ticket_form_fields">
					<legend><?php printf( __( 'Support %s Details', 'kb-support' ), $singular ); ?></legend>
					<div id="kbs_secure_site_wrapper">
						<span class="id"># <?php echo kbs_get_ticket_id( $ticket->ID ); ?></span>
                    </div>

                    <p><span class="data-label"><?php _e( 'Date', 'kb-support' ); ?>:</span> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->date ) ); ?></p>


                    <p><span class="data-label"><?php _e( 'Logged by', 'kb-support' ); ?>:</span> <?php echo $customer->name; ?></p>


                    <p><span class="data-label"><?php _e( 'Status', 'kb-support' ); ?>:</span> <?php echo $ticket->status_nicename; ?></p>

                    <p><span class="data-label"><?php _e( 'Agent', 'kb-support' ); ?>:</span> <?php echo get_userdata( $ticket->agent_id )->display_name; ?></p>

                    <p><span class="post-data-label"><?php _e( 'Subject', 'kb-support' ); ?>:</span> <?php esc_attr_e( $ticket->ticket_title ); ?></p>

					<p><span class="post-data-label"><?php _e( 'Content', 'kb-support' ); ?>:</span> 
                    	<span class="ticket-excerpt"><?php echo $ticket->get_the_excerpt(); ?></span>
                        <span class="ticket-content"><?php echo $ticket->get_content(); ?> <a class="ticket-toggle-content less"><?php _e( 'Show less', 'kb-support' ); ?> &hellip;</a></span>
                    </p>

				</fieldset>
	
				<?php do_action( 'kbs_single_ticket_form_bottom' ); ?>
			</form>
			<?php do_action( 'kbs_after_single_ticket_form' ); ?>
		</div>
	</div>
<?php else : ?>
	<?php echo kbs_display_notice( 'no_ticket' ); ?>
<?php endif; ?>
