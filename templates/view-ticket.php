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
	<div id="kbs_ticket_wrap">
		<?php do_action( 'kbs_notices' ); ?>
		<div id="kbs_ticket_form_wrap" class="kbs_clearfix">
			<?php do_action( 'kbs_before_single_ticket_form' ); ?>
	
			<form<?php kbs_maybe_set_enctype(); ?> id="kbs_single_ticket_form" class="kbs_form" action="" method="post">
				<div class="kbs_alert kbs_alert_error kbs_hidden"></div>
				<?php do_action( 'kbs_single_ticket_form_top' ); ?>
	
				<fieldset id="kbs_single_ticket_form_fields">
					<legend><?php printf( __( 'Support %s %s', 'kb-support' ), $singular, kbs_get_ticket_id( $ticket->ID ) ); ?></legend>
					
				</fieldset>
	
				<?php do_action( 'kbs_single_ticket_form_bottom' ); ?>
			</form>
			<?php do_action( 'kbs_after_single_ticket_form' ); ?>
		</div>
	</div>
<?php else : ?>
	<?php echo kbs_display_notice( 'no_ticket' ); ?>
<?php endif; ?>
