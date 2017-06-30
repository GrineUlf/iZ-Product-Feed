<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/GrineUlf/iZ-Product-Feed/
 * @since      2.0.0
 *
 * @package    Iz-product-feed
 * @subpackage Iz-product-feed/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    iz-product-feed
 * @subpackage iz-product-feed/includes
 * @author     Mike Koopman <mike@blaesbjerg.com>
 */
class iz_feed_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'iz-feed',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
