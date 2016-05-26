<?php
/**
 * KBS Form Class
 *
 * @package		KBS
 * @subpackage	Posts/Forms
 * @since		0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * KBS_Form Class
 *
 * @since	0.1
 */
class KBS_Form {
	
	/**
	 * The form ID
	 *
	 * @since	0.1
	 */
	public $ID = 0;
	
	/**
	 * The form config
	 *
	 * @since	0.1
	 */
	private $form_config;
	
	/**
	 * The form fields
	 *
	 * @since	0.1
	 */
	private $fields;
	
	/**
	 * The form layout
	 *
	 * @since	0.1
	 */
	private $layout = 0;
	
	/**
	 * Datepicker
	 *
	 * @since	0.1
	 */
	private $datepicker = false;
	
	/**
	 * recaptcha
	 *
	 * @since	0.1
	 */
	private $recaptcha = false;
		
	/**
	 * Get things going
	 *
	 * @since	0.1
	 */
	public function __construct( $_id = false, $_args = array() ) {
		$form = WP_Post::get_instance( $_id );
				
		return $this->setup_form( $form, $_args );
				
	} // __construct
	
	/**
	 * Given the form data, let's set the variables
	 *
	 * @since	0.1
	 * @param 	obj		$form	The Form post object
	 * @param	arr		$args	Arguments passed to the class on instantiation
	 * @return	bool			If the setup was successful or not
	 */
	private function setup_form( $form, $args ) {
		
		if( ! is_object( $form ) ) {
			return false;
		}

		if( ! is_a( $form, 'WP_Post' ) ) {
			return false;
		}

		if( 'kbs_form' !== $form->post_type ) {
			return false;
		}
		
		foreach ( $form as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}
		
		$this->get_form_config();
		$this->get_fields();
		
		$this->has_datepicker();
		$this->has_recaptcha();
										
		return true;

	} // setup_form
	
	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since	0.1
	 */
	public function __get( $key ) {
		
		if( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'kbs-form-invalid-property', sprintf( __( "Can't get property %s", 'kb-support' ), $key ) );
		}
	} // __get
		
	/**
	 * Retrieve the ID
	 *
	 * @since	0.1
	 * @return	int
	 */
	public function get_ID() {
		return $this->ID;
	} // get_ID
	
	/**
	 * Retrieve the form shortcode.
	 *
	 * @since	0.1
	 * @return	str
	 */
	public function get_shortcode() {
		
		$shortcode = '[kbs_form id="' . $this->ID . '"]';
		
		return $shortcode;
	} // get_shortcode
	
	/**
	 * Retrieve the form configuration.
	 *
	 * @since	0.1
	 * @return	arr
	 */
	public function get_form_config() {
		if( ! isset( $this->form_config ) )	{
			$this->form_config = get_post_meta( $this->ID, '_kbs_form_config', true );
		}
		
		return $this->form_config;
	} // get_form_config
	
	/**
	 * Retrieve the form fields.
	 *
	 * @since	0.1
	 * @return	obj
	 */
	public function get_fields() {
		
		if( ! isset( $this->fields ) )	{

			$args = array(
				'posts_per_page' => -1,
				'post_type'      => 'kbs_form',
				'post_parent'    => $this->ID,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC'
			);
			
			$args = apply_filters( 'kbs_get_form_fields', $args );
			
			$this->fields = get_posts( $args );

		}
		
		return $this->fields;
	} // get_fields
	
	/**
	 * Retrieves data for a field
	 *
	 * @since	1.3
	 * @param	str		$type	The type of key to search for.
	 * @param	str		$key	The data to retrieve.
	 * @return	str|arr	The values of the field key if it exists, or false
	 */
	public function get_field( $type, $key = 'name' )	{
		
		switch( $key )	{
			case 'name':
			default:
				$name = $this->get_field_name( $type );
				break;
		}
		
		return $name;
		
	} // get_field
	
	/**
	 * Retrieves the name of a field by its type.
	 *
	 * @since	1.3
	 * @param	str		$type	The type of field(s) to search for
	 * @param	str		$return	str or array. If str only the name of the first matched type is returned.
	 * @return	arr		The names of all fields that match the type.
	 */
	public function get_field_name( $type, $return = 'str' )	{
		
		if ( $return == 'array' )	{
			$name = array();
		}
		
		foreach ( $this->fields as $field )	{
			
			$data = $this->get_field_config( $field->ID );
			
			if ( empty ( $data ) )	{
				continue;
			}
			
			if ( $data['type'] == $type )	{
				if ( $return == 'array' )	{
					$name[] = $field->post_name;
				} else	{
					return $field->post_name;
				}
				
			}
		}
		
		if ( ! empty( $name ) )	{
			return $name;
		} else	{
			return false;
		}
		
	} // get_field_name
	
	/**
	 * Retrieves the type of a field by its name.
	 *
	 * @since	1.3
	 * @param	str		$name	The name of the field to search for
	 * @return	arr		The type of field.
	 */
	public function get_field_type( $name )	{
				
		foreach ( $this->fields as $field )	{
			
			if ( $field->post_name == $name )	{
				$data = $this->get_field_config( $field->ID );
			
				if ( empty ( $data ) )	{
					return false;
				}
				
				return $data['type'];
				
			}
			
		}

		return false;
		
	} // get_field_type
	
	/*
	 * Get the field config
	 *
	 * @param	int		$field_id		The post ID of the field
	 * @return	arr		$field_config	Array of field meta data
	 */
	public function get_field_config( $field_id )	{
		
		$field_config = get_post_meta( $field_id, '_mdjm_field_config', true );
		
		return $field_config;
		
	} // get_field_config
	
	/**
	 * Retrieve default settings for a form.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr		Default form settings.
	 */
	public function defaults()	{
		$defaults = array(
			'email_from'           => mdjm_get_option( 'system_email' ),
			'email_from_name'      => mdjm_get_option( 'company_name' ),
			'email_to'             => mdjm_get_option( 'system_email' ),
			'email_subject'        => sprintf( __( 'Enquiry via %s', 'mdjm-dynamic-contact-forms' ), esc_attr( $this->post_title ) ),
			'copy_sender'          => false,
			'create_enquiry'       => false,
			'send_template'        => false,
			'redirect'             => 'no_redirect',
			'display_message'      => false,
			'display_message_text' => '',
			'required_field_text'  => sprintf( __( '%s is a required field. Please try again.', 'mdjm-dynamic-contact-forms' ), '{FIELD_NAME}' ),
			'required_asterix'     => true,
			'error_text_color'     => '#FF0000',
			'css'                  => '',
			'layout'               => '0_column',
			'row_height'           => ''
		);
		
		return apply_filters( 'mdjm_dcf_form_defaults', $defaults );
	} // defaults
	
	/**
	 * Saves the form config.
	 *
	 * @since	1.3
	 * @param	arr		$data	$_POST data.
	 * @return	bool	True on successful save, otherwise false.
	 */
	public function save_config( $data )	{
		
		do_action( 'mdjm_pre_save_form_config', $data, $this->ID );
		
		$title = $data['form_name'];
		$name = sanitize_title( $data['form_name'] );
		
		unset( $data['form_name'] );
				
		$data = wp_parse_args( $data, $this->defaults() );
		
		if ( $title != $this->post_title )	{
			wp_update_post( array( 'ID' => $this->ID, 'post_title' => $title, 'post_name' => $name ) );
		}
		
		if ( update_post_meta( $this->ID, '_mdjm_contact_form_config', $data ) )	{
			return true;
		}
		
		do_action( 'mdjm_post_save_form_config', $data, $this->ID );
		
		return false;
		
	} // save_config
	
	/**
	 * Deletes a form field.
	 *
	 * @since	1.3
	 * @param	int		$field_id	The field post ID.
	 * @return	mixed	Field post object if successful, otherwise false.
	 */
	public function delete( $field_id )	{
		return wp_delete_post( $field_id, true );
	} // delete
	
	/**
	 * Retrieve required fields.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr		Array of field post objects.
	 */
	public function get_required_fields()	{
		
		$required = array();
		$ignore   = array( 'submit', 'section_head', 'rule' );
		
		foreach( $this->fields as $field )	{
			$settings = $this->get_field_config ( $field->ID );
			
			if ( empty ( $settings ) || empty( $settings['config']['required'] ) || in_array( $settings['type'], $ignore ) )	{
				continue;
			}
			
			$required[] = $field;
			
		}
		
		return $required;
		
	} // get_required_fields
	
	/**
	 * Whether or not the form has the specified field type.
	 *
	 * @since	0.1
	 * @param	str		$field_type	The field type to check for.
	 * @return	bool	True if the field type exists within the form, otherwise false.
	 */
	public function has_type( $field_type )	{
		
		foreach( $this->fields as $field )	{
			$settings = $this->get_field_config ( $field->ID );
			
			if ( empty ( $settings ) )	{
				continue;
			}
			
			if ( $settings['type'] == $field_type )	{
				if ( $field_type == 'addons_list' || $field_type == 'addons_check_list' )	{
					if ( ! empty( $settings['config']['display_price'] ) )	{
						$this->addons_cost = true;
					}
				}
				return true;
			}
		}
		
		return false;
		
	} // has_type
	
	/**
	 * Whether or not the form has a datepicker field.
	 *
	 * @since	0.1
	 * @param
	 * @return	void. Sets the $this->datepicker var
	 */
	public function has_datepicker()	{
		foreach( $this->fields as $field )	{
			$settings = $this->get_field_config ( $field->ID );
			
			if ( empty ( $settings ) )	{
				continue;
			}
			
			if ( $settings['type'] == 'date' && ! empty( $settings['config']['datepicker'] ) )	{
				$this->datepicker = true;
			}
		}
	} // has_datepicker
	
	/**
	 * Whether or not the form has a Google recaptcha field.
	 *
	 * @since	0.1
	 * @param
	 * @return	void. Sets the $this->reCapctha var
	 */
	public function has_recaptcha()	{
		$this->recaptcha = $this->has_type( 'recaptcha' );
	} // has_recaptcha
	
	/**
	 * Whether or not the form needs to dynamically update addons.
	 *
	 * @since	0.1
	 * @param
	 * @return	void. Sets the $this->dynamic_addons var
	 */
	public function has_dynamic_addons()	{
		if ( $this->has_type( 'package_list' ) && ( $this->has_type( 'addons_list' ) || $this->has_type( 'addons_check_list' ) ) )	{
			$this->dynamic_addons = true;
		}
	} // has_dynamic_addons
	
	/**
	 * Whether or not the field has the queried mapping.
	 *
	 * @since	0.1
	 * @param	arr		$settings	The settings for the given field.
	 * @param	str		$mapping	The mapping to check for.
	 * @return	bool	True if the field has the mapping, or false.
	 */
	public function has_mapping( $settings, $mapping )	{
		if ( empty( $settings['config']['mapping'] ) )	{
			return false;
		}
		
		if ( $settings['config']['mapping'] == $mapping )	{
			return true;
		}
	} // has_mapping
		
	/**
	 * Setup the validator rules and messages.
	 *
	 * @since	1.3
	 * @param
	 * @return
	 */
	public function validate_rules()	{
		$_rules    = '';
		$_messages = '';
		
		$fields = $this->get_required_fields();
		
		$this->validate = empty( $fields ) ? false : true;
		
		if ( empty( $this->validate ) )	{
			return;
		}
		
		$i = 1;
			
		foreach( $fields as $field )	{
			$settings  = $this->get_field_config( $field->ID );
			
			if ( $settings['type'] == 'captcha' )	{
				continue;
			}
			
			$comma     = $i < count( $fields ) ? ",\n" : "\n";
			$ignore    = ! empty( $this->recaptcha ) ? "\t\t" . 'ignore: ".ignore",' . "\n" : '';
			
			$_rules .= "\t" . '"' . $field->post_name . '": {' . "\n";
			
			if ( $settings['type'] == 'recaptcha' )	{
				$_rules .= "\t\trequired: function () {\n";
                $_rules .= "\t\t\tif (grecaptcha.getResponse() == '') {\n";
                $_rules .= "\t\t\t\treturn true;\n";
                $_rules .= "\t\t\t} else {\n";
                $_rules .= "\t\t\t\treturn false;\n";
                $_rules .= "\t\t\t}\n";
                $_rules .= "\t\t}\n";
			} else	{
				$_rules .= "\t\trequired: true";
			}
			
			$_messages .= "\t" . '"' . $field->post_name . '": {' . "\n";
			$_messages .= "\t\t" . 'required: " ' . str_replace( '{FIELD_NAME}', esc_attr( $field->post_title ), $this->form_config['required_field_text'] ) . '"' .  "\n";
			
			if ( $settings['type'] == 'email' || $settings['type'] == 'url' )	{
				$_rules    .= ",\n{$settings['type']}: true\n";
				
				$_messages .= ",\n" . sprintf( '%s: "%s"',
					$settings['type'],
					__( " Please enter a valid {$settings['type']}", 'mdjm-dynamic-contact-forms' )
				) . "\n";
			}
			
			if ( $settings['type'] == 'recaptcha' )	{
				$ignore = "\t\t" . 'ignore: ".ignore",' . "\n";
			}
			
			$_rules .= "\t}{$comma}";
			
			$_messages .= "\t}{$comma}";
			
			$i++;
		}
		
		if ( ! empty( $_rules ) )	{
			?>
			<script type="text/javascript">
			jQuery(document).ready( function($)	{
				$("#<?php echo $this->post_name; ?>").validate({
					<?php echo $ignore; ?>
					rules:	{
						<?php echo $_rules; ?>
					},
					messages:	{
						<?php echo $_messages; ?>
					},
					errorClass: "mdjm-form-error",
					validClass: "mdjm-form-valid",
					focusInvalid: false
				});
			});
			</script>
			<?php
		}
	} // validate_rules
		
	/**
	 * Enqueue scripts required for the form.
	 *
	 * @since	1.3
	 * @param
	 * @return
	 */
	public function enqueue_scripts()	{
		
		if ( ! empty ( $this->recaptcha ) )	{
			wp_register_script( 'mdjm-google-recaptcha', '//www.google.com/recaptcha/api.js"', '', MDJM_DCF_VERSION, true );
			wp_enqueue_script( 'mdjm-google-recaptcha' );
		}
		
		wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css', '', MDJM_DCF_VERSION, true );
		wp_enqueue_style( 'font-awesome' );
		
		$addons_list       = $this->get_field( 'addons_list' );
		$addons_check_list = $this->get_field( 'addons_check_list' );
		$addons_field      = ! empty( $addons_list ) ? $addons_list : $addons_check_list;
		
		if ( empty( $this->widget ) )	{

			wp_register_script( 'mdjm-dcf-scripts', MDJM_DCF_URL . '/assets/js/mdjm-contact-form.js', '', MDJM_DCF_VERSION, true );
			wp_enqueue_script( 'mdjm-dcf-scripts' );

			wp_localize_script(
				'mdjm-dcf-scripts',
				'mdjm_dcf',
				apply_filters(
					'mdjm_dcf_script_vars',
					array(
						'ajaxurl'         => mdjm_get_ajax_url(),
						'datepicker'      => ! empty ( $this->datepicker )     ? true                                : false,
						'date_format'     => ! empty ( $this->datepicker )     ? mdjm_format_datepicker_date()       : false,
						'first_day'       => ! empty ( $this->datepicker )     ? get_option( 'start_of_week' )       : false,
						'dynamic_addons'  => ! empty ( $this->dynamic_addons ) ? true                                : false,
						'packages_field'  => ! empty ( $this->dynamic_addons ) ? $this->get_field( 'package_list' )  : false,
						'addons_type'     => $this->has_type( 'addons_list' )  ? 'dropdown'                          : 'checkboxes',
						'addons_field'    => $addons_field,
						'addons_cost'     => $this->addons_cost,
						'form_name'       => $this->post_name
					)
				)
			);
			
		} else	{
			wp_register_script( 'mdjm-dcf-widget-scripts', MDJM_DCF_URL . '/assets/js/mdjm-contact-form-widget.js', '', MDJM_DCF_VERSION, true );
			wp_enqueue_script( 'mdjm-dcf-widget-scripts' );
			
			wp_localize_script(
				'mdjm-dcf-widget-scripts',
				'mdjm_dcf_widget',
				apply_filters(
					'mdjm_dcf_widget_script_vars',
					array(
						'ajaxurl'         => mdjm_get_ajax_url(),
						'datepicker'      => ! empty ( $this->datepicker )     ? true                                    : false,
						'date_format'     => ! empty ( $this->datepicker )     ? mdjm_format_datepicker_date()           : false,
						'first_day'       => ! empty ( $this->datepicker )     ? get_option( 'start_of_week' )           : false,
						'dynamic_addons'  => ! empty ( $this->dynamic_addons ) ? true                                    : false,
						'packages_field'  => ! empty ( $this->dynamic_addons ) ? $this->get_field( 'package_list' )      : false,
						'addons_type'     => $this->has_type( 'addons_list' )  ? 'dropdown'                              : 'checkboxes',
						'addons_field'    => $addons_field,
						'addons_cost'     => $this->addons_cost,
						'form_name'       => $this->post_name
					)
				)
			);
		}
		
	} // enqueue_scripts
			
	/**
	 * Prepare the form.
	 *
	 * @since	0.1
	 * @param
	 * @return
	 */
	public function prepare_form()	{
		$this->enqueue_scripts();
	} // prepare_form
	
	/**
	 * Determine the template to use.
	 *
	 * @since	0.1
	 * @param
	 * @return
	 */
	public function get_template()	{
		
		switch ( $this->layout )	{
			case 0:
			default:
				$template = 'default';
				break;
				
			case 2:
				$template = '2column';
				break;
				
			case 4:
				$template = '4column';
				break;
		}
		
		return apply_filters( 'mdjm_dcf_template', $template, $this );
		
	} // get_template
	
	/**
	 * Display the contact form.
	 *
	 * @since	0.1
	 * @param
	 * @return
	 */
	public function display_form()	{
		global $layout;
		
		if ( ! empty( $this->layout ) )	{
			$layout = $this->layout;
		}
		
		ob_start();
		
		mdjm_get_template_part( 'contact-form', 'header', true );
		echo '<input type="hidden" name="mdjm_dcf_contact_form' . $this->widget . '" id="mdjm_dcf_contact_form' . $this->widget . '" value="' . $this->ID . '" />';
		wp_nonce_field( 'submit_dcf_contact_form' . $this->widget, 'mdjm_nonce', true, true );
		mdjm_action_field( 'submit_dcf_contact_form' . $this->widget );
		
		if ( $this->layout == '4' )	{
			$i = 0;
			
			$search = array();
			$replace = array();
			
			foreach ( $this->fields as $field )	{
							
				ob_start();
				
				$num = $i == 0 ? 'one' : 'two';
				
				$settings = $this->get_field_config( $field->ID );
				
				$ignore = array( 'rule', 'section_head' );
				
				if ( in_array( $settings['type'], $ignore ) )	{
					continue;
				}
				
				$func     = $settings['type'] . '_field_callback';
				array_push( $search, "{field_name_{$num}}", "{label_{$num}}", "{field_{$num}}" );
				array_push(
					$replace,
					$field->post_name,
					$this->label_callback( $field, $settings ),
					method_exists( $this, $func ) ? $this->$func( $field, $settings ) : __( 'Callback does not exist for field', 'mdjm-dynamic-contact-forms' )
				);
				
				$i++;
				
				if ( $i == 2 )	{
					mdjm_get_template_part( 'contact-form', $this->get_template(), true );
				
					$content = ob_get_clean();
					$content = str_replace( $search, $replace, $content );
					
					echo $content;
					
					$i = 0;
					$search  = array();
					$replace = array();
				}
			}
						
		} else	{
		
			foreach ( $this->fields as $field )	{
				ob_start();
				
				$settings = $this->get_field_config( $field->ID );
				
				if ( $settings['type'] == 'captcha' )	{
					continue;
				}
				
				mdjm_get_template_part( 'contact-form', $this->get_template(), true );
				
				$content = ob_get_clean();
				
				$func = $settings['type'] . '_field_callback';
				
				$search  = array( '{field_name}', '{label}', '{field}' );
				$replace = array(
					$field->post_name,
					$this->label_callback( $field, $settings ),
					method_exists( $this, $func ) ? $this->$func( $field, $settings ) : __( 'Callback does not exist for field', 'mdjm-dynamic-contact-forms' )
				);
				
				$content = str_replace( $search, $replace, $content );
				$content = mdjm_do_content_tags( $content );
				
				echo $content;
			}
			
		}
		
		mdjm_get_template_part( 'contact-form', 'footer', true );
		
		$body    = ob_get_clean();
		$search  = array( '{form_id}', '{form_name}', '{form_url}' );
		$replace = array( $this->ID, $this->post_name . $this->widget, remove_query_arg( 'mdjm_message' ) );
		$form    = str_replace( $search, $replace, $body );	
		$form    = mdjm_do_content_tags( $form );
		
		echo $form;
		
	} // display_form
	
	/**
	 * Retrieve the default client fields.
	 *
	 * @since	1.3
	 * @param
	 * @return	arr
	 */
	public function get_default_client_fields()	{
		$fields = mdjm_get_client_fields();
		
		$defaults = array();
		
		foreach ( $fields as $field )	{
			if ( ! empty( $field['default'] ) )	{
				$defaults[] = $field['id'];
			}
		}
		
		return $defaults;
		
	} // get_default_client_fields
	
	/**
	 * Label callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function label_callback( $field, $settings )	{
		
		$output   = '';
		$required = '';
		$class    = ! empty ( $settings['config']['label_class'] ) ? ' ' . $settings['config']['label_class'] : '';
		
		if ( $settings['type'] == 'rule' )	{
			$output .=  sprintf( '<hr id="%1$s" class="%2$s" />', $field->post_name, $class );
		}
		elseif ( $settings['type'] == 'section_head' )	{
			if ( empty( $settings['config']['display_label'] ) )	{
				return '';
			}
			
			$wrap     = $settings['config']['section_wrap'];
			if ( ! empty( $settings['config']['font_size'] ) )	{
				$styles[] = 'font-size: ' . $settings['config']['font_size'] . 'px;';
			}
			
			if ( ! empty( $settings['config']['font_weight'] ) )	{
				$styles[] = 'font-weight: ' . $settings['config']['font_weight'] . ';';
			}
			
			if ( ! empty( $settings['config']['font_color'] ) )	{
				'color: ' . $settings['config']['font_colour'] . ';';
			}
			
			if ( ! empty( $settings['config']['font_align'] ) )	{
				'text-align: ' . $settings['config']['font_align'] . ';';
			}
			
			$output .= sprintf( '<%1$s%2$s class="%3$s">%4$s</%1$s>',
				$wrap,
				! empty( $styles ) ? ' style="' . implode( ' ', $styles ) . '"' : '',
				$class,
				esc_attr( $field->post_title )
			);
			
		} else	{
		
			if ( empty( $settings['config']['hide_label'] ) && $settings['type'] != 'submit' )	{
				
				if ( ! empty( $this->form_config['required_asterix'] ) && ! empty( $settings['config']['required'] ) )	{
					$required = ' <span class="fa fa-asterisk fa-lg mdjm_dcf_form_error" aria-hidden="true" style="font-size: xx-small; vertical-align: text-top;"></span>';
				}
				
				$output .= sprintf( '<label for="%1$s" id="mdjm-dcf-label-%1$s" class="mdjm-dcf-label%2$s">%3$s</label>%4$s',
					$field->post_name,
					$class,
					esc_attr( $field->post_title ),
					$required
				);
				
				if ( $this->layout == 0 )	{
					$output .= '<br />';
				}
			}
			
		}
		
		return apply_filters( 'mdjm_dcf_label_callback', $output, $field, $settings );
		
	} // label_callback
	
	/**
	 * Text field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function text_field_callback( $field, $settings )	{
		$required = '';
		$value    = '';
		
		$class  = ! empty ( $settings['config']['input_class'] ) ? ' ' . $settings['config']['input_class']             : '';
		$size   = ! empty ( $settings['config']['width'] )       ? ' size="' . $settings['config']['width'] . '"'       : '';
		$placeholder = ! empty ( $settings['config']['placeholder'] ) ? ' placeholder="' . esc_attr( $settings['config']['placeholder'] ) . '"' : '';
		
		if ( ! empty ($this->form_config['required_asterix'] ) && ! empty( $settings['config']['required'] ) )	{
			$required = ' required';
		}
		
		if ( is_user_logged_in() )	{
			if ( ! empty( $settings['config']['mapping'] ) && in_array( $settings['config']['mapping'], $this->get_default_client_fields() ) )	{
				$value = get_user_meta( get_current_user_id(), $settings['config']['mapping'], true );
			}
		}
		
		$output = sprintf( '<input type="%1$s" name="%2$s" id="%2$s" class="%3$s" value="%4$s"%5$s%6$s%7$s />',
			$settings['type'],
			$field->post_name,
			$class,
			$value,
			$size,
			$placeholder,
			$required
		);
				
		return apply_filters( "mdjm_dcf_{$settings['type']}_field_callback", $output, $field, $settings );
	} // text_field_callback
	
	/**
	 * Email field callback.
	 *
	 * Uses text field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function email_field_callback( $field, $settings )	{
		return $this->text_field_callback( $field, $settings );
	} // email_field_callback
	
	/**
	 * Telephone field callback.
	 *
	 * Uses text field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function tel_field_callback( $field, $settings )	{
		return $this->text_field_callback( $field, $settings );
	} // tel_field_callback
	
	/**
	 * URL field callback.
	 *
	 * Uses text field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function url_field_callback( $field, $settings )	{
		return $this->text_field_callback( $field, $settings );
	} // url_field_callback
	
	/**
	 * Date field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function date_field_callback( $field, $settings )	{
		$required = '';
		$value    = '';
		$readonly = '';
		$output   = '';
		
		$placeholder    = ! empty ( $settings['config']['placeholder'] ) ? ' placeholder="' . esc_attr( $settings['config']['placeholder'] ) . '"' : '';
		$has_datepicker = ! empty( $settings['config']['datepicker'] ) ? true : false;
		
		$class  = $has_datepicker ? 'mdjm_date' . $this->widget : '';
		$class  .= ! empty ( $settings['config']['input_class'] ) ? ' ' . $settings['config']['input_class']             : '';
		$size   = ! empty ( $settings['config']['width'] )       ? ' size="' . $settings['config']['width'] . '"'       : '';
		
		if ( ! empty ($this->form_config['required_asterix'] ) && ! empty( $settings['config']['required'] ) )	{
			$required = ' required';
		}
		
		if ( $has_datepicker )	{
			$hidden_value = '';
			$readonly = ' readonly="readonly"';
			
			if ( isset( $_GET['mdjm_avail_date'] ) && $this->has_mapping( $settings, '_mdjm_event_date' ) )	{

				$value = mdjm_format_short_date( $_GET['mdjm_avail_date'] );

				$hidden_value = $_GET['mdjm_avail_date'];

			}
			
			$output .= '<input type="hidden" name="_mdjm_event_date' . $this->widget . '" id="_mdjm_event_date' . $this->widget . '" value="' . $hidden_value . '" />';
			
		}
				
		$output .= sprintf( '<input type="text" name="%1$s" id="%1$s" class="%2$s" value="%3$s"%4$s%5$s%6$s%7$s />',
			$field->post_name . $this->widget,
			$class,
			$value,
			$size,
			$placeholder,
			$readonly,
			$required
		);
		
		return apply_filters( 'mdjm_dcf_date_field_callback', $output, $field, $settings );
	} // date_field_callback
	
	/**
	 * Time field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function time_field_callback( $field, $settings )	{
		$required = '';
		$class    = ! empty( $field_settings['config']['input_class'] ) ? $field_settings['config']['input_class'] : '';
		$start    = 'H:i' == mdjm_get_option( 'time_format' ) ? '00' : '1';
		$end      = 'H:i' == mdjm_get_option( 'time_format' ) ? '23' : '12';
		$minutes  = array( '00', '15', '30', '45' );
		
		// Hours field
		$output   = '<select name="' . $field->post_name . '" id="' . $field->post_name . '" class="' . $class .'">';
		
		while( $start <= $end )	{
			$output .= '<option value="' . $start . '">' . $start . '</option>' . "\r\n";
			$start++;
		}
		
		$output .= '</select>';
		
		$output .= '&nbsp;';
		
		// Minutes field
		$output .= '<select name="' . $field->post_name . '_min" id="' . $field->post_name . '_min" class="' . $class .'">';
		
		foreach ( $minutes as $minute )	{
			$output .= '<option value="' . $minute . '">' . $minute . '</option>' . "\r\n";
		}
		
		$output .= '</select>';
		
		// Period field
		if ( 'H:i' != mdjm_get_option( 'time_format' ) )	{
			$output .= '&nbsp;';
			$output .= '<select name="' . $field->post_name . '_period" id="' . $field->post_name . '_period" class="' . $class .'">';
			$output .= '<option value="AM">AM</option>' . "\r\n";
			$output .= '<option value="PM">PM</option>' . "\r\n";
			$output .= '</select>';
		}
		
		return apply_filters( 'mdjm_dcf_time_field_callback', $output, $field, $settings );
	} // time_field_callback
	
	/**
	 * Select List field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function select_field_callback( $field, $settings, $multiple = false )	{
		
		$required = ! empty ( $settings['config']['required'] )    ? ' required'                        : false;
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		$multiple = ! empty( $multiple )                           ? ' multiple="multiple"'             : '';
		$array    = ! empty( $multiple )                           ? '[]'                               : '';
		
		$output = sprintf( '<select name="%1$s%2$s" id="%1$s" class="%3$s"%4$s%5$s>',
			$field->post_name,
			$array,
			$class,
			$multiple,
			$required
		);
		
		$options = explode( "\r\n", $settings['config']['options'] );
		
		if ( empty( $options ) || ! is_array( $options ) )	{
			$output .= '<option value="">' . apply_filters( 'mdjm_dcf_no_options', __( 'No options available', 'mobile-dj-manager' ) ) . '</option>';
		} else	{
			foreach ( $options as $option )	{
				$output .= sprintf( '<option value="%s">%s</option>', $option, esc_attr( $option ) );
			}
		}
		
		$output .= '</select>';	
		
		return apply_filters( 'mdjm_dcf_select_field_callback', $output, $field, $settings );
	} // select_field_callback
	
	/**
	 * Multiple Select List field callback.
	 *
	 * Uses select_field_callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function select_multi_field_callback( $field, $settings )	{
		$output = $this->select_field_callback( $field, $settings, true );
		
		return apply_filters( 'mdjm_dcf_select_multi_field_callback', $output, $field, $settings, true );
	} // select_multi_field_callback
	
	/**
	 * Event List field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function event_list_field_callback( $field, $settings )	{
		$required = ! empty ( $settings['config']['required'] ) ? true                               : false;
		$class = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		
		$first = ! empty( $settings['config']['event_list_first_entry'] ) ? esc_attr( $settings['config']['event_list_first_entry'] ) : '';
		
		$args = array(
			'show_option_none'   => $first,
			'option_none_value'  => '',
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'selected'           => mdjm_get_option( 'event_type_default', false ),
			'name'               => $field->post_name,
			'id'                 => $field->post_name,
			'echo'               => false,
			'class'              => $class,
			'required'           => $required
		);
		
		return apply_filters( 'mdjm_dcf_event_list_field_callback', mdjm_event_types_dropdown( $args ), $field, $settings );
	} // event_list_field_callback
	
	/**
	 * Package List field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function package_list_field_callback( $field, $settings )	{
		if ( ! mdjm_get_option( 'enable_packages' ) )	{
			return __( 'Packages are not enabled', 'mdjm-dynamic-contact-forms' );
		}
		
		$required = ! empty ( $settings['config']['required'] )    ? true : false;
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		$selected = ! empty ( $settings['config']['package_list_selected'] ) ? 	$settings['config']['package_list_selected'] : '';	
		$first    = ! empty ( $settings['config']['package_list_first_entry'] ) ? esc_attr( $settings['config']['package_list_first_entry'] ) : '';
		
		if ( ! empty( $selected ) )	{
			$this->selected_package = $selected;
		}
				
		$args = array(
			'first_entry'        => $first,
			'option_none_value'  => '',
			'orderby'            => 'name', 
			'order'              => 'ASC',
			'selected'           => $selected,
			'name'               => $field->post_name,
			'id'                 => $field->post_name,
			'title'              => true,
			'class'              => $class,
			'required'           => $required,
			'cost'               => ! empty ( $settings['config']['display_price'] ) ? true : false
		);
		
		return apply_filters( 'mdjm_dcf_package_list_field_callback', mdjm_package_dropdown( $args ), $field, $settings );
	} // package_list_field_callback
	
	/**
	 * Addons List field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function addons_list_field_callback( $field, $settings )	{
		if ( ! mdjm_get_option( 'enable_packages' ) )	{
			return __( 'Packages are not enabled', 'mdjm-dynamic-contact-forms' );
		}
		
		$required = ! empty ( $settings['config']['required'] )    ? ' required'                        : '';
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
				
		$args = array(
			'name'               => $field->post_name,
			'id'                 => $field->post_name,
			'title'              => true,
			'class'              => $class . $required,
			'package'            => ! empty( $this->selected_package ) ? $this->selected_package : '',
			'cost'               => ! empty ( $settings['config']['display_price'] ) ? true : false
		);
		
		return apply_filters( 'mdjm_dcf_addons_list_field_callback', mdjm_addons_dropdown( $args ), $field, $settings );
	} // addons_list_field_callback
	
	/**
	 * Addons Check List field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function addons_check_list_field_callback( $field, $settings )	{
		if ( ! mdjm_get_option( 'enable_packages' ) )	{
			return __( 'Packages are not enabled', 'mdjm-dynamic-contact-forms' );
		}
		
		$required = ! empty ( $settings['config']['required'] )    ? ' required'                        : '';
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
				
		$args = array(
			'name'               => $field->post_name,
			'id'                 => $field->post_name,
			'title'              => true,
			'class'              => $class . $required,
			'package'            => ! empty( $this->selected_package ) ? $this->selected_package : '',
			'cost'               => ! empty ( $settings['config']['display_price'] ) ? true : false
		);
		
		$output  = '<span id="' . $field->post_name . '">';
		$output .= mdjm_addons_checkboxes( $args );
		$output .= '</span>';
		
		return apply_filters( 'mdjm_dcf_addons_check_list_field_callback', $output, $field, $settings );
	} // addons_check_list_field_callback
	
	/**
	 * Venue List field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function venue_list_field_callback( $field, $settings )	{
		
		$required = ! empty ( $settings['config']['required'] )    ? true                               : false;
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
			
		$args = array(
			'name'           => $field->post_name,
			'class'          => $class,
			'first_entry'    => ! empty( $settings['config']['venue_list_first_entry'] ) ? $settings['config']['venue_list_first_entry'] : '',
			'required'       => $required,
			'echo'           => false
		);
		
		return apply_filters( 'mdjm_dcf_venue_list_field_callback', mdjm_venue_dropdown( $args ), $field, $settings );
	} // venue_list_field_callback
	
	/**
	 * Checkbox field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function checkbox_field_callback( $field, $settings )	{
		
		$checked  = ! empty ( $settings['config']['is_checked'] )  ? ' checked="checked"'               : '';
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		$value    = $settings['config']['checked_value'];

		$output = sprintf( '<input type="checkbox" name="%1$s" id="%1$s" class="%2$s" value="%2$s" />', $field->post_name, $class, $value );

		return apply_filters( 'mdjm_dcf_checkbox_field_callback', $output, $field, $settings );
	} // checkbox_field_callback
	
	/**
	 * Checkbox list field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function checkbox_list_field_callback( $field, $settings )	{
		
		$output   = '';
		$options  = explode( "\r\n", $settings['config']['options'] );
		$class    = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		$value    = $settings['config']['checked_value'];
		
		if ( empty( $options ) || ! is_array( $options ) )	{
			$output .= apply_filters( 'mdjm_dcf_no_options', __( 'No options available', 'mobile-dj-manager' ) );
		} else	{
			$i = 0;
			foreach ( $options as $option )	{
				$output .= sprintf( '<input type="checkbox" name="%1$s[]" id="%1$s" class="%2$s" value="%3$s" />%4$s%5$s', 
					$field->post_name,
					$class,
					$option,
					esc_attr( $option ),
					$i < count( $options ) ? '<br />' : ''
				);
			}
		}

		return apply_filters( 'mdjm_dcf_checkbox_field_callback', $output, $field, $settings );
	} // checkbox_list_field_callback
	
	/**
	 * Textarea field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function textarea_field_callback( $field, $settings )	{
		
		$placeholder = ! empty ( $settings['config']['placeholder'] ) ? $settings['config']['placeholder'] : '';
		$class       = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		$width       = ! empty ( $settings['config']['width'] )       ? $settings['config']['width']       : '';
		$height      = ! empty ( $settings['config']['height'] )      ? $settings['config']['height']      : '';
		$required    = ! empty ( $settings['config']['required'] )    ? ' required'                        : '';

		$output = sprintf( '<textarea name="%1$s" id="%1$s" class="%2$s" cols="%3$s" rows="%4$s" placeholder="%5$s"%6$s></textarea>',
			$field->post_name,
			$class,
			$width,
			$height,
			esc_attr( $placeholder ),
			$required
		);

		return apply_filters( 'mdjm_dcf_textarea_field_callback', $output, $field, $settings );
	} // textarea_field_callback
		
	/**
	 * recaptcha field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function recaptcha_field_callback( $field, $settings )	{
		
		$site_key = mdjm_get_option( 'dcf_recaptcha_site_key' );
		
		if ( empty ( $site_key ) )	{
			$output = apply_filters( 'mdjm_dcf_no_site_key', __( 'No site key is configured for recaptcha', 'mdjm-dynamic-contact-forms' ) );
		} else	{
			$output  = sprintf( '<div class="g-recaptcha" data-sitekey="%s"></div>', $site_key );
			$output .= sprintf( '<input type="hidden" name="%1$s%2$s" id="%1$s%2$s" value="" required />', $field->post_name, $this->widget );
		}

		return apply_filters( 'mdjm_dcf_recaptcha_field_callback', $output, $field, $settings );
	} // recaptcha_field_callback
	
	/**
	 * Section head field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function section_head_field_callback( $field, $settings )	{
		return '';
	} // section_head_field_callback
	
	/**
	 * Horizontal rule field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function rule_field_callback( $field, $settings )	{
		return '';
	} // rule_field_callback
	
	/**
	 * Submit field callback.
	 *
	 * @since	0.1
	 * @param	obj		$field		The field WP_Post object.
	 * @param	arr		$settings	The configuration for the field
	 * @return	str
	 */
	public function submit_field_callback( $field, $settings )	{

		$class  = ! empty ( $settings['config']['input_class'] ) ? $settings['config']['input_class'] : '';
		$output =  sprintf( '<input type="submit" name="%1$s" id="%1$s" value="%2$s" class="%3$s" />', $field->post_name, esc_attr( $field->post_title ), $class );

		return apply_filters( 'mdjm_dcf_submit_field_callback', $output, $field, $settings );
	} // submit_field_callback
	
	/**
	 * Process form submission.
	 *
	 * Query form settings and create client and/or enquiry before emailing.
	 *
	 * @since	1.3
	 * @param	arr		$data	$_POST data
	 * @return	bool	True if the submission was successful, otherwise false.
	 * @note	True is defined by a successful email being sent to admin with form data.
	 *			It does not neceassrily mean that any other form actions (create event/client)
	 *			have been completed.
	 *			If expected actions are not all completed, enabled MDJM debugging and check the 
	 *			debug log file for troubleshooting info.
	 */
	public function submit( $data )	{
		
		MDJM()->debug->log_it( sprintf( 'MDJM DCF submission for form ID %s.', $this->ID ), true );
		
		$return = false;
		
		if ( ! empty( $this->create_enquiry ) )	{
			$data['client_id'] = $this->create_client( $data );
			$event_id = $this->create_event( $data );
		}
		
		$return = $this->notices( $data, $event_id );
		
		return $return;
				
	} // submit
	
	/**
	 * Checks whether a client needs to be created and creates.
	 *
	 * If the user submitting the form is logged in, just return their ID.
	 *
	 * @since	1.3
	 * @param	arr		$data	Form submission data.
	 * @return	int|bool	Client user ID or false if creation fails.
	 */
	public function create_client( $data )	{
		
		global $current_user;
		
		if ( is_user_logged_in() )	{
			MDJM()->debug->log_it( "User is logged in, returning ID {$current_user->ID}" );
			return $current_user->ID;
		}
		
		$client_fields = mdjm_get_client_fields();	
		
		if ( empty( $client_fields ) )	{
			return false;
		}
		
		foreach( $client_fields as $client_field )	{
			
			foreach ( $this->fields as $field )	{
				if ( $this->has_mapping( $this->get_field_config( $field->ID ), $client_field['id'] ) )	{
					
					if ( $client_field['id'] == 'user_email' )	{

						$client_email = $data[ $field->post_name ];
						$client_data[ $client_field['id'] ] = $client_email;

					} elseif( $client_field['id'] == 'first_name' || $client_field['id'] == 'last_name' )	{
						$client_data[ $client_field['id'] ] = ucfirst( $data[ $field->post_name ] );
					} else	{
						$client_meta[ $client_field['id'] ] = strip_tags( addslashes( $data[ $field->post_name ] ) );
					}
					
				}
			}
			
		}
		
		if ( ! empty( $client_email ) )	{
			
			$exists = username_exists( $client_email );
			
			if ( ! empty( $exists ) )	{
				MDJM()->debug->log_it( sprintf( 'User %s already exists, returning ID %d', $client_email, $exists ) );
				return $exists;
			}
			
			$password = wp_generate_password(
				mdjm_get_option( 'pass_length', 8 ),
				! mdjm_get_option( 'complex_passwords', false ) ? false : true
			);
			
			$client_id = wp_create_user( $client_email, $password, $client_email );
			
			if ( is_wp_error( $client_id ) )	{
				MDJM()->debug->log_it( 'Error creating user: ' . $client_id->get_error_message() );
				return false;
			} else	{
				
				MDJM()->debug->log_it( "New client added with ID {$client_id}" );
				
				if ( ! empty( $client_data ) )	{
					
					$client_data['ID']                   = $client_id;
					$client_data['role']                 = 'client';
					$client_data['show_admin_bar_front'] = 'false';
					$client_data['display_name']         = '';
					
					foreach( $client_data as $key => $value )	{
						if ( $key == 'first_name' )	{
							$client_data['display_name'] .= $value;
						}
						if ( $key == 'last_name' )	{
							$client_data['display_name'] .= ! empty( $client_data['display_name'] ) ? " {$value}" : $value;
						}
					}
					
					wp_update_user( $client_data );
					
				}
				
				if ( ! empty( $client_meta ) )	{
					foreach( $client_meta as $key => $value )	{
						update_user_meta( $client_id, $key, $value );
					}
				}
				
				return $client_id;

			}
			
		} else	{
			return false;
		}
		
	} // create_client

	/**
	 * Creates the unattended event.
	 *
	 * @since	1.3
	 * @param	arr			$data	Form submission data.
	 * @return	obj|bool	MDJM_Event object on success, otherwise false.
	 */
	public function create_event( $data )	{
		
		if ( empty( $data['client_id'] ) )	{
			MDJM()->debug->log_it( 'Skipping event creation. No client associated' );
			
			return false;
		}
				
		MDJM()->debug->log_it( 'Creating Unattended Enquiry from Contact Form Submission' );
		
		$mappings = mdjm_dcf_field_mappings();
		
		$meta['_mdjm_event_client'] = $data['client_id'];
		$meta['_mdjm_event_cost']   = 0;
		
		foreach( $this->fields as $field )	{

			$settings = $this->get_field_config( $field->ID );

			if ( empty( $settings ) )	{
				continue;
			}

			if ( ! empty( $settings['config']['mapping'] ) && ! empty( $data[ $field->post_name ] ) )	{
				
				if ( $settings['type'] == 'date' && $this->has_mapping( $settings, '_mdjm_event_date' ) )	{
					$meta[ $settings['config']['mapping'] ] = $data['_mdjm_event_date'];
				} elseif( $settings['type'] == 'time' )	{
					if ( 'H:i' == mdjm_get_option( 'time_format' ) )	{
						$meta[ $settings['config']['mapping'] ] = date( 'H:i:s', strtotime( $data[ $field->post_name ] .
							':' . $data[ $field->post_name . '_min' ] ) );
					} else	{
						$meta[ $settings['config']['mapping'] ] = date( 'H:i:s', strtotime( $data[ $field->post_name ] .
							':' . $data[ $field->post_name . '_min' ] . $data[ $field->post_name . '_period' ] ) );
					}

				} else	{
					$meta[ $settings['config']['mapping'] ] = $data[ $field->post_name ];					
				}
			}

		}
						
		$args = array(
			'post_status'    => 'mdjm-unattended'
		);
		
		$mdjm_event = new MDJM_Event();
		
		if ( $mdjm_event->create( $args, $meta ) )	{
			MDJM()->debug->log_it( "Event ID: {$mdjm_event->ID} was successfully created." );
			
			$journal_args = array(
				'user_id'			=> 1,
				'event_id'		   => $mdjm_event->ID,
				'comment_content'	=> '',
				'comment_type'	   => 'mdjm-journal',
				'comment_date'	   => current_time( 'timestamp' ),
				'comment_content'    => sprintf( __( '%s created via Dynamic Contact Form submission.', 'mdjm-dynamic-contact-forms' ), mdjm_get_label_singular() )
			);
			
			 mdjm_add_journal( $args );
			
			return $mdjm_event->ID;
		}
		else	{
			return false;
		}
		
	} // create_event
	
	/**
	 * Send email notifications.
	 *
	 * @since	1.3
	 * @param	arr		$data		Form submission data.
	 * @param	int		$event_id	Event ID.
	 * @return	void
	 */
	public function notices( $data, $event_id = '' )	{
		
		$client_id = ! empty( $data['client_id'] ) ? $data['client_id'] : '';
		
		$message  = '<p>' . __( 'Hi there,', 'mdjm-dynamic-contact-forms' ) . '</p>';
		$message .= '<p>' . sprintf( __( 'Your %s enquiry has just been received via %s with the following details...', 'mdjm-dynamic-contact-forms' ), mdjm_get_label_singular(), mdjm_get_option( 'app_name' ) );
		
		foreach ( $this->fields as $field )	{
			$settings = $this->get_field_config( $field->ID );

			if ( empty( $settings ) || empty( $field->post_name ) )	{
				continue;
			}
			
			$ignore = array( 'rule', 'section_head', 'submit', 'recaptcha' );
			
			if ( in_array( $settings['type'], $ignore ) )	{
				continue;
			}
			
			if ( $settings['type'] == 'email' )	{
				$client_email = $data[ $field->post_name ];
			}
			
			$message .= "<p><strong>{$field->post_title}</strong><br />";
			if( $settings['type'] == 'time' )	{
				if ( 'H:i' == mdjm_get_option( 'time_format' ) )	{
					$message .= date( 'H:i:s', strtotime( $data[ $field->post_name ] .
						':' . $data[ $field->post_name . '_min' ] ) );
				} else	{
					$message .= date( 'H:i:s', strtotime( $data[ $field->post_name ] .
						':' . $data[ $field->post_name . '_min' ] . $data[ $field->post_name . '_period' ] ) );
				}

			} elseif( $settings['type'] == 'event_list' )	{
				$type = get_term( (int)$data[ $field->post_name ], 'event-types' );
				$message .= $type->name;
			} elseif( $settings['type'] == 'package_list' )	{
				$message .= get_package_name( $data[ $field->post_name ] );
			} elseif( $settings['type'] == 'addons_list' || $settings['type'] == 'addons_check_list' )	{
				foreach ( $data[ $field->post_name ] as $addon )	{
					$message .= get_addon_name( $addon ) . '<br />';
				}
			} else	{
				$message .= $data[ $field->post_name ];
			}
			$message .= '</p>';
		}
		
		$message .= '<p>' . __( 'Regards,', 'mdjm-dynamic-contact-forms' ) . '<br />';
		$message .= mdjm_get_option( 'company_name' ) . '</p>';
		
		$message = apply_filters( 'mdjm_dcf_admin_notice', $message );
		$message = mdjm_do_content_tags( $message, $event_id, $client_id );
		
		if ( empty( $client_email ) )	{
			MDJM()->debug->log_it( 'Cannot send notification email to client as no email address was identified.' );
		} elseif ( empty( $this->form_config['copy_sender'] ) )	{
			MDJM()->debug->log_it( 'Settings do not permit message to be sent to client.' );
		} elseif( ! empty( $this->form_config['send_template'] ) )	{
			$this->reply_with_template( $client_email, $this->form_config['send_template'], $event_id, $client_id );
		} else	{
			$args = array(
				'to_email'       => $client_email,
				'from_name'      => $this->form_config['email_from_name'],
				'from_email'     => $this->form_config['email_from'],
				'event_id'       => $event_id,
				'client_id'      => $client_id,
				'subject'        => $this->form_config['email_subject'],
				'attachments'    => array(),
				'message'        => $message,
				'track'          => false,
				'copy_to'        => 'disable',
				'source'         => __( 'MDJM Dynamic Contact Form submission', 'mdjm-dynamic-contact-forms' )
			);
			
			if ( mdjm_send_email_content( $args ) )	{
				MDJM()->debug->log_it( 'Client notice email sent.' );
				$emailed_client = true;
			}
		}
		$message .= sprintf( __( 'Login to the %s <a href="%s">%s to review all your enquiries.', 'mdjm-dynamic-contact-forms' ), mdjm_get_option( 'company_name' ), admin_url(), mdjm_get_option( 'app_name' ) ) . '</p>';
		$message .= '<p>' . sprintf( __( 'A copy of this email was %s to the client.', 'mdjm-dynamic-contact-forms' ), ! empty( $emailed_client ) ? 'sent' : 'not sent' ) . '</p>';
			
		$args = array(
			'to_email'       => $this->form_config['email_to'],
			'from_name'      => $this->form_config['email_from_name'],
			'from_email'     => $this->form_config['email_from'],
			'event_id'       => $event_id,
			'client_id'      => $client_id,
			'subject'        => $this->form_config['email_subject'],
			'attachments'    => array(),
			'message'        => $message,
			'track'          => false,
			'copy_to'        => 'disable',
			'source'         => __( 'MDJM Dynamic Contact Form submission', 'mdjm-dynamic-contact-forms' )
		);
		
		return mdjm_send_email_content( $args );
		
	} // notices
	
	/**
	 * Respond to client with a template.
	 *
	 * @since	1.3
	 * @param	str		$to				Email address of recipient.
	 * @param	int		$template_id	ID of the post template to send.
	 * @param	int		$event_id		The event ID.
	 * @param	int		$client_id		The client ID.
	 * @return	void
	 */
	function reply_with_template( $to, $template_id, $event_id = '', $client_id = '' )	{
					
		$from_name    = $this->form_config['email_from_name'];
		$from_name    = apply_filters( 'mdjm_dcf_email_from_name', $from_name );
	
		$from_email   = $this->form_config['email_from'];
		$from_email   = apply_filters( 'mdjm_dcf_email_from_address', $from_email );
	
		$to_email     = $to;
	
		$subject      = mdjm_email_set_subject( $template_id );
		$subject      = apply_filters( 'mdjm_dcf_subject', wp_strip_all_tags( $subject ) );
		$subject      = mdjm_do_content_tags( $subject, $event_id, $client_id );
	
		$attachments  = apply_filters( 'mdjm_dcf_attachments', array() );
		
		$message	  = mdjm_get_email_template_content( $template_id );
		$message      = mdjm_do_content_tags( $message, $event_id, $client_id );
	
		$emails = MDJM()->emails;
	
		$emails->__set( 'event_id', $event_id );
		$emails->__set( 'from_name', $from_name );
		$emails->__set( 'from_address', $from_email );
		
		$headers = apply_filters( 'mdjm_dcf_headers', $emails->get_headers(), $event_id, $client_id );
		$emails->__set( 'headers', $headers );
		
		$emails->__set( 'track', false );
			
		$emails->send( $to_email, $subject, $message, $attachments, __( 'Dynamic Contact Form Submission', 'mobile-dj-manager' ) );
		
	} // reply_with_template
	
	/**
	 * Defines the messages that are displayed when the form is submitted.
	 *
	 * @since	1.3
	 * @param	arr		$messages	MDJM Messages.
	 * @return	arr		Filtered MDJM Messages.
	 */
	function form_messages( $messages )	{
		
		if ( ! empty( $this->form_config['display_message'] ) )	{
			$success = mdjm_do_content_tags( $this->form_config['display_message_text'] );
			$success = nl2br( html_entity_decode( stripcslashes( $success ) ) );
		} else	{
			$success = __( "Thanks for getting in touch. We've received your message and will respond as quickly as possible.", 'mdjm-dynamic-contact-forms' );
		}
		
		$dcf_messages = array(
			'contact_form_error'	=> array(
				'class'		=> 'error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'There was an error submitting your form. Please try again.', 'mdjm-dynamic-contact-forms' )
			),
			'contact_form_success'	=> array(
					'class'		=> 'success',
					'title'		=> '',
					'message'	=> $success
			),
		);
		
		if ( ! empty( $this->widget ) )	{
			$dcf_messages['contact_form_error_widget'] = array(
				'class'		=> 'mdjm-dcf-widget-error',
				'title'		=> __( 'Error', 'mobile-dj-manager' ),
				'message'	=> __( 'There was an error submitting your form. Please try again.', 'mdjm-dynamic-contact-forms' )
			);
			$dcf_messages['contact_form_success'] = array(
					'class'		=> 'mdjm-dcf-widget-success',
					'title'		=> '',
					'message'	=> $success
			);
		}
		
		$dcf_messages = apply_filters( 'mdjm_dcf_messages', $dcf_messages );
		
		return array_merge( $messages, $dcf_messages );
	} // form_messages

} // MDJM_DCF_Form