<?php
/**
 * This template is used to display a customer's ticket history.
 *
 * @shortcode	[kbs_view_ticket]
 */
if ( is_user_logged_in() )	: ?>
	<?php global $current_user;
	
	$customer = new KBS_Customer( $current_user->ID, true );
	$tickets  = kbs_get_customer_tickets( $customer->id, array(), false, true ); ?>

	<?php if ( ! empty( $tickets ) ) : ?>
        <div id="kbs_item_wrapper" class="kbs_ticket_history_wrapper" style="float: left">
            <div class="ticket_info_wrapper data_section">
				<?php do_action( 'kbs_before_ticket_history_table', $tickets, $customer ); ?>
                <table id="ticket_history">
                    <thead>
                        <tr id="ticket_history_header">
                            <th><?php echo '#'; ?></th>
                            <th><?php _e( 'Opened', 'kb-support' ); ?></th>
                            <th><?php _e( 'Status', 'kb-support' ); ?></th>
                            <th><?php _e( 'Actions', 'kb-support' ); ?></th>
                        </tr>
                    </thead>

                    <?php foreach ( $tickets as $ticket ) : ?>

                        <?php $ticket_url = kbs_get_ticket_url( $ticket->ID ); ?>
                        <tr id="ticket_data_<?php echo $ticket->ID; ?>" class="ticket_data_row">
                            <td class="the_ticket_id"><a href="<?php echo esc_url( $ticket_url ); ?>"><?php echo kbs_get_ticket_number( $ticket->ID ); ?></a></td>
                            <td class="date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->post_date ) ); ?></td>
                            <td class="status"><?php echo kbs_get_ticket_status( $ticket, true ); ?></td>
                            <td class="actions"><a href="<?php echo esc_url( $ticket_url ); ?>"><?php _e( 'View', 'kb-support' ); ?></a></td>
                        </tr>

                    <?php endforeach; ?>

                </table>
                <?php do_action( 'kbs_after_ticket_history_table', $tickets, $customer ); ?>

            </div>
    
            <div id="kbs_ticket_history_pagination" class="kbs_pagination navigation">
                <?php
                $big = 999999;
                echo paginate_links( array(
                    'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                    'format'  => '?paged=%#%',
                    'current' => max( 1, get_query_var( 'paged' ) ),
                    'total'   => ceil( kbs_get_customer_ticket_count( $customer->id ) / 20 ) // 20 items per page
                ) );
                ?>
            </div>

        </div>

	<?php else : ?>
        <div class="kbs_alert kbs_alert_info"><?php printf( __( 'You have no %s yet.', 'kb-support' ), kbs_get_ticket_label_plural( true ) ); ?></div>
    <?php endif; ?>

<?php else : ?>

	<?php echo kbs_display_notice( 'ticket_login' ); ?>
	<?php echo kbs_login_form(); ?>

<?php endif; ?>
