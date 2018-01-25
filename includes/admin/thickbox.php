<?php
/**
 * Thickbox
 *
 * @package     KBS
 * @subpackage  Admin
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds an "Link KB Article" button above the TinyMCE Editor on add/edit screens.
 *
 * @since	1.0
 * @global	$pagenow
 * @global	$typenow
 * @return	str		"Link KB Article" Button
 */
function kbs_media_button()	{

	global $pagenow, $typenow;

	if ( 'kbs_ticket' == $typenow && ( 'draft' == get_post_status() || 'auto-draft' == get_post_status() ) )	{
		return;
	}

	$output = '';

	$post_types = array( 'kbs_ticket' );
	$post_types = apply_filters( 'kbs_link_article_media_button_post_types', $post_types );

	/** Only run in post/page creation and edit screens */
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && in_array( $typenow, $post_types ) ) {

		$img = '<span class="wp-media-buttons-icon dashicons dashicons-admin-links" id="kbs-media-button"></span> ';
		$output = '<a href="#TB_inline?width=640&inlineId=choose-article" class="thickbox button kbs-thickbox" style="padding-left: .4em;">' . $img . sprintf( __( 'Link %s', 'kb-support' ), kbs_get_article_label_singular() ) . '</a>';

	}
	echo $output;
}
add_action( 'media_buttons', 'kbs_media_button', 11 );

/**
 * Admin Footer For Thickbox
 *
 * Prints the footer code needed for the Link KB Article
 * TinyMCE button.
 *
 * @since	1.0
 * @global	$pagenow
 * @global	$typenow
 * @return	void
 */
function kbs_admin_footer_for_thickbox() {

	if ( 'draft' == get_post_status() || 'auto-draft' == get_post_status() )	{
		return;
	}

	global $pagenow, $typenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && 'kbs_ticket' == $typenow )	{

		$single_article = kbs_get_article_label_singular(); ?>

		<script type="text/javascript">
			function linkArticle() {
				var url = jQuery('#articles').val(),
					text = jQuery('#kbs-text').val() || '';

				// Return early if no article is selected
				if ('' === url || '0' === url) {
					alert('<?php printf( __( "You must choose a %s.", "kb-support" ), $single_article ); ?>');
					return;
				}

				// Use article title if no link text specified
				if ('' === text)	{
					text = jQuery('#articles option[value="' + url + '"]').text();
				}

				// Send the shortcode to the editor
				window.send_to_editor('<a href="' + url + '" target="_blank">' + text + '</a>');
			}
		</script>

		<div id="choose-article" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<h3><?php echo sprintf( __( 'Complete the form below to insert a link to a %s', 'kb-support' ), $single_article ); ?></h3>

				<p>
                    <label for="kbs-text"><strong><?php _e( 'Enter Link Text', 'kb-support' ); ?></strong>:</label><br>
                	<input type="text" class="regular-text" size="30" id="kbs-text" value="" /><br>
					<span class="description"><?php printf( __( 'Leave empty to use %s title', 'kb-support' ), $single_article ); ?></span>
				</p>

				<p>
				    <label for="articles"><strong><?php printf( __( 'Select %s', 'kb-support' ), $single_article ); ?></strong>:</label><br>
					<?php echo KBS()->html->article_dropdown( array( 'name' => 'articles', 'key' => 'url', 'chosen' => true ) ); ?>
				</p>

				<p class="submit">
					<input type="button" id="kbs-insert-link" class="button-primary" value="<?php echo sprintf( __( 'Link %s', 'kb-support' ), $single_article ); ?>" onclick="linkArticle();" />
					<a id="kbs-cancel-link-article" class="button-secondary" onclick="tb_remove();"><?php _e( 'Cancel', 'kb-support' ); ?></a>
				</p>
			</div>
		</div>

	<?php
	}

} // kbs_admin_footer_for_thickbox
add_action( 'admin_footer', 'kbs_admin_footer_for_thickbox' );
