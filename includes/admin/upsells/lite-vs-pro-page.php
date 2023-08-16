<?php
// Set the PROs
$pro_arguments = array(
	'email_signatures'            => array(
		'title'       => esc_html__( 'Email Signatures', 'kb-support' ),
		'description' => esc_html__( 'Enable each agent to register a custom signature which can be inserted into any of the emails that are sent to a customer.', 'kb-support' ),
	),
	'reply_approvals'            => array(
		'title'       => esc_html__( 'Reply Approvals', 'kb-support' ),
		'description' => esc_html__( 'Adds an approval process to ticket replies created by selected agents.', 'kb-support' ),
	),
	'easy_digital_downloads'     => array(
		'title'       => esc_html__( 'Easy Digital Downloads', 'kb-support' ),
		'description' => esc_html__( 'Integrate your EDD store with KB Support to provide a complete sales and support solution for you and your customers.', 'kb-support' ),
	),
	'custom_ticket_status'       => array(
		'title'       => esc_html__( 'Custom Ticket Status', 'kb-support' ),
		'description' => esc_html__( 'Create custom ticket statuses and choose whether or not to send customer emails when a ticket enters the custom status.', 'kb-support' ),
	),
	'mailchimp'                  => array(
		'title'       => esc_html__( 'MailChimp Integration', 'kb-support' ),
		'description' => esc_html__( 'Grow your subscriptions by enabling quick and seamless customer sign-ups to your MailChimp newsletter lists via KB Support.', 'kb-support' ),
	),
	'email_support'              => array(
		'title'       => esc_html__( 'Email Support', 'kb-support' ),
		'description' => esc_html__( 'With email piping customers and agents can manage tickets via email without having to access your website. Agents can use built-in email commands to automate processes.', 'kb-support' ),
	),
	'woocommerce'                => array(
		'title'       => esc_html__( 'WooCommerce', 'kb-support' ),
		'description' => esc_html__( 'Integrate your WooCommerce store with KB Support to provide a complete sales and support solution for you and your customers.', 'kb-support' ),
	),
	'advanced_ticket_assignment' => array(
		'title'       => esc_html__( 'Advanced Ticket Assignment', 'kb-support' ),
		'description' => esc_html__( 'Add intelligent automatic assignment of tickets to KB Support and streamline your support processes', 'kb-support' ),
	),
	'canned_replies'             => array(
		'title'       => esc_html__( 'Canned Replies', 'kb-support' ),
		'description' => esc_html__( 'Create a series of replies that can be accessed directly from the ticket reply screen. Agents can click the relevant reply and have it instantly inserted into the ticket.', 'kb-support' ),
	),
	'ratings_and_satisfaction'   => array(
		'title'       => esc_html__( 'Ratings and Satisfaction', 'kb-support' ),
		'description' => esc_html__( 'The Ratings and Satisfaction extension allows customers and visitors to easily provide you with valuable feedback on your support services and documentation.', 'kb-support' ),
	),
	'knowledge_base'             => array(
		'title'       => esc_html__( 'Knowledge Base Integrations', 'kb-support' ),
		'description' => esc_html__( 'Integrate your existing knowledge base solution into KB Support whilst preserving the features of both products to create the ultimate support tool for your business.', 'kb-support' ),
	),
	'zapier'                     => array(
		'title'       => esc_html__( 'Zapier', 'kb-support' ),
		'description' => esc_html__( 'Connect KB Support to thousands of 3rd party applications via Zapier and fully automate your support workflows', 'kb-support' ),
	),

);
?>
<div class="wrap rsvp-lite-vs-premium">
	<hr class="wp-header-end" />
	<div class="free-vs-premium">
		<!--  Table header -->
		<div class="wpchill-plans-table table-header">
			<div class="wpchill-pricing-package wpchill-empty">
				<!--This is an empty div so that we can have an empty corner-->
			</div>
			<div class="wpchill-pricing-package wpchill-title">
				<p class="wpchill-name"><strong>PREMIUM</strong></p>
			</div>
			<div class="wpchill-pricing-package wpchill-title wpchill-kbs">
				<p class="wpchill-name"><strong>LITE</strong></p>
			</div>
		</div>
		<!--  Table content -->

        <?php
        foreach( $pro_arguments as $pro ) {
            ?>
            <div class="wpchill-plans-table">
			<div class="wpchill-pricing-package feature-name">
				<h3><?php echo esc_html( $pro['title']); ?></h3>
				<p class="tab-header-description modula-tooltip-content">
					<?php echo esc_html( $pro['description'] ); ?>
				</p>
			</div>
			<div class="wpchill-pricing-package">
				<span class="dashicons dashicons-saved"></span>
			</div>
			<div class="wpchill-pricing-package">
				<span class="dashicons dashicons-no-alt"></span>
			</div>
		</div>
            <?php
        }
        ?>
		<!-- Support -->
		<div class="wpchill-plans-table">
			<div class="wpchill-pricing-package feature-name">
				<h3><?php esc_html_e( 'Support', 'kb-support' ); ?></h3>
			</div>
			<div class="wpchill-pricing-package">Priority</div>
			<div class="wpchill-pricing-package"><a href="https://wordpress.org/support/plugin/kb-support/"
					target="_blank">wp.org</a>
			</div>
		</div>
		<!--  Table footer -->
		<div class="wpchill-plans-table tabled-footer">
			<div class="wpchill-pricing-package wpchill-empty">
				<!--This is an empty div so that we can have an empty corner-->
			</div>
			<div class="wpchill-pricing-package wpchill-title wpchill-modula-grid-gallery-business">

				<a href="https://kb-support.com/pricing/?utm_source=kb_support&utm_medium=lite-vs-pro&utm_campaign=upsell" target="_blank"
					class="button button-primary button-hero "><span class="dashicons dashicons-cart"></span>
					<?php esc_html_e( 'Upgrade now!', 'kb-support' ); ?> </a>

			</div>
			<div class="wpchill-pricing-package wpchill-title">


			</div>
		</div>
	</div>
</div>