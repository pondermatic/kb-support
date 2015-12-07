<?php
	defined( 'ABSPATH' ) or die( "Direct access to this page is disabled!!!" );
/**
 * KB Support Uninstall Procedures
 * 
 * 
 * 
 */
global $wp_roles;
/**
 * Remove the user roles and capabilities
 */
// Loop through all user roles and remove our custom caps
$caps = array( 
	'kbs_admin', 'kbs_user', 'edit_ticket', 'read_ticket', 'delete_ticket', 'edit_tickets',
	'edit_others_tickets', 'publish_tickets', 'read_private_tickets', 'edit_article',
	'read_article', 'delete_article', 'edit_articles', 'edit_others_articles',
	'publish_articles', 'read_private_articles'
);

// Loop through roles assigning capabilities
foreach( $caps as $cap )	{ 
	foreach( array_keys( $wp_roles->roles ) as $role ) {
		$wp_roles->remove_cap( $role, $cap );
	}
}

// Remove the custom KBS roles
$roles = array( 'kbs_manager', 'kbs_agent', 'kbs_customer' );
foreach( $roles as $role )	{
	remove_role( $role );	
}
?>