=== KB Support ===
Contributors: mikeyhoward1977
Tags: Helpdesk, Help Desk, Support, Customer Support, Service, Service Desk, ITIL, Support Helpdesk, Ticket, Ticket System, Support Tickets, Helpdesk Tickets, Knowledgebase, Knowledge Base, Service Level, SLA
Requires at least: 4.1
Tested up to: 4.8
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://kb-support.com/donate-kb-support/

The ultimate help desk and knowledge base support tool plugin for WordPress.

== Description ==

An effective Helpdesk / Support desk tool is one that encourages users to find and implement the solution to their queries without actually logging a support ticket and KB Support has been developed to do exactly that!

From the moment your customers begin to log a ticket, KB Support will search your knowledgebase and offer potential solutions based upon the articles that are published on your site.

From your own experience in running a helpdesk for your product(s) you will know that in a busy environment, documenting solutions and making them readily available to your customers always seems to take a back seat. KB Support makes this process much easier and encourages your support desk agents to produce articles when working through resolution of a customers ticket. Those articles are then immediately available on your website, and automatically referenced during a support ticket submission as a potential solution.

Key Features of KB Support include:

* Easily manage and customise submission forms. No coding required, multiple forms can be created and utilised
* Guest submission is fully supported. Customers do not need an account to create or manage support tickets.
* A fully responsive and clean front end design where customers can manage their tickets, including viewing and creating replies
* Integrated Knowledge Base Articles can be easily created and referenced to try and offer solutions without tickets being created
* Ability to restrict access to individual knowledge base articles to logged in users only
* Auto assign new tickets to agents based on current ticket count, or randomly
* Tracks an agents status so you can see if they are online or offline
* A number of useful shortcodes to display submission forms, KB Article lists, ticket history, login/registration forms, profile editor, KB Article search form - and more
* Private notes that are visible to agents only
* Restrict which tickets an agent can view. i.e. Just those to which they are assigned
* A bunch of hooks and filters for our developer friends

More information can be found at [https://kb-support.com/](https://kb-support.com/).

**Follow this plugin on [GitHub](https://github.com/KB-Support/kb-support)**

**Languages**

Would you like to help translate the plugin into more languages? [Join our WP-Translations Community](https://kb-support.com/articles/translating-kb-support/).

== Installation ==

**Automated Installation**

1. Activate the plugin
1. Go to Tickets > Settings and configure the options
1. Insert form shortcode into the ticket submission page
1. For detailed setup instructions, vist the official [Documentation](https://kb-support.com/articles/category/getting-started/)

**Manual Installation**

Once you have downloaded the plugin zip file, follow these simple instructions to get going;

1. Login to your WordPress administration screen and select the "Plugins" -> "Add New" from the menu
1. Select "Upload Plugin" from the top of the main page
1. Click "Choose File" and select the kb-support.zip file you downloaded
1. Click "Install Now"
1. Once installation has finished, select "Activate Plugin"

== Frequently Asked Questions ==

= Where can I find documentation? =

Searchable docs can be found at [https://kb-support.com]([https://kb-support.com)

= How do I Add a Submission Form? =

Create your submission form within Tickets > Submission Forms, and copy the shortcode to any page. i.e. [kbs_submit form="1277"].

A default form with the most popular fields is added during installation. Customise this form to meet your needs, or create new forms as required.

= How can a Customer view their Ticket History? =

Place the [kbs_tickets] shortcode on any page.

= Is there a Pro version with additional features? =

Extensions are available at [https://kb-support.com/extensions/](https://kb-support.com/extensions/ "KB Support Extensions") to further enhance KB Support features and functionality. New extensions are being added regularly. [Join our mailing list](http://eepurl.com/cnxWcz) to be the first to hear about new releases and to receive a 25% discount off of your first purchase!

== Screenshots ==

1. The interface for managing a ticket Submission Form. Create as many forms as you need, choose from a number of fields and add to your web page with a simple shortcode

2. What the default submission form might look like on your website when your customers log a support ticket. Theme in image is Twenty Seventeen

3. Suggested KB Article to resolve a ticket that a customer is in the process of submitted

4. The ticket overview screen a customer see's once their submission is completed

5. The edit ticket screen as seen by an agent

6. When a customer is accessing a KB Article that is restricted

== Changelog ==

= 1.0 =

**New**

* Company Support
	* You can now create companies
	* Customers can be added to a company

* Service Level Tracking
	* Enable SLA tracking within Tickets -> Settings -> Service Levels
	* Define your target response and resolution times
	* The ticket edit screen will display the status of the SLA for each newly created ticket. Green is good, amber if SLA is nearing expiry, red if expired

**Bug Fixes**

* Corrected descriptions for email headers in settings

**Tweaks**

* Make sure `$current_meta` array exists to avoid potential PHP notices
* Remove all SLA related meta keys from DB as SLA's were not tracked until this version
* Log the current KBS version number at the time each ticket was logged
* Ensure that the last modified date is updated for a ticket when a reply or note is added
* Add log entries when notes are added to tickets
* When a ticket is deleted, make sure to delete all associated replies and log entries from the `posts` and `postmeta` database tables

= 0.9.5 =

**IMPORTANT**

* Fatal error may occur when submitting a ticket from the front end if PHP 7.1 is running

= 0.9.4 =

**Bug Fix**

* Fixed an issue whereby new tickets created within admin were not set the correct status

**Tweaks**

* Ensure the trash view is displayed at the end of all views on the edit tickets screen

= 0.9.3 =

**New**

* Added **Link KB Article** media button to admin ticket reply form enabling quick and easy inserting of links to KB Articles from reply form

**Tweaks**

* Tidied javascript code
* Added draft updated message for articles and forms
* Users with manage_ticket_settings capability (Administrator & Support Manager by default) can now delete tickets
* Support Managers should always see all tickets, even when the `Restrict Agent Ticket View?` setting option is enabled

**Bug Fixes**

* Re-open ticket link failed to re-open ticket
* Don't count agent views for articles
* Support Customers should not have the upload_files capability

= 0.9.2 =

**Tweaks**

* Added upgrade functions
* Additional hooks within the view-ticket.php template
* Added plugin links
* Increment tested with to 4.7.2

= 0.9.1 =

**Tweaks**

* Added filter `kbs_user_profile_fields` to enable plugins to register user profile fields within the KB Support section
* Added hook `kbs_display_user_profile_fields` to enable plugins to output user profile fields
* Added banned emails to tools page. Form submissions containing banned addresses will be rejected
* Added System Info to tools page
* Updated call to `wp_register_style()` to display version
* Added `kbs_get_agent_id_from_ticket( $ticket_id )` function to retrieve an agent ID directly from the post meta table
* Removed dynamic `do_action()` calls from $_POST and $_GET submissions
* Only enqueue chosen menu's on submission page

= 0.9 =

**Release**

Our initial release!

== Upgrade Notice ==
