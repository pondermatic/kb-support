<?php
/**
 * This template is used to display the form for submitting a ticket [kbs_form]
 */
global $kbs_form;
?>

<div id="kbs_ticket_wrap">
	<?php do_action( 'kbs_notices' ); ?>
	<div id="kbs_ticket_form_wrap" class="kbs_clearfix">
        <?php do_action( 'kbs_before_ticket_form' ); ?>
        <form<?php kbs_maybe_set_enctype(); ?> id="kbs_ticket_form" class="kbs_form" action="" method="post">
    		<div class="kbs_alert kbs_alert_error kbs_hidden"></div>
            <?php do_action( 'kbs_ticket_form_top' ); ?>
    
            <fieldset id="kbs_ticket_form_fields">
                <legend><?php esc_attr_e( get_the_title( $kbs_form->ID ) ); ?></legend>
        
                <?php foreach( $kbs_form->fields as $field ) : ?>
                
                    <?php $settings = $kbs_form->get_field_settings( $field->ID ); ?>
                    
                        <p class="kbs-<?php echo $field->post_name; ?>">
                            <?php if ( empty( $settings['hide_label'] ) && 'recaptcha' != $settings['type'] ) : ?>
                                <label for="<?php echo $field->post_name; ?>">
									<?php esc_attr_e( get_the_title( $field->ID ) ); ?>
                                    <?php if ( $settings['required'] ) : ?>
                                        <span class="kbs-required-indicator">*</span>
                                    <?php endif; ?>
                                </label>
                            <?php endif; ?>
        
                            <?php $kbs_form->display_field( $field, $settings ); ?>
                        </p>

                <?php endforeach; ?>

        		<?php do_action( 'kbs_ticket_form_after_fields' ); ?>
            </fieldset>
            <?php do_action( 'kbs_ticket_form_before_submit' ); ?>
            <fieldset id="kbs_ticket_form_submit">
                <input type="hidden" name="kbs_form_id" value="<?php echo $kbs_form->ID; ?>" />
                <input type="hidden" name="kbs_honeypot" id="kbs_honeypot" value="" />
                <input type="hidden" name="redirect" value="<?php echo kbs_get_current_page_url(); ?>" />
                <input type="hidden" name="action" value="kbs_validate_ticket_form" />
                <input class="button" name="kbs_ticket_submit" id="kbs_ticket_submit" type="submit" value="<?php esc_attr_e( kbs_get_form_submit_label() ); ?>" />
            </fieldset>
        	<?php do_action( 'kbs_ticket_form_bottom' ); ?>
        </form>
        <?php do_action( 'kbs_after_ticket_form' ); ?>
    </div><!--end #kbs_ticket_form_wrap-->
</div><!-- end of #kbs_ticket_wrap -->
