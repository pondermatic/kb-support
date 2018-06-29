<?php	
/**
 * Manage kbs_ticket post metaboxes.
 * 
 * @since		0.1
 * @package		KBS
 * @subpackage	Functions/Metaboxes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Returns default KBS Ticket meta fields.
 *
 * @since	1.0
 * @return	array	$fields		Array of fields.
 */
function kbs_ticket_metabox_fields() {

	// Format is KBS_Ticket key => field name
	$fields = array(
		'customer_id' => 'kbs_customer_id',
		'agent_id'    => 'kbs_agent_id',
        'agents'      => 'kbs_assigned_agents'
	);

	return apply_filters( 'kbs_ticket_metabox_fields_save', $fields );

} // kbs_ticket_metabox_fields

/**
 * Remove unwanted metaboxes to for the kbs_ticket post type.
 *
 * @since	1.0
 * @return	void
 */
function kbs_ticket_remove_meta_boxes()	{
	$metaboxes = array(
        array( 'submitdiv', 'kbs_ticket', 'side' ),
        array( 'tagsdiv-department', 'kbs_ticket', 'side' ),
        array( 'comments', 'kbs_ticket', 'normal' ),
		array( 'commentsdiv', 'kbs_ticket', 'normal' )
    );

    $metaboxes = apply_filters( 'kbs_ticket_remove_metaboxes', $metaboxes );

	foreach( $metaboxes as $metabox )	{
		remove_meta_box( $metabox[0], $metabox[1], $metabox[2] );
	}
} // kbs_ticket_remove_meta_boxes
add_action( 'admin_head', 'kbs_ticket_remove_meta_boxes' );

/**
 * Define and add the metaboxes for the kbs_ticket post type.
 *
 * @since	1.0
 * @param	object	$post	The WP_Post object.
 * @return	void
 */
function kbs_ticket_add_meta_boxes( $post )	{

	if ( ! kbs_agent_can_access_ticket( $post->ID ) )	{
		wp_die(
			sprintf(
				__( 'You do not have access to this %s. <a href="%s">Go Back</a>', 'kb-support' ),
				kbs_get_ticket_label_singular( true ),
				admin_url( 'edit.php?post_type=kbs_ticket' )
			),
			__( 'Error', 'kb-support' ),
			array( 'response' => 403 )
		);
	}

	global $kbs_ticket, $kbs_ticket_update;

	$save              = __( 'Create', 'kb-support' );
	$kbs_ticket_update = false;
	$kbs_ticket        = new KBS_Ticket( $post->ID );
	$single_label      = kbs_get_ticket_label_singular();
    $ticket_number     = '';

	if ( 'draft' != $post->post_status && 'auto-draft' != $post->post_status )	{
		$save              = __( 'Update', 'kb-support' );
		$kbs_ticket_update = true;
        $ticket_number     = '# ' . kbs_format_ticket_number( $kbs_ticket->number );
		remove_post_type_support( $post->post_type, 'editor' );
	}

	add_meta_box(
		'kbs-ticket-metabox-save',
		sprintf( '%1$s %2$s %3$s', $save, $single_label, $ticket_number ),
		'kbs_ticket_metabox_save_callback',
		'kbs_ticket',
		'side',
		'high',
		array()
	);

    add_meta_box(
		'kbs-ticket-metabox-agents',
		__( 'Assignment', 'kb-support' ),
		'kbs_ticket_metabox_agents_callback',
		'kbs_ticket',
		'side',
		'high',
		array()
	);

	if ( $kbs_ticket_update )	{
		add_meta_box(
			'kbs-ticket-metabox-ticket-details',
			sprintf( __( 'Original %1$s', 'kb-support' ), $single_label ),
			'kbs_ticket_metabox_data_callback',
			'kbs_ticket',
			'normal',
			'high',
			array()
		);

		if ( 'new' != $kbs_ticket->status )	{

			add_meta_box(
				'kbs-ticket-metabox-ticket-reply',
				sprintf( __( 'Reply to %1$s', 'kb-support' ), $single_label ),
				'kbs_ticket_metabox_reply_callback',
				'kbs_ticket',
				'normal',
				'high',
				array()
			);

		}

		add_meta_box(
			'kbs-ticket-metabox-ticket-private',
			__( 'Private Notes', 'kb-support' ),
			'kbs_ticket_metabox_notes_callback',
			'kbs_ticket',
			'normal',
			'high',
			array()
		);
	}
	
} // kbs_ticket_add_meta_boxes
add_action( 'add_meta_boxes_kbs_ticket', 'kbs_ticket_add_meta_boxes' );

/**
 * Returns an array of available ticket actions.
 *
 * @since   1.2.4
 * @param	object   $kbs_ticket  KBS_Ticket class object
 * @param	bool     $updating    True if this ticket is being updated, false if new.
 * @return	array    Array of actions
 */
function kbs_get_ticket_actions( $kbs_ticket, $updating = true )   {

    $actions = array();

    if ( $updating )   {
        if ( ! empty( $kbs_ticket->form_data ) )    {
            $actions['form_data'] = '<a href="#" class="toggle-view-submission-option-section">' . __( 'View submission data', 'kb-support' ) . '</a>';
        }

        if ( ! empty( $kbs_ticket->files ) )    {
            $actions['files'] = '<a href="#" class="toggle-view-files-option-section">' . __( 'Hide attachments', 'kb-support' ) . '</a>';
        }
    }

    if ( $updating && current_user_can( 'manage_ticket_settings' ) ) {

        if ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
            $delete_url  = get_delete_post_link( $kbs_ticket->ID );
			$delete_term = _x( 'Trash', 'verb', 'kb-support' );
            $delete_ays  = '';
		} else {
            $delete_url  = get_delete_post_link( $kbs_ticket->ID, null, true );
            $delete_term = sprintf( __( 'Delete %s', 'kb-support' ), kbs_get_ticket_label_singular( true ) );
			$delete_ays  = ! MEDIA_TRASH ? " onclick='return KBSShowNotice.warn();'" : '';
		}

        $actions['trash'] = sprintf(
            '<a href="%s" class="kbs-delete"%s>%s</a>',
            $delete_url,
            $delete_ays,
            $delete_term
        );

    }

    $actions = apply_filters( 'kbs_ticket_actions', $actions, $kbs_ticket );

    return $actions;
} // kbs_get_ticket_actions

/**
 * The callback function for the save metabox.
 *
 * @since	1.0
 * @global	object	$post				WP_Post object
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 */
function kbs_ticket_metabox_save_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	wp_nonce_field( 'kbs_ticket_meta_save', 'kbs_ticket_meta_box_nonce' );

	/*
	 * Output the items for the save metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_status_fields', $post->ID );
} // kbs_ticket_metabox_save_callback

/**
 * The callback function for the agent assignment metabox.
 *
 * @since	1.0
 * @global	object	$post				WP_Post object
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 */
function kbs_ticket_metabox_agents_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the save metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_agent_fields', $post->ID );
} // kbs_ticket_metabox_agents_callback

/**
 * The callback function for the original ticket details metabox.
 *
 * @since	1.0
 * @global	object	$post				WP_Post object
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 */
function kbs_ticket_metabox_data_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the details metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_data_fields', $post->ID );
} // kbs_ticket_metabox_data_callback

/**
 * The callback function for the ticket reply metabox.
 *
 * @since	1.0
 * @global	object	$post				WP_Post object
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 */
function kbs_ticket_metabox_reply_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the ticket reply metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_reply_fields', $post->ID );
} // kbs_ticket_metabox_reply_callback

/**
 * The callback function for the private notes metabox.
 *
 * @since	1.0
 * @global	object	$post				WP_Post object
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new
 */
function kbs_ticket_metabox_notes_callback()	{
	global $post, $kbs_ticket, $kbs_ticket_update;

	/*
	 * Output the items for the pruvate notes metabox
	 * @since	1.0
	 * @param	int	$post_id	The Ticket post ID
	 */
	do_action( 'kbs_ticket_notes_fields', $post->ID );
} // kbs_ticket_metabox_notes_callback

/**
 * Display the save ticket metabox row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_save_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update; ?>

	<div class="submitbox" id="submitpost">
		<div id="minor-publishing">
        	<div id="minor-publishing-actions">
                <div class="clear"></div>
            </div><!-- #minor-publishing-actions -->
            <div id="kbs-ticket-actions">

                <?php do_action( 'kbs_ticket_metabox_save_top', $ticket_id ); ?>

				<?php if ( ! $kbs_ticket_update && ! kbs_get_option( 'ticket_received_disable_email', false ) ) : ?>

                    <?php echo KBS()->html->hidden( array(
                        'id'           => 'kbs-pending-ticket-created-email',
                        'name'         => '_kbs_pending_ticket_created_email',
                        'value'        => '1'
                    ) ); ?>

				<?php endif; ?>

                <?php do_action( 'kbs_ticket_metabox_save_before_status', $ticket_id ); ?>

				<?php echo KBS()->html->ticket_status_dropdown( array(
					'name'     => 'ticket_status',
					'selected' => $kbs_ticket->post_status,
					'chosen'   => true
				) ); ?>

                <?php do_action( 'kbs_ticket_metabox_save_after_status', $ticket_id ); ?>

                <div id="kbs-customer-select">
					<p><?php echo KBS()->html->customer_dropdown( array(
						'name'     => 'kbs_customer_id',
						'selected' => $kbs_ticket->customer_id,
						'chosen'   => true
					) ); ?></p>
                </div>

                <?php do_action( 'kbs_ticket_metabox_save_after_customer', $ticket_id ); ?>

                <p><a href="<?php echo wp_get_referer(); ?>"><?php printf( __( 'Back to %s', 'kb-support' ), kbs_get_ticket_label_plural() ); ?></a>

					<?php submit_button( 
                        sprintf( '%s %s',
                            empty( $kbs_ticket_update ) ? __( 'Add', 'kb-support' ) : __( 'Update', 'kb-support' ),
                            kbs_get_ticket_label_singular()
                        ),
                        'primary',
                        'save',
                        false,
                        array( 'id' => 'save-post' )
                    ); ?>
                </p>

                <?php do_action( 'kbs_ticket_metabox_save_bottom', $ticket_id ); ?>

            </div><!-- #kbs-ticket-actions -->
        </div><!-- #minor-publishing -->
    </div><!-- #submitpost -->
    <?php

} // kbs_ticket_metabox_save_row
add_action( 'kbs_ticket_status_fields', 'kbs_ticket_metabox_save_row', 10 );

/**
 * Output the SLA data for the ticket.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_sla_row( $ticket_id )	{
	
	global $kbs_ticket, $kbs_ticket_update;
	
	if ( ! kbs_track_sla() || ! $kbs_ticket_update )	{
		return;
	}
	
	$sla_respond_class  = '';
	$sla_resolve_class  = '';
	$sla_respond_remain = '';
	$sla_resolve_remain = '';
	
	if ( $kbs_ticket_update )	{
		$respond            = $kbs_ticket->sla_respond;
		$resolve            = $kbs_ticket->sla_resolve;
		$sla_respond_class  = kbs_sla_has_passed( $kbs_ticket->ID ) ? '_over' : '';
		$sla_resolve_class  = kbs_sla_has_passed( $kbs_ticket->ID, 'resolve' ) ? '_over' : '';
		$sla_respond_remain = ' ' . $kbs_ticket->get_sla_remain();
		$sla_resolve_remain = ' ' . $kbs_ticket->get_sla_remain( 'resolve' );
	} else	{
		$respond = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( kbs_calculate_sla_target_response() ) );
		$resolve = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( kbs_calculate_sla_target_resolution() ) );
	}

	if ( ! empty( $ticket->sla_respond ) || ! empty( $kbs_ticket->sla_resolve ) ) : ?>
        <p><strong><?php _e( 'SLA Status', 'kb-support' ); ?></strong></p>
        <p><?php echo kbs_display_sla_response_status_icon( $kbs_ticket ); ?></p>
            
        <p><?php echo kbs_display_sla_resolve_status_icon( $kbs_ticket ); ?></p>
    
    <?php endif;

} // kbs_ticket_metabox_sla_row
add_action( 'kbs_ticket_metabox_save_after_customer', 'kbs_ticket_metabox_sla_row', 10 );

/**
 * Display the agent ticket metabox row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_agent_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

    ?>
    <div id="kbs-agent-options">
        <p>
            <?php echo KBS()->html->agent_dropdown( array(
                'name'     => 'kbs_agent_id',
                'selected' => ( ! empty( $kbs_ticket->agent_id ) ? $kbs_ticket->agent_id : get_current_user_id() ),
                'chosen'   => true
            ) ); ?>
        </p>
    </div>
    <?php

} // kbs_ticket_metabox_agent_row
add_action( 'kbs_ticket_agent_fields', 'kbs_ticket_metabox_agent_row', 10 );

/**
 * Display the additional agents ticket metabox row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_additional_agents_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

    if ( ! kbs_multiple_agents() )  {
        return;
    }

    ?>
    <div id="kbs-multi-agent-options">
        <p><label for="kbs_assigned_agents"><?php _e( 'Additional Agents', 'kb-support' ); ?>:</label>
            <?php echo KBS()->html->agent_dropdown( array(
                'name'            => 'kbs_assigned_agents',
                'selected'        => $kbs_ticket->agents,
                'chosen'          => true,
                'multiple'        => true,
                'show_option_all' => false,
                'placeholder'     => __( 'Select Additional Agents', 'kb-support' ),
                'exclude'         => array( $kbs_ticket->agent_id )
            ) ); ?>
        </p>

        <?php do_action( 'kbs_ticket_metabox_after_agent', $ticket_id ); ?>
    </div>
    <?php

} // kbs_ticket_metabox_additional_agents_row
add_action( 'kbs_ticket_agent_fields', 'kbs_ticket_metabox_additional_agents_row', 50 );

/**
 * Display the ticket content sections metaboxes.
 *
 * @since	1.2.4
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_sections()  {
    global $kbs_ticket, $kbs_ticket_update;

    $date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

    ?>
    <div id="kbs-ticket-metabox-fields" class="kbs_meta_table_wrap">

        <div class="widefat kbs_repeatable_table">

            <div class="kbs-ticket-option-fields kbs-repeatables-wrap">

                <div class="kbs_ticket_content_wrapper">

                    <div class="kbs-ticket-content-row-header">
                        <span class="kbs-ticket-content-row-title">
                            <?php printf(
                                __( 'Received: %s', 'kb-support' ),
                                date_i18n( $date_format, strtotime( $kbs_ticket->date ) )
                            ); ?>

                            <?php if ( $kbs_ticket->date != $kbs_ticket->modified_date ) : ?>
                                <br>
                                <?php printf(
                                    __( 'Updated: %s', 'kb-support' ),
                                    date_i18n( $date_format, strtotime( $kbs_ticket->modified_date ) )
                                ); ?>
                            <?php endif; ?>
                        </span>

                        <?php
                        $actions = kbs_get_ticket_actions( $kbs_ticket, $kbs_ticket_update );
                        ?>

                        <span class="kbs-ticket-content-row-actions">
                            <?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
                        </span>
                    </div>

                    <div class="kbs-ticket-content-row-standard-fields">
                        <?php do_action( 'kbs_ticket_metabox_standard_fields', $kbs_ticket, $kbs_ticket_update ); ?>
                    </div>
                    <?php do_action( 'kbs_ticket_metabox_custom_sections', $kbs_ticket, $kbs_ticket_update ); ?>
                </div>

            </div>

        </div>
    
    </div>
    <?php
} // kbs_ticket_metabox_sections
add_action( 'kbs_ticket_data_fields', 'kbs_ticket_metabox_sections', 10 );

/**
 * Display the ticket customer section.
 *
 * @since	1.2.4
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_customer_section( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	$user_info   = $kbs_ticket->user_info;
    $customer_id = $kbs_ticket->customer_id;

    if ( '-1' == $customer_id || empty( $customer_id ) )    {
        $customer = __( 'No customer assigned', 'kb-support' );
    } else  {
        $customer = sprintf(
            '%s %s (<a href="%s">#%s</a>)',
            $kbs_ticket->first_name,
            $kbs_ticket->last_name,
            add_query_arg( array(
                'post_type' => 'kbs_ticket',
                'page'      => 'kbs-customers',
                'view'      => 'userdata',
                'id'        => $kbs_ticket->customer_id
            ), admin_url( 'edit.php' ) ),
            $kbs_ticket->customer_id
        );
    }

    ?>
	<div class="kbs-customer-ticket-overview">
		<span class="kbs-ticket-avatar">
			<?php echo get_avatar( $kbs_ticket->email, '', kbs_get_company_logo( $kbs_ticket->company_id ) ); ?>
		</span>
		<span class="kbs-customer-ticket-contact">
			<span class="kbs-customer-ticket-attr customer-name">
				<?php echo $customer; ?>
			</span>

			<?php if ( ! empty( $kbs_ticket->company_id ) ) : ?>
				<span class="kbs-customer-ticket-attr customer-company">
					<?php echo kbs_get_company_name( $kbs_ticket->company_id ); ?>
				</span>
			<?php endif; ?>

			<span class="kbs-customer-ticket-attr customer-email">
				<?php printf( '<a href="mailto:%1$s">%1$s</a>', $kbs_ticket->email ); ?>
			</span>

			<?php if ( ! empty( $user_info['website'] ) ) : ?>
				<span class="kbs-customer-ticket-attr customer-website">
					<?php printf( '<a href="%1$s">%1$s</a>', esc_url( $user_info['website'] ) ); ?>
				</span>
			<?php endif; ?>

		</span>
	</div>
	<hr>
    <?php
		
} // kbs_ticket_metabox_customer_section
add_action( 'kbs_ticket_metabox_standard_fields', 'kbs_ticket_metabox_customer_section', 10 );

/**
 * Display the original ticket content section.
 *
 * @since	1.0
 * @global	obj		$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 * @return	str
 */
function kbs_ticket_metabox_content_section( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

    ?>
    <div class="kbs-ticket-content">
		<h3>
			<?php printf( __( '%s Content', 'kb-support' ), kbs_get_ticket_label_singular() ); ?>
		</h3>
        <p><?php echo $kbs_ticket->get_content(); ?></p>
    </div>
    <?php
		
} // kbs_ticket_metabox_content_section
add_action( 'kbs_ticket_metabox_standard_fields', 'kbs_ticket_metabox_content_section', 20 );

/**
 * Display the ticket form submission details row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_form_data_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update; ?>

    <?php if ( empty( $kbs_ticket->form_data ) ) return; ?>

    <div id="kbs-ticket-formdata-fields" class="kbs-custom-ticket-sections-wrap">
        <div class="kbs-custom-ticket-sections">
            <div class="kbs-custom-ticket-section">
                <span class="kbs-custom-ticket-section-title"><?php echo $kbs_ticket->get_form_name(); ?></span>

                <span class="kbs-ticket-form-content">
                    <?php echo $kbs_ticket->show_form_data(); ?>
                </span>
            </div>
        </div>
    </div>
    <?php
		
} // kbs_ticket_metabox_form_data_row
add_action( 'kbs_ticket_metabox_custom_sections', 'kbs_ticket_metabox_form_data_row', 20 );

/**
 * Display the ticket files row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_files_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	if ( ! kbs_get_option( 'file_uploads' ) )	{
		return;
	}

	?>

	<?php if ( $kbs_ticket_update && ! empty( $kbs_ticket->files ) ) : ?>
		
		<div id="kbs-original-ticket-files-wrap" class="kbs_ticket_files_wrap">
        	<p><strong><?php _e( 'Attached Files', 'kb-support' ); ?></strong></p>
			<?php foreach( $kbs_ticket->files as $file ) : ?>
                <p><a class="kbs_ticket_form_data" href="<?php echo wp_get_attachment_url( $file->ID ); ?>" target="_blank"><?php echo basename( get_attached_file( $file->ID ) ); ?></a></p>
            <?php endforeach; ?>
		</div>
	
	<?php endif;
		
} // kbs_ticket_metabox_details_row
add_action( 'kbs_ticket_data_fields', 'kbs_ticket_metabox_files_row', 30 );

/**
 * Display the ticket replies row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_existing_replies_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	?>
    <div id="kbs-replies-loader"></div>

    <div id="kbs_historic_reply_fields" class="kbs_meta_table_wrap">
        <div class="widefat">
            <div class="kbs-historic-reply-option-fields"></div>
        </div>
    </div>
	<?php

} // kbs_ticket_metabox_replies_row
add_action( 'kbs_ticket_reply_fields', 'kbs_ticket_metabox_existing_replies_row', 10 );

/**
 * Display the ticket reply row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_reply_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	if ( 'closed' == $kbs_ticket->post_status ) : ?>
		<p>
			<?php printf( __( 'This %1$s is currently closed. <a href="%2$s">Re-open %3$s.</a>', 'kb-support' ),
				kbs_get_ticket_label_singular( true ),
				wp_nonce_url( add_query_arg( 'kbs-action', 're-open-ticket', get_edit_post_link( $ticket_id ) ), 'kbs-reopen-ticket', 'kbs-ticket-nonce' ),
				kbs_get_ticket_label_singular()
			); ?>
		</p>
	<?php else :

		$settings = apply_filters( 'kbs_ticket_reply_mce_settings', array(
			'textarea_rows'    => 10,
			'quicktags'        => true
		) ); ?>

		<div id="kbs-ticket-reply-wrap">
        	<p><label for="kbs_ticket_reply"><strong><?php _e( 'Add a New Reply', 'kb-support' ); ?></strong></label><br />
            	<?php do_action( 'kbs_ticket_metabox_before_reply_content', $ticket_id );
				wp_editor( '', 'kbs_ticket_reply', $settings ); ?>
            </p>
        </div>

		<?php
		/*
		 * Fires immediately before the reply buttons are output
		 * @since	1.0
		 * @param	int	$post_id	The Ticket post ID
		 */
		do_action( 'kbs_ticket_before_reply_buttons', $ticket_id );

		?>
        <div id="kbs-ticket-reply-container">
            <div class="kbs-reply"><a id="kbs-reply-update" class="button button-primary"><?php _e( 'Reply', 'kb-support' ); ?></a></div>
            <div class="kbs-reply"><a id="kbs-reply-close" class="button button-secondary"><?php _e( 'Reply and Close', 'kb-support' ); ?></a></div>
        </div>
        <div id="kbs-new-reply-loader"></div>

	<?php endif;
		
} // kbs_ticket_metabox_details_row
add_action( 'kbs_ticket_reply_fields', 'kbs_ticket_metabox_reply_row', 20 );

/**
 * Display the ticket add note row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_notes_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update;

	global $kbs_ticket, $kbs_ticket_update;

	?>
    <div id="kbs-notes-loader"></div>

    <div id="kbs_notes_fields" class="kbs_meta_table_wrap">
        <div class="widefat">
            <div class="kbs-notes-option-fields"></div>
        </div>
    </div>
	<?php

} // kbs_ticket_metabox_notes_row
add_action( 'kbs_ticket_notes_fields', 'kbs_ticket_metabox_notes_row', 10 );

/**
 * Display the ticket add note row.
 *
 * @since	1.0
 * @global	object	$kbs_ticket			KBS_Ticket class object
 * @global	bool	$kbs_ticket_update	True if this ticket is being updated, false if new.
 * @param	int		$ticket_id			The ticket post ID.
 */
function kbs_ticket_metabox_add_note_row( $ticket_id )	{

	global $kbs_ticket, $kbs_ticket_update; ?>

	<div id="kbs-ticket-add-note-container">
    	<p><label for="kbs_new_note"><strong><?php _e( 'Add a New Note', 'kb-support' ); ?></strong></label><br />
			<?php echo KBS()->html->textarea( array(
                'name'  => 'kbs_new_note',
				'id'    => 'kbs_new_note',
                'desc'  => __( 'Notes are only visible to support workers', 'kb-support' ),
                'class' => 'large-text',
                'rows'  => 5
            ) ); ?>
        </p>
        <?php
        /*
         * Fires immediately before the add note button is output.
         * @since	1.0
         * @param	int	$post_id	The Ticket post ID
         */
        do_action( 'kbs_ticket_before_add_note_button', $ticket_id ); ?>

		<div class="kbs-add-note"><a id="kbs-add-note" class="button button-secondary"><?php _e( 'Add Note', 'kb-support' ); ?></a></div>
        <div id="kbs-new-note-loader"></div>
	</div>
	<?php
		
} // kbs_ticket_metabox_add_note_row
add_action( 'kbs_ticket_notes_fields', 'kbs_ticket_metabox_add_note_row', 20 );
