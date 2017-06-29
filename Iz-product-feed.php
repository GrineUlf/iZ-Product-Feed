<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.blaesbjerg.com
 * @since             1.0.0
 * @package           Iz Product Feed
 *
 * @wordpress-plugin
 * Plugin Name:       Iz Product Feed
 * Plugin URI:        http://example.com/plugin-name-uri/
 * Description:       Google Merchant Product Feed for Woocommerce
 * Version:           1.0.0
 * Author:            Mike Koopman
 * Author URI:        http://www.aleval.nl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_iz_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-iz-feed-activator.php';
	iz_feed_Activator::activate();
	iz_feed_Activator::iz_set_cron();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_iz_feed() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-iz-feed-deactivator.php';
	iz_feed_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_iz_feed' );
register_deactivation_hook( __FILE__, 'deactivate_iz_feed' );
register_activation_hook( __FILE__, 'my_activation_func' );

function my_activation_func() {
    file_put_contents( __DIR__ . '/my_loggg.txt', ob_get_contents() );
}
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-iz-feed.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_iz_product_feed() {

	$plugin = new iz_feed();
	$plugin->run();

}
run_iz_product_feed();
