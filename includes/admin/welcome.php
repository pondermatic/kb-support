<?php
/**
 * Weclome Page Class
 *
 * @package     KBS
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since	1.0
 */
class KBS_Welcome {

	/**
	 * @var	str		The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_ticket_settings';

	/**
	 * @var	str		Singular label for tickets
	 */
	public $ticket_singular = 'Ticket';

	/**
	 * @var	str		Plural label for tickets
	 */
	public $ticket_plural = 'Tickets';

	/**
	 * @var	str		Singular label for KB Articles
	 */
	public $article_singular = 'KB Article';

	/**
	 * @var	str		Plural label for KB Articles
	 */
	public $article_plural = 'KB Articles';

	/**
	 * Get things started
	 *
	 * @since	1.0
	 */
	public function __construct()	{
		$this->ticket_singular  = kbs_get_ticket_label_singular();
		$this->ticket_plural    = kbs_get_ticket_label_plural();
		$this->article_singular = kbs_get_article_label_singular();
		$this->article_plural   = kbs_get_article_label_plural();

		add_action( 'admin_menu', array( $this, 'admin_menus') );
		//add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	} // __construct

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to KB Support', 'kb-support' ),
			__( 'Welcome to KB Support', 'kb-support' ),
			$this->minimum_capability,
			'kbs-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'KB Support Changelog', 'kb-support' ),
			__( 'KB Support Changelog', 'kb-support' ),
			$this->minimum_capability,
			'kbs-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with KB Support', 'kb-support' ),
			__( 'Getting started with KB Support', 'kb-support' ),
			$this->minimum_capability,
			'kbs-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Now remove them from the menus so plugins that allow customizing the admin menu don't show them
		remove_submenu_page( 'index.php', 'kbs-about' );
		remove_submenu_page( 'index.php', 'kbs-changelog' );
		remove_submenu_page( 'index.php', 'kbs-getting-started' );

	} // admin_menus

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function admin_head() {
		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.kbs-about-wrap .kbs-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 200px; position: absolute; top: 0px; right: 0px; text-align: right; }
			.kbs-about-wrap .kbs-badge img { border: none; }
			.kbs-about-wrap .kbs-badge .kbs-version { font-size: 14px; }
			.kbs-about-wrap #kbs-header { margin-bottom: 15px; }
			.kbs-about-wrap #kbs-header h1 { margin-bottom: 15px !important; }
			.kbs-about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
			.kbs-about-wrap .feature-section { margin-top: 5px; }
			.kbs-about-wrap .feature-section-content,
			.kbs-about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
			.kbs-about-wrap .feature-section-content { float: left; padding-right: 50px; }
			.kbs-about-wrap .feature-section-content h4 { margin: 0 0 1em; }
			.kbs-about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 5px; }
			.kbs-about-wrap .feature-section-media img { border: 1px solid #ddd; }
			.kbs-about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
			.kbs-about-wrap ul { list-style-type: disc; padding-left: 20px; }
			/* responsive */
			@media all and ( max-width: 782px ) {
				.kbs-about-wrap .feature-section-content,
				.kbs-about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
				.kbs-about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
			}
			/*]]>*/
		</style>
		<?php
	} // admin_head

	/**
	 * Welcome message
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function welcome_message() {
		list( $display_version ) = explode( '-', KBS_VERSION );

		$page = isset( $_GET['page'] ) ? $_GET['page'] : 'kbs-about';

		?>
        <h1><?php _e( 'Welcome to KB Support!', 'kb-support' ); ?></h1>
		<div class="kbs-badge"><img src="<?php echo KBS_PLUGIN_URL; ?>assets/images/kbs-logo.png" height="75" width="393" />
			<span class="kbs-version"><?php printf( __( 'Version %s', 'kb-support' ), $display_version ); ?></span>
        </div>
        <p class="about-text">
            <?php
            switch ( $page )	{
                case 'kbs-getting-started':
                    _e( 'Thank you for installing KB Support!', 'kb-support' );
					echo '<br />';
					_e( "You are now equipped with the best tool to provide your customers with an exceptional support experience.", 'kb-support' );
                    break;

                default:
                    _e( 'Thank you for updating to the latest version!', 'kb-support' );
                    echo '<br />';
                    printf(
                        __( 'KB Support %s is ready to make support business even more efficient.', 'kb-support' ),
                        $display_version
                    );
            }
            ?>
        </p>
		<?php
	} // welcome_message

	/**
	 * Navigation tabs
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function tabs()	{
		$selected        = isset( $_GET['page'] ) ? $_GET['page'] : 'kbs-getting-started';
		$about_url       = esc_url( admin_url( add_query_arg( array( 'page' => 'kbs-about' ), 'index.php' ) ) );
		$get_started_url = esc_url( admin_url( add_query_arg( array( 'page' => 'kbs-getting-started' ), 'index.php' ) ) );
		?>

		<h2 class="nav-tab-wrapper wp-clearfix">			
			<a href="<?php echo $about_url; ?>" class="nav-tab <?php echo $selected == 'kbs-about' ? 'nav-tab-active' : ''; ?>">
				<?php _e( "What's New", 'kb-support' ); ?>
			</a>
			<a href="<?php echo $get_started_url; ?>" class="nav-tab <?php echo $selected == 'kbs-getting-started' ? 'nav-tab-active' : ''; ?>">
				<?php _e( 'Getting Started', 'kb-support' ); ?>
			</a>
		</h2>

		<?php
	} // tabs

	/**
	 * Render About Screen
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap kbs-about-wrap">
			<?php
				// Load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>

			<div>
            	<p><?php _e( "With the release of KB Support version 1.0 we're not only adding even more functionality, we're also officially out of beta testing!", 'kb-support' ); ?></p>
                <p><?php _e( "Let's take a look at what KB Support version 1.0 has to offer...", 'kb-support' ); ?></p>
            </div>

			<div class="feature-section two-col">
            	<h2><?php _e( 'Service Level Tracking', 'kb-support' ); ?></h2>
                <div class="col">
                	<p><?php printf( __( 'A company who takes support seriously not only provides customers with targetted response and resolution times for support %s, but also measures their performance against these targets.', 'kb-support' ), strtolower( $this->ticket_plural ) ); ?></p>
                    <p><?php printf( __( 'KB Support now allows you to specify the targeted response and resolution time for %s that are logged.', 'kb-support' ), strtolower( $this->ticket_plural ) ); ?></p>
                    <p><?php printf( __( 'When viewing %1$s, visual indicators will now display the status of these service level targets enabling you to quickly identify any %1$s that are approaching, or have already exceeded, the defined SLA.', 'kb-support' ), strtolower( $this->ticket_plural ) ); ?></p>
                    <div class="return-to-dashboard">
                        <a href="<?php echo add_query_arg( array(
							'post_type' => 'kbs_ticket',
							'page'      => 'kbs-settings',
							'tab'       => 'tickets',
							'section'   => 'sla'
							),
							admin_url( 'edit.php' )
						); ?>">
                            &rarr; <?php _e( 'Enable SLA Tracking and Define Targets', 'kb-support' ); ?>
                        </a>
                    </div>
                </div>
                <div class="col">
                	<img src="<?php echo KBS_PLUGIN_URL . 'assets/images/screenshots/10-sla-settings.jpg'; ?>" style="border: none;" />
                </div>
			</div>

			<hr />

			<div class="feature-section two-col">
            	<h2><?php _e( 'Information at your Fingertips', 'kb-support' ); ?></h2>
                <div class="col">
                	<p><?php printf( __( 'The new KB Support %1$s Summary dashboard widget provides you with an overview of open and closed %2$s over varying periods of time.', 'kb-support' ), $this->ticket_singular, strtolower( $this->ticket_plural ) ); ?></p>
                    <p><?php printf( __( 'Easily identify the number of %1$s that have been opened and closed on the current day, month, and previous month. Additionally, you can see the current total number of open %1$s and how many of your Support Workers are currently available.', 'kb-support' ), strtolower( $this->ticket_plural ) ); ?></p>
                </div>
                <div class="col">
					<img src="<?php echo KBS_PLUGIN_URL . 'assets/images/screenshots/10-kbs-summary-dashboard.jpg'; ?>" style="border: none;" />
                </div>
			</div>

			<hr />

			<div class="feature-section two-col">
            	<h2><?php _e( 'Companies', 'kb-support' ); ?></h2>
                <div class="col">
                	<p><?php printf( __( "Create companies and add your customers to the companies to enable grouping of %s and restrictions to %s for specific companies.", 'kb-support' ), strtolower( $this->ticket_plural ), $this->article_plural ); ?></p>
                    <p><?php _e( 'Additional email tags have also been added to enable you to easily insert company specific information into emails', 'kb-support' ); ?>
                        <ul>
                            <li><?php _e( '<code>{company}</code> - The name of the company', 'kb-support' ); ?></li>
                            <li><?php _e( '<code>{company_contact}</code> - The contact name of the company', 'kb-support' ); ?></li>
                            <li><?php _e( '<code>{company_email}</code> - The email address of the company', 'kb-support' ); ?></li>
                            <li><?php _e( '<code>{company_phone}</code> - The phone number of the company', 'kb-support' ); ?></li>
                            <li><?php _e( '<code>{company_website}</code> - The website URL of the company', 'kb-support' ); ?></li>
                            <li><?php _e( '<code>{company_logo}</code> - Inserts the logo of the company', 'kb-support' ); ?></li>
                        </ul>
                    </p>
                    <div class="return-to-dashboard">
                        <a href="<?php echo admin_url( 'edit.php?post_type=kbs_company' ); ?>">
                            &rarr; <?php _e( 'Create a Company', 'kb-support' ); ?>
                        </a>
                    </div>
                </div>
                <div class="col">
                	<img src="<?php echo KBS_PLUGIN_URL . 'assets/images/screenshots/10-company-list.jpg'; ?>" style="border: none;" />
                </div>
			</div>

			<hr />

			<div class="changelog">
				<h2><?php _e( 'What else has changed?', 'kb-support' ); ?></h2>
				<div class="under-the-hood two-col">
                    <div class="col">
                        <h3><?php _e( 'Tweaks', 'kb-support' ); ?></h3>
                        <ul>
							<li><?php _e( "Removed all SLA related meta keys from the Database as SLA's were not tracked until this version", 'kb-support' ); ?></li>
							<li><?php _e( 'Log the current KBS version number at the time each ticket was logged', 'kb-support' ); ?></li>
                            <li><?php _e( 'Ensure that the last modified date is updated for a ticket when a reply or note is added', 'kb-support' ); ?></li>
                            <li><?php _e( 'Add log entries when notes are added to tickets', 'kb-support' ); ?></li>
                            <li><?php _e( 'When a ticket is deleted, make sure to delete all associated replies and log entries from the <em>posts</em> and <em>postmeta</em> database tables', 'kb-support' ); ?></li>
                            <li><?php _e( 'Added ticket and article count to the At a Glance dashboard widget', 'kb-support' ); ?></li>
                        </ul>
                    </div>
                    <div class="col">
                        <h3><?php _e( 'Bug Fixes', 'kb-support' ); ?></h3>
                        <ul>
							<li><?php _e( 'Corrected descriptions for email headers in settings', 'kb-support' ); ?></li>
                            <li><?php _e( 'Make sure <code>$current_meta</code> array exists to avoid potential PHP notices', 'kb-support' ); ?></li>
                            <li><?php _e( '<code>kbs_agent_ticket_count()</code> was not always returning the correct totalss', 'kb-support' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'kbs-settings' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to KB Support Settings', 'kb-support' ); ?></a> &middot;
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'kbs-changelog' ), 'index.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'kb-support' ); ?></a>
			</div>
		</div>
		<?php
	} // about_screen

	/**
	 * Render Changelog Screen
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function changelog_screen() {
		?>
		<div class="wrap about-wrap kbs-about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'kb-support' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'kbs_ticket', 'page' => 'kbs-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to KB Support Settings', 'kb-support' ); ?></a>
			</div>
		</div>
		<?php
	} // changelog_screen

	/**
	 * Render Getting Started Screen
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function getting_started_screen()	{
		$default_form = get_option( 'kbs_default_submission_form_created' );
		$form_url     = '#';

		if ( $default_form && 'publish' == get_post_status( $default_form ) )	{
			$form_url = admin_url( 'post.php?post=' . $default_form . '&action=edit' );
		}

		?>
		<div class="wrap about-wrap kbs-about-wrap">
			<?php
				// Load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
            <div class="feature-section two-col">
                <h2><?php printf( __( 'Start Receiving &amp; Managing Support %s', 'kb-support' ), $this->ticket_plural ); ?></h2>
                <div class="col">
                    <img src="<?php echo KBS_PLUGIN_URL . 'assets/images/screenshots/getting-started-email.png'; ?>" sizes="(max-width: 500px) calc(100vw - 40px), (max-width: 781px) calc((100vw - 70px) * .466), (max-width: 959px) calc((100vw - 116px) * .469), (max-width: 1290px) calc((100vw - 240px) * .472), 496px" />
                    <h3><?php _e( 'Optimise Settings', 'kb-support' ); ?></h3>
                    <p><?php _e( "KB Support will work as soon as installed and activated as we've set the default settings for you, however you should review the options and ensure they're fully optimised for your support business.", 'kb-support' ); ?></p>
                    <p><?php printf( __( "These settings define the communication flow and content between your business and your customers, as well as determine who can submit a %s, how %s are assigned to support workers, what tasks support workers can undertake, plus much more.", 'kb-support' ), strtolower( $this->ticket_singular ), strtolower( $this->ticket_plural ) ); ?></p>
                    <div class="return-to-dashboard">
                    	<a href="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-settings' ); ?>"><?php printf( __( '%s &rarr; Settings', 'kb-support' ), $this->ticket_plural ); ?></a>
                    </div>
                </div>
                <div class="col">
                    <img src="<?php echo KBS_PLUGIN_URL . 'assets/images/screenshots/getting-started-form.png'; ?>" sizes="(max-width: 500px) calc(100vw - 40px), (max-width: 781px) calc((100vw - 70px) * .466), (max-width: 959px) calc((100vw - 116px) * .469), (max-width: 1290px) calc((100vw - 240px) * .472), 496px" />
                    <h3><?php _e( 'Customise your Submission Form(s)', 'kb-support' ); ?></h3>
                    <p><?php printf( __( 'The %s submission forms are the first point at which your customers can provide you with details regarding the issues they are experiencing.', 'kb-support' ), strtolower( $this->ticket_singular ) ); ?></p>
					<p><?php printf( __( 'We created a <a href="%s">default form</a> for you during install, but it is fully customisable and you should ensure it has all the fields you need to maximise the opportunity of obtaining all information you need from your customers in order to efficiently address their issue.', 'kb-support' ), $form_url ); ?></p>
                    <div class="return-to-dashboard">
                    	<a href="<?php echo admin_url( 'edit.php?post_type=kbs_form' ); ?>"><?php printf( __( '%s &rarr; Submission Forms', 'kb-support' ), $this->ticket_plural ); ?></a>
                    </div>
                </div>
            </div>

            <hr />

			<div class="feature-section one-col">
                <h2><?php printf( __( 'Create %s', 'kb-support' ), $this->article_plural ); ?></h2>
                <p><?php printf( __( '%1$s provide a single document repository for your products and/or services that is readily available to support customers assisting them in resolving any issues they may be experiencing.', 'kb-support' ), $this->article_plural ); ?></p>
                <p><?php printf( __( 'Support Workers are provided with a number of prompts to quickly and easily publish new %1$s during the management of a %3$s and as soon as it is published, the %2$s is available to any customer via a general search, post listing, and whilst they are in the process of logging a new %3$s.', 'kb-support' ), $this->article_plural, $this->article_singular, strtolower( $this->ticket_singular ), strtolower( $this->ticket_plural ) ); ?></p>
                <p><?php printf( __( 'Furthermore, individual %1$s can be restricted so that only customers with an active account on your website are able to view their content.', 'kb-support' ), $this->article_plural, $this->article_singular ); ?></p>
                </p>
                <img src="<?php echo KBS_PLUGIN_URL . 'assets/images/screenshots/getting-started-kb-articles.png'; ?>" sizes="(max-width: 500px) calc(100vw - 40px), (max-width: 782px) calc(100vw - 70px), (max-width: 959px) calc(100vw - 116px), (max-width: 1290px) calc(100vw - 240px), 1050px" style="border: none;" />
                <div class="return-to-dashboard">
                	<a href="<?php echo admin_url( 'post-new.php?post_type=article' ); ?>"><?php printf( __( '%1$s &rarr; New %2$s', 'kb-support' ), $this->article_plural, $this->article_singular ); ?></a>
                </div>
            </div>

			<hr />

            <div class="changelog">
				<h2><?php _e( "We're Here to Help", 'kb-support' ); ?></h2>
				<div class="under-the-hood two-col">
                    <div class="col">
                        <h3><?php _e( 'Documentation', 'kb-support' ); ?></h3>
                        <p><?php _e( 'We have a growing library of <a href="https://kb-support.com/support/" target="_blank">Support Documents</a> to help new and advanced users with features and customisations.', 'kb-support' ); ?></p>
                    </div>
                    <div class="col">
                        <h3><?php _e( 'Excellent Support', 'kb-support' ); ?></h3>
                        <p><?php printf( __( 'We pride ourselves on our level of support and excellent response times. If you are experiencing an issue, <a href="%s" target="_blank">submit a support ticket</a> and we will respond quickly.', 'kb-support' ), 'https://kb-support.com/support-request/' );?></p>
                    </div>
                </div>

				<div class="under-the-hood two-col">
                    <div class="col">
                        <h3><?php _e( 'Get the Latest News','kb-support' ); ?></h3>
                        <p><?php printf( __( '<a href="%s" target="_blank">Subscribe to our Newsletter</a> for all the latest news and offers from KB Support.', 'kb-support' ), 'http://eepurl.com/cnxWcz' ); ?></p>
                    </div>
                    <div class="col">
                        <h3><?php _e( 'Get Social', 'kb-support' );?></h3>
                        <p><?php printf( __( 'The <a href="%s" target="_blank">KB Support Facebook Page</a> and our <a href="%s" target="_blank">Twitter Account</a> are also great places for the latest news.', 'kb-support' ), 'https://www.facebook.com/kbsupport/', 'https://twitter.com/kbsupport_wp' ); ?></p>
                    </div>
                </div>
            </div>

			<hr />

            <div class="feature-section no-heading two-col">
                <div class="col">
                    <h3><?php _e( 'Extensions', 'kb-support' ); ?></h3>
                    <p><?php printf( __( 'We have an ever growing catalogue of extensions available at our <a href="%s" target="_blank">plugin store</a> that will extend the functionality of KB Support and further enhance your customers support experience.', 'kb-support' ), 'https://kb-support.com/extensions/' ); ?></p>
                </div>
                <div class="col">
                    <h3><?php _e( 'Contribute to KB Support', 'kb-support' ); ?></h3>
                    <p><?php _e( 'Anyone is welcome to contribute to KB Support and we\'d love you to get involved with our project. Please read the <a href="" target="_blank">guidelines for contributing</a> to our <a href="" target="_blank">GitHub repository</a>.', 'kb-support' ); ?></p>
                    <p><?php _e( 'There are various ways you can contribute', 'kb-support' ); ?>&hellip;<br />
                       <?php printf( __( '<a href="%s" target="_blank">Raise an Issue on GitHub</a>', 'kb-support' ), 'https://github.com/KB-Support/kb-support/issues' ); ?><br />
                       <?php printf( __( '<a href="%s" target="_blank">Send us a Pull Request</a> with your bug fixes and/or new features', 'kb-support' ), 'https://www.google.co.uk/url?sa=t&rct=j&q=&esrc=s&source=web&cd=2&cad=rja&uact=8&ved=0ahUKEwikn8uql5fQAhXiDsAKHcP6AIQQFgggMAE&url=https%3A%2F%2Fhelp.github.com%2Farticles%2Fcreating-a-pull-request%2F&usg=AFQjCNEyxULKOpCMlFly-Rcy8_YemfrOhQ&sig2=OSYkosRNJKTjCkbKTS8Qdg&bvm=bv.137904068,d.bGg' ); ?><br />
                       <?php printf( __( '<a href="%s" target="_blank">Translate KB Support</a> into different languages', 'kb-support' ), 'https://kb-support.com/articles/translating-kb-support/' ); ?><br />
                       <?php printf( __( 'Provide feedback and suggestions on <a href="%s" target="_blank">enhancements</a>', 'kb-support' ), 'https://github.com/KB-Support/kb-support/issues' ); ?><br />
                       <?php _e( 'Assist with maintaining documentation', 'kb-support' ); ?></p>
                </div>
            </div>

			<hr />

			<div class="return-to-dashboard">
            	<a href="<?php echo admin_url( 'edit.php?post_type=kbs_ticket&page=kbs-settings' ); ?>">
					<?php _e( 'Configure Settings', 'kb-support' ); ?>
                </a> |
                <a href="<?php echo esc_url( self_admin_url( 'edit.php?post_type=kbs_ticket' ) ); ?>">
                    <?php printf( __( 'Go to %s', 'kb-support' ), $this->ticket_plural ); ?>
                </a> |
                 <a href="<?php echo esc_url( self_admin_url( 'edit.php?post_type=kbs_form' ) ); ?>">
                    <?php _e( 'Manage Submission Forms', 'kb-support' ); ?>
                </a> |
                <a href="<?php echo esc_url( self_admin_url( 'edit.php?post_type=article' ) ); ?>">
                    <?php printf( __( 'Go to %s', 'kb-support' ), $this->article_plural ); ?>
                </a> |
                <a href="https://kb-support.com/extensions/" target="_blank">
                    <?php _e( 'View Extensions', 'kb-support' ); ?>
                </a> |
                <a href="<?php echo admin_url(); ?>">
                    <?php _e( 'WordPress Dashboard', 'kb-support' ); ?>
                </a>
            </div>

		</div>
		<?php
	} // getting_started_screen

	/**
	 * Parse the KBS readme.txt file
	 *
	 * @since	1.0
	 * @return	str		$readme		HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( KBS_PLUGIN_DIR . 'readme.txt' ) ? KBS_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changelog was found.', 'kb-support' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
	} // parse_readme

	/**
	 * Sends user to the Welcome page on first activation of KBS as well as each
	 * time KBS is upgraded to a new major version
	 *
	 * @access	public
	 * @since	1.0
	 * @return	void
	 */
	public function welcome() {
		// Bail if no activation redirect
		if ( ! get_transient( '_kbs_activation_redirect' ) )	{
			return;
		}

		// Delete the redirect transient
		delete_transient( '_kbs_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )	{
			return;
		}

		$upgrade = get_option( 'kbs_version_upgraded_from' );

		if ( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=kbs-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=kbs-about' ) ); exit;
		}
	} // welcome
}
new KBS_Welcome();
