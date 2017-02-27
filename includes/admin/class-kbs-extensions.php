<?php
/**
 * Extensions
 *
 * @package     KBS
 * @subpackage  Classes/Extensions
 * @copyright   Copyright (c) 2017, Mike Howard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

class KBS_Extensions	{

	public $extensions;

	/**
	 * Get things going.
	 */
	public function __construct()	{
		// Add the menu option for extensions
		add_action( 'admin_menu', array( &$this, 'options_link' ), 90 );

		// Daily scheduled task to retrieve add-ons
		add_action( 'kbs_twice_daily_scheduled_events', array( &$this, 'get_extensions' ) );
	} // __construct

	/**
	 * Adds the 'add-ons' menu option.
	 *
	 * @since	1.0.3
	 * @return	void
	 */
	public function options_link()	{
		global $kbs_extensions_page;

		$kbs_extensions_page = add_submenu_page(
			'edit.php?post_type=kbs_ticket',
			__( 'KB Support Extensions', 'kb-support' ), 
			__( 'Extensions', 'kb-support' ),
			'manage_ticket_settings',
			'kbs-extensions',
			array( &$this, 'extensions_page' )
		);
	} // options_link

	/**
	 * Display the add-ons page.
	 *
	 * @since	1.0.3
	 * @return	void
	 */
	public function extensions_page()	{
		setlocale( LC_MONETARY, get_locale() );
		$extensions_url = 'https://kb-support.com/extensions/';
		$extensions     = $this->get_extensions();
		$tags           = '<a><em><strong><blockquote><ul><ol><li><p>';
		$length         = 55;
		$more           = false;
		$item           = 1;
		$column         = 1;
		?>
        <div class="wrap">
            <h1>
                <?php _e( 'Extensions for KB Support', 'kb-support' ); ?>&nbsp;&nbsp;&nbsp;<a href="https://kb-support.com/extensions/" class="button-primary" target="_blank"><?php _e( 'Browse All Extensions', 'kb-support' ); ?></a>
            </h1>
            <div>
            	<p><?php _e( 'These extensions <em><strong>add even more functionality</strong></em> to your KB Support help desk.', 'kb-support' ); ?></p>
            </div>

			<div id="kbs-extension-container">
            	<?php foreach ( $extensions as $key => $extension ) :

					if ( $item == 5 )	{
						$item = 1;
					}

					if ( $item == 1 )	{
						$column = 1;
					} elseif( $item == 3 )	{
						$column = 2;
					}

					$link  = 'https://kb-support.com/downloads/' . $extension->info->slug .'/';
					$price = false;
    
                    if ( isset( $extension->pricing->amount ) ) {
                        $price = '&pound;' . number_format( $extension->pricing->amount, 2 );
                    } else {
                        if ( isset( $extension->pricing->singlesite ) ) {
                            $price = '&pound;' . number_format( $extension->pricing->singlesite, 2 );
                        }
                    }

					if ( ! empty( $extension->info->excerpt ) ) {
						$the_excerpt = $extension->info->excerpt;
					}

					$the_excerpt   = strip_shortcodes( strip_tags( stripslashes( $the_excerpt ), $tags ) );
					$the_excerpt   = preg_split( '/\b/', $the_excerpt, $length * 2+1 );
					$excerpt_waste = array_pop( $the_excerpt );
					$the_excerpt   = implode( $the_excerpt ); ?>

					<?php if ( $item == 1 ) : ?>
                        <div class="kbs-extension-col-wrapper">
                    <?php endif; ?>

						<?php if ( $item == 1 || $item == 3 ) : ?>
                            <div class="kbs-extension-col kbs-extension-col-<?php echo $item == 1 ? 1 : 2; ?>">
                        <?php endif; ?>

							<div class="kbs-extension-item">
                                <div class="kbs-extension-item-img">
                                    <a href="<?php echo $link; ?>" target="_blank"><img src="<?php echo $extension->info->thumbnail; ?>" /></a>
                                </div>
                                <div class="kbs-extension-item-desc">
                                    <p class="kbs-extension-item-heading"><?php echo $extension->info->title; ?></p>
                                    <p><?php echo $the_excerpt; ?></p>
                                    <div class="kbs-extension-buy-now">
                                        <a href="<?php echo $link; ?>" class="button-primary" target="_blank"><?php printf( __( 'Buy Now from %s', 'kb-support' ), $price ); ?></a>
                                    </div>
                                </div>
                            </div>

						<?php if ( $item == 2 || $item == 4 ) : // Close kbs-extension-col kbs-extension-col- ?>
                            </div>
                        <?php endif; ?>

					<?php if ( $item == 4 ) : // Close kbs-extension-col-wrapper ?>
                        </div>
                    <?php endif; ?>

					<?php $item++; ?>

                <?php endforeach; ?>
            </div>
        </div>
        <?php

	} // extensions_page

	/**
	 * Retrieve the extensions_page and store within transient.
	 *
	 * @since	1.0.3
	 * @return	void
	 */
	public function get_extensions()	{
		$extensions = get_transient( 'kbsupport_extensions_feed' );

		if ( false === $extensions || doing_action( 'kbs_twice_daily_scheduled_events' ) )	{
			$route    = esc_url( 'https://kb-support.com/edd-api/products/' );
			$number   = 20;
			$endpoint = add_query_arg( array( 'number' => $number ), $route );
			$response = wp_remote_get( $endpoint );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body    = wp_remote_retrieve_body( $response );
				$content = json_decode( $body );
		
				if ( is_object( $content ) && isset( $content->products ) ) {
					set_transient( 'kbsupport_extensions_feed', $content->products, DAY_IN_SECONDS / 2 ); // Store for 12 hours
					$extensions = $content->products;
				}
			}
		}

		return $extensions;
	} // get_extensions

} // KBS_Extensions

new KBS_Extensions;
