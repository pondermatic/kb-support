<?php
/**
 * Company Page
 *
 * @package     KBS
 * @subpackage  Companies/Functions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Customers Page
 *
 * Renders the companies page contents.
 *
 * @since	1.0
 * @return	void
*/
function kbs_companies_page() {
	$default_views  = kbs_company_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'companies';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		if ( 'add' == $requested_view )	{
			kbs_render_add_company_view();
		} else	{
			kbs_render_company_view( $requested_view, $default_views );
		}
	} else {
		kbs_companies_list();
	}
} // kbs_companies_page

/**
 * Register the views for company management
 *
 * @since	1.0
 * @return	arr		Array of views and their callbacks
 */
function kbs_company_views() {
	$views = array();

	return apply_filters( 'kbs_company_views', $views );
} // kbs_company_views

/**
 * Register the tabs for company management.
 *
 * @since	1.0
 * @return	arr		Array of tabs for the company
 */
function kbs_company_tabs() {
	$tabs = array();

	return apply_filters( 'kbs_company_tabs', $tabs );
} // kbs_company_tabs

/**
 * List table of company
 *
 * @since	1.0
 * @return	void
 */
function kbs_companies_list() {
	include( dirname( __FILE__ ) . '/class-kbs-company-table.php' );

	$add_company_url = admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies&view=add' );

	$companies_table = new KBS_Company_Table();
	$companies_table->prepare_items();
	?>
	<div class="wrap">
		<h1>
			<?php _e( 'Companies', 'kb-support' ); ?>
            <a href="<?php echo esc_url( $add_company_url ); ?>" class="page-title-action"><?php _e( 'Add Company', 'kb-support' ); ?></a>
        </h1>
		<?php do_action( 'kbs_company_table_top' ); ?>
		<form id="kbs-companies-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies' ); ?>">
			<?php
			$companies_table->search_box( __( 'Search Companies', 'kb-support' ), 'kbs-companies' );
			$companies_table->display();
			?>
			<input type="hidden" name="post_type" value="kbs_ticket" />
			<input type="hidden" name="page" value="kbs-companies" />
			<input type="hidden" name="view" value="companies" />
		</form>
		<?php do_action( 'kbs_companies_table_bottom' ); ?>
	</div>
	<?php
} // kbs_companies_list

/**
 * Renders the company view wrapper
 *
 * @since	1.0
 * @param	str		$view		The View being requested
 * @param	arr		$callbacks	The Registered views and their callback functions
 * @return	void
 */
function kbs_render_company_view( $view, $callbacks ) {

	$render            = isset( $_GET['render'] ) ? $_GET['render'] : true;
	$company_view_role = apply_filters( 'kbs_view_companies_role', 'view_ticket_settings' );
	$url               = remove_query_arg( array( 'kbs-message', 'render' ) );
	$company_tabs      = kbs_company_tabs();
	$active_tab        = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $company_tabs ) ? $_GET['tab'] : 'general';

	if ( ! current_user_can( $company_view_role ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'company_list_permission',
			'render'      => 0
		), $url ) );
		die();
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'invalid_company_id',
			'render'      => 0
		), $url ) );
		die();
	}

	$company_id = (int)$_GET['id'];
	$company    = new KBS_Company( $company_id );

	if ( empty( $company->id ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'invalid_company_id',
			'render'      => 0
		), $url ) );
		die();
	}

	?>

	<div class='wrap'>
        <h1 class="nav-tab-wrapper">
            <?php
            foreach( $company_tabs as $key => $tab ) {
    
                $tab_url = add_query_arg( array(
                    'view' => $key,
                ) , $url );
    
                $active = $view == $key ? ' nav-tab-active' : '';
    
                echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab['title'] ) . '" class="nav-tab' . $active . '">';
                    echo '<span class="dashicons ' . sanitize_html_class( $tab['dashicon'] ) . '" aria-hidden="true"></span>' . esc_html( $tab['title'] );
                echo '</a>';
            }
            ?>
        </h1>

		<?php if ( $company && $render ) : ?>
            <div id="kbs-item-wrapper" class="kbs-company-wrapper" style="float: left">
                <?php $callbacks[ $view ]( $company ) ?>
            </div>
        <?php endif; ?>

	</div><!-- .wrap -->
	
	<?php

} // kbs_render_company_view

/**
 * View a company
 *
 * @since	1.0
 * @param	$company	The Company object being displayed
 * @return	void
 */
function kbs_companies_view( $company ) {

	$company_edit_role = apply_filters( 'kbs_edit_companies_role', 'manage_ticket_settings' );

	$tickets      = kbs_get_company_tickets( $company->id, array(), false );
	$ticket_count = count( $tickets );

	?>

	<?php do_action( 'kbs_company_card_top', $company ); ?>

	<div class="info-wrapper company-section">

		<form id="edit-company-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies&view=companydata&id=' . $company->id ); ?>">

			<div class="kbs-item-info company-info">

				<div class="logo-wrap left" id="company-logo">
					<?php echo get_avatar( $company->email, 96, '', $company->name ); ?><br />
					<?php if ( current_user_can( $company_edit_role ) ): ?>
						<span class="info-item editable company-edit-link"><a href="#" id="edit-company"><?php _e( 'Edit Company', 'kb-support' ); ?></a></span>
					<?php endif; ?>
				</div>

				<div class="company-id right">
					#<?php echo $company->id; ?>
				</div>

				<div class="company-address-wrapper right">
					<?php
						$address = $company->get_meta( 'address', true );
						$defaults = array(
							'line1'   => '',
							'line2'   => '',
							'city'    => '',
							'state'   => '',
							'country' => '',
							'zip'     => ''
						);

						$address     = wp_parse_args( $address, $defaults );
						$has_address = false;

						foreach ( $address as $address_field )	{
                        	if ( ! empty( $address_field ) )	{
								$has_address = true;
							}
						}

					?>

					<?php if ( ! empty( $has_address ) ) : ?>
                        <strong><?php _e( 'Company Address', 'kb-support' ); ?></strong>
                        <span class="company-address info-item editable">
                            <span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
                            <span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
                            <span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
                            <span class="info-item" data-key="state"><?php echo $address['state']; ?></span>
                            <span class="info-item" data-key="country"><?php echo $address['country']; ?></span>
                            <span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
                        </span>
                    <?php else : ?>
                    	<span class="company-address info-item editable">
							<?php _e( 'No Company Address Recorded', 'kb-support' ); ?>
                        </span>
					<?php endif; ?>

					<span class="company-address info-item edit-item">
						<input class="info-item" type="text" data-key="line1" name="companyinfo[line1]" placeholder="<?php _e( 'Address 1', 'kb-support' ); ?>" value="<?php echo $address['line1']; ?>" />
						<input class="info-item" type="text" data-key="line2" name="companyinfo[line2]" placeholder="<?php _e( 'Address 2', 'kb-support' ); ?>" value="<?php echo $address['line2']; ?>" />
						<input class="info-item" type="text" data-key="city" name="companyinfo[city]" placeholder="<?php _e( 'City', 'kb-support' ); ?>" value="<?php echo $address['city']; ?>" />
                        <input type="text" data-key="state" name="companyinfo[state]" id="card_state" class="card_state kbs-input info-item" placeholder="<?php _e( 'County / State / Province', 'kb-support' ); ?>"/>
						<select data-key="country" name="companyinfo[country]" id="billing_country" class="billing_country kbs-select edit-item">
							<?php

							$selected_country = $address['country'];

							$countries = kbs_get_country_list();
							foreach( $countries as $country_code => $country ) {
								echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
							}
							?>
						</select>

						<input class="info-item" type="text" data-key="zip" name="companyinfo[zip]" placeholder="<?php _e( 'Postal / Zip', 'kb-support' ); ?>" value="<?php echo $address['zip']; ?>" />
					</span>

				</div>

				<div class="company-main-wrapper left">

					<span class="company-name info-item edit-item"><input size="15" data-key="name" name="companyinfo[name]" type="text" value="<?php echo esc_attr( $company->name ); ?>" placeholder="<?php _e( 'Company Name', 'kb-support' ); ?>" /></span>

					<span class="company-name info-item editable"><span data-key="name"><?php echo $company->name; ?></span></span>

					<span class="company-contact info-item edit-item"><input size="15" data-key="name" name="companyinfo[contact]" type="text" value="<?php echo esc_attr( $company->name ); ?>" placeholder="<?php _e( 'Company Contact Name', 'kb-support' ); ?>" /></span>

					<span class="company-contact info-item editable"><span data-key="name"><?php echo $company->contact; ?></span></span>

					<span class="company-name info-item edit-item"><input size="20" data-key="email" name="companyinfo[email]" type="text" value="<?php echo $company->email; ?>" placeholder="<?php _e( 'Company Contact Email', 'kb-support' ); ?>" /></span>

					<span class="company-email info-item editable" data-key="email"><?php echo $company->email; ?></span>

					<span class="company-primary-phone info-item edit-item"><input size="20" data-key="primary-phone" name="companyinfo[primary_phone]" type="text" value="<?php echo $company->primary_phone; ?>" placeholder="<?php _e( 'Company Primary Phone', 'kb-support' ); ?>" /></span>

					<span class="company-primary-phone info-item editable" data-key="primary_phone"><?php echo $company->primary_phone; ?></span>

					<span class="company-website info-item edit-item"><input size="20" data-key="web-address" name="companyinfo[website]" type="text" value="<?php echo $company->website; ?>" placeholder="<?php _e( 'http://', 'kb-support' ); ?>" /></span>

					<span class="company-website info-item editable" data-key="website"><?php echo $company->website; ?></span>

					<span class="company-since info-item">
						<?php _e( 'Customer since', 'kb-support' ); ?>
						<?php echo date_i18n( get_option( 'date_format' ), strtotime( $company->date_created ) ) ?>
					</span>

					<?php do_action( 'kbs_company_before_ticket_count', $company ); ?>

                    <a href="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&company=' . $company->id ); ?>">
                        <?php printf( _n( '%d ' . kbs_get_ticket_label_singular(), '%d ' . kbs_get_ticket_label_plural(), $ticket_count, 'kb-support' ), $ticket_count ); ?>
                    </a>

				</div>

			</div>

			<span id="company-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="companyinfo[id]" value="<?php echo $company->id; ?>" />
				<?php wp_nonce_field( 'edit-company', '_wpnonce', false, true ); ?>
				<input type="hidden" name="kbs-action" value="edit-company" />
				<input type="submit" id="kbs-edit-company-save" class="button-secondary" value="<?php _e( 'Update Company', 'kb-support' ); ?>" />
				<a id="kbs-edit-company-cancel" href="" class="delete"><?php _e( 'Cancel', 'kb-support' ); ?></a>
			</span>

		</form>
	</div>

	<hr />

	<?php do_action( 'kbs_company_before_tables_wrapper', $company ); ?>

	<div id="kbs-item-tables-wrapper" class="company-tables-wrapper company-section">

		<?php do_action( 'kbs_company_before_tables', $company ); ?>

        <h3><?php printf( __( 'Recent %s', 'kb-support' ), kbs_get_ticket_label_plural() ); ?></h3>
		
		<table class="wp-list-table widefat striped tickets">
			<thead>
				<tr>
					<th><?php _e( '#', 'kb-support' ); ?></th>
					<th><?php _e( 'Opened', 'kb-support' ); ?></th>
                    <th><?php _e( 'Title', 'kb-support' ); ?></th>
					<th><?php _e( 'Status', 'kb-support' ); ?></th>
                    <?php do_action( 'kbs_company_tickets_table_headers', $company ); ?>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $tickets ) ) : ?>
					<?php foreach ( $tickets as $ticket ) : ?>
						<tr>
							<td><a href="<?php echo admin_url( 'post.php?post=' . $ticket->ID . '&action=edit' ); ?>">
									<?php echo kbs_get_ticket_id( $ticket->ID ); ?>
								</a>
                            </td>
							<td class="date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $ticket->post_date ) ); ?></td>
                            <td class="title"><?php echo get_the_title( $ticket->ID ); ?></td>
							<td><?php echo kbs_get_ticket_status( $ticket, true ); ?></td>
                            <?php do_action( 'kbs_after_company_tickets_table_status', $company ); ?>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5"><?php printf( __( 'No %s Found', 'kb-support' ), kbs_get_ticket_label_plural() ); ?></td></tr>
				<?php endif; ?>
			</tbody>
            <tfoot>
				<tr>
					<th><?php _e( '#', 'kb-support' ); ?></th>
					<th><?php _e( 'Opened', 'kb-support' ); ?></th>
                    <th><?php _e( 'Title', 'kb-support' ); ?></th>
					<th><?php _e( 'Status', 'kb-support' ); ?></th>
                    <?php do_action( 'kbs_company_tickets_table_headers', $company ); ?>
				</tr>
			</tfoot>
		</table>

		<?php do_action( 'kbs_company_after_tickets_table', $company ); ?>

    </div>

	<?php
}

/**
 * View the notes of a company.
 *
 * @since	1.0
 * @param	$company	The Company being displayed
 * @return	void
 */
function kbs_company_notes_view( $company ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $company->get_notes_count();
	$per_page    = apply_filters( 'kbs_company_notes_per_page', 20 );
	$total_pages = ceil( $note_count / $per_page );

	$company_notes = $company->get_notes( $per_page, $paged );
	?>

	<div id="kbs-item-notes-wrapper">
		<div class="kbs-item-notes-header">
			<?php echo get_avatar( 999999999, 30, $company->logo ); ?> <span><?php echo $company->name; ?></span>
		</div>
		<h3><?php _e( 'Notes', 'kb-support' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
            <div style="display: block; margin-bottom: 35px;">
                <form id="kbs-add-company-note" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies&view=notes&id=' . $company->id ); ?>">
                    <textarea id="company-note" name="company_note" class="company-note-input" rows="10"></textarea>
                    <br />
                    <input type="hidden" id="company-id" name="company_id" value="<?php echo $company->id; ?>" />
                    <input type="hidden" name="kbs-action" value="add-company-note" />
                    <?php wp_nonce_field( 'add-company-note', 'add_company_note_nonce', true, true ); ?>
                    <input id="add-company-note" class="right button-primary" type="submit" value="Add Note" />
                </form>
            </div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="kbs-company-notes">
		<?php if ( count( $company_notes ) > 0 ) : ?>
			<?php foreach( $company_notes as $key => $note ) : ?>
				<div class="company-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="kbs-no-company-notes">
				<?php _e( 'No Company Notes', 'kb-support' ); ?>
			</div>
		<?php endif; ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
} // kbs_company_notes_view

/**
 * View the delete company page.
 *
 * @since	1.0
 * @param	$company	The Company being displayed
 * @return	void
 */
function kbs_companies_delete_view( $company ) {
	$company_edit_role = apply_filters( 'kbs_edit_companies_role', 'manage_ticket_settings' );

	?>

	<?php do_action( 'kbs_company_delete_top', $company ); ?>

	<div class="info-wrapper company-section">

		<form id="delete-company" method="post" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-company&view=delete&id=' . $company->id ); ?>">

				<div class="kbs-item-notes-header">
				<?php echo get_avatar( 9999999, 30, $company->logo ); ?> <span><?php echo $company->name; ?></span>
			</div>


			<div class="company-info delete-company">

				<span class="delete-company-options">
					<p>
						<?php echo KBS()->html->checkbox( array( 'name' => 'kbs-company-delete-confirm' ) ); ?>
						<label for="kbs-company-delete-confirm"><?php _e( 'Are you sure you want to delete this company?', 'kb-support' ); ?></label>
					</p>

					<?php do_action( 'kbs_company_delete_inputs', $company ); ?>
				</span>

				<span id="company-edit-actions">
					<input type="hidden" name="company_id" value="<?php echo $company->id; ?>" />
					<?php wp_nonce_field( 'delete-company', '_wpnonce', false, true ); ?>
					<input type="hidden" name="kbs-action" value="delete-company" />
					<input type="submit" disabled="disabled" id="kbs-delete-company" class="button-primary" value="<?php _e( 'Delete Company', 'kb-support' ); ?>" />
					<a id="kbs-delete-company-cancel" href="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-companies&view=companydata&id=' . $company->id ); ?>" class="delete"><?php _e( 'Cancel', 'kb-support' ); ?></a>
				</span>

			</div>

		</form>
	</div>

	<?php

	do_action( 'kbs_company_delete_bottom', $company );
} // kbs_companies_delete_view

/**
 * Renders the form for adding a new company.
 *
 * @since	1.0
 * @return	voide
 */
function kbs_render_add_company_view()	{
	?>
    <div class="wrap">
		<h1>
			<?php _e( 'Add Company', 'kb-support' ); ?>
        </h1>

		<div id="kbs-item-wrapper" class="kbs-company-wrapper" style="float: left">
        	<div class="notice-wrap"></div>
			<div class="info-wrapper company-section">

                <form id="add-company-info" method="post" action="">

                    <div class="kbs-item-info company-info">

                        <div class="company-main-wrapper left">

                            <span class="company-name info-item"><input size="15" id="company-name" name="company_name" type="text" value="" placeholder="<?php _e( 'Company Name', 'kb-support' ); ?>" /></span>

                            <span class="company-email info-item"><input size="20" id="company-email" name="company_email" type="text" value="" placeholder="<?php _e( 'Company Email', 'kb-support' ); ?>" /></span>

                        </div>
        
                    </div>
        
                    <span id="company-add-actions">
                        <?php wp_nonce_field( 'add-company', 'add_company_nonce', false, true ); ?>
                        <input type="hidden" name="kbs-action" value="add-company" />
                        <button class="button-primary kbs-add-company-save" id="kbs-add-company-save"><?php _e( 'Add Company', 'kb-support' ); ?></button>
                        <span class="spinner"></span>
                    </span>
        
                </form>
            </div>
        </div>

		<?php do_action( 'kbs_add_company_form_bottom' ); ?>
	</div>
    <?php
} // kbs_render_add_company_view
