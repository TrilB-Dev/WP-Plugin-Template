<?php
/**
 * Define the activation functionality for the plugin.
 *
 * Handles the registration and execution of activation callbacks.
 *
 * @since 1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/Includes/Core/WP
 */
namespace PluginName\Includes\Core\WP;

use PluginName\Includes\Core\Capabilities;
use PluginName\Includes\Plugins\Plugins;
use PluginName\Includes\Settings\SettingsManager;
use PluginName\Includes\Settings\Settings;
use PluginName\Includes\Licence\LicenceRepository;
use PluginName\Includes\Licence\KeyManager;
use PluginName\Includes\Core\PostType;
use PluginName\Includes\Core\Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Activator {
	/**
	 * Registered activation callbacks.
	 *
	 * @var array<int, callable>
	 */
	private static array $callbacks = array();

	/**
	 * Register extension activation callbacks.
	 *
	 * @param callable $callback Callback invoked during activation.
	 * @return void
	 */
	public static function register( callable $callback ): void {
		self::$callbacks[] = $callback;
	}

	/**
	 * Run PluginName and extension activation tasks.
	 *
	 * @param array<int, callable>|null $callbacks Optional callbacks for this run.
	 * @return void
	 */
	public static function activate( ?array $callbacks = null ): void {
		Settings::register_group(
			'setup',
			array(
				'first_install_complete'    => false,
				'onboarding_steps_complete' => 0,
			)
		);

		Plugins::get_instance()->init();
		Capabilities::install();
		Database::install();
		SettingsManager::install();
		( new PostType() )->register();
		( new Taxonomy() )->register();

		foreach ( $callbacks ?? self::$callbacks as $callback ) {
			call_user_func( $callback );
		}

		flush_rewrite_rules();
	}
}



