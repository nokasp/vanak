<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mehrdaddindar.ir
 * @since             0.2.2
 * @package           Vanak
 *
 * @wordpress-plugin
 * Plugin Name:       Vanak
 * Plugin URI:        https://mehrdaddindar.ir/vanak
 * Description:       The 'Notify via Bale for WooCommerce - Vanak' plugin is an efficient tool within WordPress that enables website administrators to establish communication with the Balachat messaging bot. With the use of this plugin, you can effortlessly receive notifications, updates, and details related to your WooCommerce orders through the Balachat messaging platform. By leveraging this plugin, you'll be capable of enhancing your online business management and establishing improved communication with customers and orders.
 * Version:           0.2.2
 * Author:            Mehrdad Dindar
 * Author URI:        https://mehrdaddindar.ir
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vanak
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.2.2 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'VANAK_VERSION', '0.2.2' );
define("VANAK_URL", plugin_dir_url(__FILE__));
define("VANAK_PATH", plugin_dir_path(__FILE__));


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vanak-activator.php
 */
function activate_vanak() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vanak-activator.php';
	Vanak_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vanak-deactivator.php
 */
function deactivate_vanak() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vanak-deactivator.php';
	Vanak_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_vanak' );
register_deactivation_hook( __FILE__, 'deactivate_vanak' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-vanak.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.2.2
 */
function run_vanak() {

	$plugin = new Vanak();
	$plugin->run();

}
run_vanak();
