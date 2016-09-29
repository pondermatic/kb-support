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

                                <?php if ( ! empty( $settings['description'] ) && 'label' == $settings['description_pos'] ) : ?>
                                	<?php kbs_display_form_field_description( $field, $settings ); ?>
                                <?php endif; ?>

                            <?php endif; ?>

                            <?php $kbs_form->display_field( $field, $settings ); ?>

                            <?php if ( ! empty( $settings['description'] ) && 'field' == $settings['description_pos'] ) : ?>
                                	<?php kbs_display_form_field_description( $field, $settings ); ?>
                                <?php endif; ?>
                        </p>

                <?php endforeach; ?>

        		<?php do_action( 'kbs_ticket_form_after_fields' ); ?>
            </fieldset>
            <?php do_action( 'kbs_ticket_form_before_submit' ); ?>
            <fieldset id="kbs_ticket_form_submit">
            	<?php kbs_render_hidden_form_fields( $kbs_form->ID ); ?>
                <input class="button" name="kbs_ticket_submit" id="kbs_ticket_submit" type="submit" value="<?php esc_attr_e( kbs_get_form_submit_label() ); ?>" />
            </fieldset>
        	<?php do_action( 'kbs_ticket_form_bottom' ); ?>
        </form>
        <?php do_action( 'kbs_after_ticket_form' ); ?>
    </div><!--end #kbs_ticket_form_wrap-->
</div><!-- end of #kbs_ticket_wrap -->
