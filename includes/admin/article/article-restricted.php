<?php
/**
 * Manage article taxonomies.
 * 
 * @since		1.0
 * @package		KBS
 * @subpackage	Posts/Taxonomies
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Adds the restricted checkbox for new terms.
 *
 * @since	1.0
 * @param	obj		$term	The term object.
 * @return	str
 */
function kbs_article_taxonomy_restricted_add_meta_field()	{
	?>
    <div class="form-field term-restricted-wrap">
        <label for="tag-restricted"><input type="checkbox" name="tag-restricted" id="tag-restricted" value="1" /> <?php _e( 'Restrict Access?' ); ?></label>
        <p><?php _e( 'Restrict access to logged in users?', 'kb-support' ); ?></p>
    </div>
    <?php
}
// kbs_article_taxonomy_restricted_add_meta_field
add_action( 'article_category_add_form_fields', 'kbs_article_taxonomy_restricted_add_meta_field' );

/**
 * Adds the restricted checkbox when editing terms.
 *
 * @since	1.0
 * @param	obj		$term	The term object.
 * @return	str
 */
function kbs_article_taxonomy_restricted_edit_meta_field( $term )	{
	$restricted = kbs_article_is_term_restricted( $term->term_id );
	?>
    <tr class="form-field term-restricted-wrap">
	<th scope="row" valign="top"><label for="tag-restricted	"><?php _e( 'Restrict Access?', 'kb-support' ); ?></label></th>
		<td>
			<input type="checkbox" name="tag-restricted" id="tag-restricted" value="1"<?php checked( '1', $restricted ); ?> />
			<p class="description"><?php _e( 'Restrict access to logged in users?', 'kb-support' ); ?></p>
		</td>
	</tr>
    <?php
}
// kbs_article_taxonomy_restricted_edit_meta_field
add_action( 'article_category_edit_form_fields', 'kbs_article_taxonomy_restricted_edit_meta_field' );

/**
 * Saves the restricted setting for the term.
 *
 * @since	1.0
 * @param	int		$term_id	The term ID
 * @param	str		$taxonomy	The taxonomy
 * @return	str
 */
function kbs_article_save_restricted_meta( $term_id, $taxonomy )	{
	$restricted = ! empty( $_POST['tag-restricted'] ) ? true : false;

	if ( $restricted )	{
		update_term_meta( $term_id, '_kbs_term_restricted', $restricted );
	} else	{
		delete_term_meta( $term_id, '_kbs_term_restricted' );
	}
} // kbs_article_save_restricted_meta
add_action( 'edited_article_category', 'kbs_article_save_restricted_meta', 10, 2 );
add_action( 'create_article_category', 'kbs_article_save_restricted_meta', 10, 2 );

/**
 * Adds the visibility column to the terms list.
 *
 * @since	1.0
 * @param	arr		$columns	Array of table columns.
 * @return	arr		Array of table columns
 */
function kbs_article_tax_add_visibility_column( $columns )	{
    $columns['visibility'] = __( 'Visibility', 'kb-support' );

    return $columns;
} // kbs_article_tax_add_visibility_column
add_filter('manage_edit-article_category_columns', 'kbs_article_tax_add_visibility_column');

/**
 * Renders the data for the visibility column on the terms list.
 *
 * @since	1.0
 * @param	str		$string			String to render
 * @param	str		$column_name	Column Name
 * @param	str		$term_id		Term ID
 * @return	str		String to render
 */
function kbs_article_tax_render_visibility_column_content( $content, $column_name, $term_id )	{
	if ( kbs_article_is_term_restricted( $term_id ) )	{

		$label = kbs_get_taxonomy_labels( $_GET['taxonomy'] );
		echo '<span class="padlock" title="' . sprintf( __( 'This is a restricted %s', 'kb-support' ), strtolower( $label['singular_name'] ) ) . '"></span>';

	}
	do_action( 'kbs_article_tax_column_visibility', $term_id );

	return $content;
} // kbs_article_tax_render_visibility_column_content
add_filter( 'manage_article_category_custom_column', 'kbs_article_tax_render_visibility_column_content', 10, 3 );
