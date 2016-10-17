<?php
/**
 * This template is used to display the KB Article search form with [kbs_search]
 */
$single = kbs_get_article_label_singular();
$plural = kbs_get_article_label_plural();
?>

<form role="search" method="get" class="search-form" id="kbs-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">

	<label>

		<span class="screen-reader-text">
			<?php echo sprintf( _x( 'Search %s for:', 'label', 'kb-support' ), $plural ); ?>
        </span>

		<input type="search" class="search-field" id="kbs-search-field" placeholder="<?php echo sprintf( esc_attr_x( 'Search %s &hellip;', 'placeholder', 'kb-support' ), $plural ); ?>" value="<?php echo get_search_query(); ?>" name="s_article" title="<?php echo sprintf( esc_attr_x( 'Search %s for:', 'label', 'kb-support' ), $plural ); ?>" />

	</label>

	<input type="hidden" name="kbs_action" value="search_articles" />

	<button type="submit" class="search-submit" id="kbs-search-submit">
    	<span class="screen-reader-text">
			<?php echo _x( 'Search', 'submit button', 'kb-support' ); ?>
        </span>
    </button>

</form>
