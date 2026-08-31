<?php

/**
 * PluginName - A WordPress Plugin
 *
 * This is the main plugin file for the PluginName WordPress plugin. It contains the plugin metadata and initializes the plugin by including necessary files and setting up activation and deactivation hooks.
 *
 * Plugin Name:       PluginName
 * Plugin URI:        https://trilb.dev/collection/web-extension/wordpress/pluginname
 * Description:       PluginName is a WordPress plugin.
 * Author:            MrTrilB
 * Author URI:        https://trilb.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pluginname
 * Domain Path:       src/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGINNAME_VERSION', '0.4.2-Dev' );
define( 'PLUGINNAME_NAME', 'pluginname' );
define( 'PLUGINNAME_FILE', __FILE__ );
define( 'PLUGINNAME_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGINNAME_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGINNAME_BASENAME', plugin_basename( __FILE__ ) );
define( 'PLUGINNAME_ROOT', PLUGINNAME_DIR );
define( 'PLUGINNAME_ROOT_URL', PLUGINNAME_URL );
define( 'PLUGINNAME_API', PLUGINNAME_DIR . 'src/API' );
define( 'PLUGINNAME_ASSETS', PLUGINNAME_DIR . 'src/Assets' );
define( 'PLUGINNAME_ASSETS_URL', PLUGINNAME_URL . 'src/Assets' );
define( 'PLUGINNAME_ADMIN', PLUGINNAME_DIR . 'src/Admin' );
define( 'PLUGINNAME_ADMIN_URL', PLUGINNAME_URL . 'src/Admin' );
define( 'PLUGINNAME_LANGUAGES', PLUGINNAME_DIR . 'src/languages' );
define( 'PLUGINNAME_INCLUDES', PLUGINNAME_DIR . 'src/includes' );
define( 'PLUGINNAME_CORE', PLUGINNAME_INCLUDES . '/Core' );
define( 'PLUGINNAME_SETTINGS', PLUGINNAME_INCLUDES . '/Settings' );
define( 'PLUGINNAME_PLUGINS', PLUGINNAME_INCLUDES . '/Plugins' );
define( 'PLUGINNAME_PLUGINS_URL', PLUGINNAME_URL . 'src/includes/Plugins' );

$pluginname_autoloader = PLUGINNAME_DIR . 'vendor/autoload.php';
if ( is_readable( $pluginname_autoloader ) ) {
	require_once $pluginname_autoloader;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pluginname-activator.php
 */
function activate_pluginname() {
	\PluginName\Includes\Core\WP\Activator::activate();
}

register_activation_hook( __FILE__, 'activate_pluginname' );
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pluginname-deactivator.php
 */
function deactivate_pluginname() {
	\PluginName\Includes\Core\WP\Deactivator::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_pluginname' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once PLUGINNAME_DIR . 'src/Plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pluginname() {

	$plugin = new \PluginName\PluginName( PLUGINNAME_FILE, PLUGINNAME_NAME, PLUGINNAME_VERSION );
	$plugin->run();

}
run_pluginname();
