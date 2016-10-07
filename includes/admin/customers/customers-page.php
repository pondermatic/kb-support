<?php
/**
 * Customer Page
 *
 * @package     KBS
 * @subpackage  Customers/Functions
 * @copyright   Copyright (c) 2016, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Customers Page
 *
 * Renders the customers page contents.
 *
 * @since	1.0
 * @return	void
*/
function kbs_customers_page() {
	$default_views = kbs_customer_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'customers';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[$requested_view] ) ) {
		kbs_render_customer_view( $requested_view, $default_views );
	} else {
		kbs_customers_list();
	}
} // kbs_customers_page

/**
 * Register the views for customer management
 *
 * @since	1.0
 * @return	arr		Array of views and their callbacks
 */
function kbs_customer_views() {
	$views = array();

	return apply_filters( 'kbs_customer_views', $views );
} // kbs_customer_views

/**
 * Register the tabs for customer management.
 *
 * @since	1.0
 * @return	arr		Array of tabs for the customer
 */
function kbs_customer_tabs() {
	$tabs = array();

	return apply_filters( 'kbs_customer_tabs', $tabs );
} // kbs_customer_tabs

/**
 * List table of customers
 *
 * @since	1.0
 * @return	void
 */
function kbs_customers_list() {
	include( dirname( __FILE__ ) . '/class-kbs-customer-table.php' );

	$customers_table = new KBS_Customer_Table();
	$customers_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php _e( 'Customers', 'kbs-support' ); ?></h1>
		<?php do_action( 'kbs_customers_table_top' ); ?>
		<form id="kbs-customers-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers' ); ?>">
			<?php
			$customers_table->search_box( __( 'Search Customers', 'kbs-support' ), 'kbs-customers' );
			$customers_table->display();
			?>
			<input type="hidden" name="post_type" value="kbs_ticket" />
			<input type="hidden" name="page" value="kbs-customers" />
			<input type="hidden" name="view" value="customers" />
		</form>
		<?php do_action( 'kbs_customers_table_bottom' ); ?>
	</div>
	<?php
} // kbs_customers_list

/**
 * Renders the customer view wrapper
 *
 * @since	1.0
 * @param	str		$view		The View being requested
 * @param	arr		$callbacks	The Registered views and their callback functions
 * @return	void
 */
function kbs_render_customer_view( $view, $callbacks ) {

	$render = isset( $_GET['render'] ) ? $_GET['render'] : true;

	$customer_view_role = apply_filters( 'kbs_view_customers_role', 'view_ticket_reports' );

	$url = remove_query_arg( array( 'kbs_message', 'render' ) );

	if ( ! current_user_can( $customer_view_role ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'customer_list_permission',
			'render'      => 0
		), $url ) );
		die();
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'invalid_customer_id',
			'render'      => 0
		), $url ) );
		die();
	}

	$customer_id = (int)$_GET['id'];
	$customer    = new KBS_Customer( $customer_id );

	if ( empty( $customer->id ) ) {
		wp_safe_redirect( add_query_arg( array(
			'kbs_message' => 'invalid_customer_id',
			'render'      => 0
		), $url ) );
		die();
	}

	$customer_tabs = kbs_customer_tabs();
	?>

	<div class='wrap'>
		<h2><?php _e( 'Customer Details', 'easy-digital-downloads' );?></h2>
		<?php if ( edd_get_errors() ) :?>
			<div class="error settings-error">
				<?php edd_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $customer && $render ) : ?>

			<div id="kbs-item-tab-wrapper" class="customer-tab-wrapper">
				<ul id="kbs-item-tab-wrapper-list" class="ustomer-tab-wrapper-list">
				<?php foreach ( $customer_tabs as $key => $tab ) : ?>
					<?php $active = $key === $view ? true : false; ?>
					<?php $class  = $active ? 'active' : 'inactive'; ?>

					<li class="<?php echo sanitize_html_class( $class ); ?>">
					<?php if ( ! $active ) : ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-customers&view=' . $key . '&id=' . $customer->id . '#wpbody-content' ) ); ?>">
					<?php endif; ?>
						<span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>" aria-hidden="true"></span>
						<span class="screen-reader-text"><?php echo esc_attr( $tab['title'] ); ?></span>
					<?php if ( ! $active ) : ?>
						</a>
					<?php endif; ?>
					</li>

				<?php endforeach; ?>
				</ul>
			</div>

			<div id="kbs-item-card-wrapper" class="kbs-customer-card-wrapper" style="float: left">
				<?php $callbacks[$view]( $customer ) ?>
			</div>

		<?php endif; ?>

	</div>
	<?php

} // kbs_render_customer_view
