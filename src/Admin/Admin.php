<?php
/**
 * Admin class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin
 * @since 1.0.0
 */
namespace PluginName\Admin;

use PluginName\Includes\Settings\Settings;
use PluginName\Includes\Functions\Admin\FunctionsPlugins;
use PluginName\Includes\Functions\Admin\FunctionsSidebar;
use PluginName\Includes\Functions\Helpers\AjaxHelper;
use PluginName\Includes\Core\Capabilities;
use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Functions\Helpers\LoggerHelper;
use PluginName\Includes\Functions\Helpers\RequestHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Assets\Assets;
use PluginName\Admin\Manager\Manager;
use PluginName\Admin\Manager\Tools\ToolsManager;
use PluginName\Admin\Manager\Dashboard\DashboardManager;
use PluginName\Admin\Manager\Reports\ReportsManager;
use PluginName\Admin\Manager\Users\UserManager;
use PluginName\Admin\Manager\Settings\SettingsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin {
	/**
	 * The DashboardManager instance for managing the dashboard page.
	 *
	 * @var DashboardManager
	 * */
	private DashboardManager $dashboard_manager;
	/**
	 * SettingsManager instance for managing settings-related admin pages.
	 *
	 * @var SettingsManager
	 */
	private SettingsManager $settings_manager;
	/**
	 * ReportsManager instance for managing customer reports and account data.
	 *
	 * @var ReportsManager
	 */
	private ReportsManager $reports_manager;
	/**
	 * LicencesManager instance for managing licence-type and issued licence pages.
	 *
	 * @var UserManager
	 */
	private UserManager $user_manager;
	/**
	 * ToolsManager instance for managing tools-related admin pages.
	 *
	 * @var ToolsManager
	 */
	private ToolsManager $tools_manager;
	/**
	 * Registry of the admin managers.
	 *
	 * @var array<string, Manager>
	 */
	private array $managers;
	/**
	 * LoaderHelper instance for managing action and filter hooks.
	 *
	 * @var LoaderHelper
	 */
	private LoaderHelper $loader;
	/**
	 * FunctionsPlugins instance for managing plugin-related admin functions.
	 *
	 * @var FunctionsPlugins
	 */
	private FunctionsPlugins $plugin_functions;
	/**
	 * Assets instance for managing admin assets.
	 *
	 * @var Assets
	 */
	private Assets $assets;
	/**
	 * Constructor for the Admin class.
	 *
	 * Initializes the various admin managers and registers their assets.
	 *
	 * @param Assets $assets The Assets instance for managing admin assets.
	 */
	public function __construct( Assets $assets ) {
		$this->managers = array(
			'dashboard' => new DashboardManager(),
			'settings'  => new SettingsManager(),
			'reports'   => new ReportsManager(),
			'user'      => new UserManager(),
			'tools'     => new ToolsManager(),
		);
		/**
		 * Initialize the individual manager instances from the registry.
		 */
		$this->dashboard_manager = $this->managers['dashboard'];
		/**
		 * Initialize the settings manager instance from the registry.
		 */
		$this->settings_manager  = $this->managers['settings'];
		/**
		 * Initialize the reports manager instance from the registry.
		 */
		$this->reports_manager   = $this->managers['reports'];
		/**
		 * Initialize the user manager instance from the registry.
		 */
		$this->user_manager      = $this->managers['user'];
		/**
		 * Initialize the tools manager instance from the registry.
		 */
		$this->tools_manager     = $this->managers['tools'];
		/**
		 * Initialize the plugin functions manager.
		 */
		$this->plugin_functions = new FunctionsPlugins();
		/**
		 * Initialize the loader helper.
		 */
		$this->loader = new LoaderHelper();
		/**
		 * Initialize the assets manager.
		 */
		$this->assets = $assets;
		/**
		 * Register assets for the admin managers.
		 */
		foreach ( $this->managers as $manager ) {
			$manager->register_assets( $assets );
		}
		/**
		 * Register assets for the plugin functions manager.
		 */
		$this->loader->register_component(
			$this,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_pluginname_dismiss_onboarding',
					'callback' => 'dismiss_onboarding',
				),
			)
		);
		$this->loader->register_component(
			$this->plugin_functions,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_pluginname_toggle_plugin',
					'callback' => 'toggle_plugin',
				),
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_pluginname_save_plugin_settings',
					'callback' => 'save_plugin_settings',
				),
			)
		)->run();
	}
	/**
	 * Register admin menu pages and subpages.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu(): void {
		LoggerHelper::write_log( 'PluginName admin menu registration started.' );

		try {
			FunctionsSidebar::register_admin_menu( $this );
			LoggerHelper::write_log( 'PluginName admin menu registration complete.' );
		} catch ( \Throwable $e ) {
			LoggerHelper::write_log( 'PluginName admin menu registration failed: ' . $e->getMessage() );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_die( esc_html( $e->getMessage() ), __( 'PluginName admin menu error', 'pluginname' ), array( 'back_link' => true ) );
			}
		}
	}
	/**
	 * Render the dashboard page.
	 *
	 * This method is responsible for rendering the dashboard page of the PluginName plugin.
	 * It delegates the rendering to the DashboardManager instance.
	 */
	public function render_dashboard(): void {
		$group = RequestHelper::get_key( 'group', '' );
		$tab   = RequestHelper::get_key( 'tab', '' );
		$this->route_manager( $group, $tab );
	}

	/**
	 * Dispatch the admin request to the correct manager.
	 *
	 * @param string $group Requested group slug.
	 * @param string $tab Requested tab slug.
	 * @return void
	 */
	public function route_manager( string $group, string $tab = '' ): void {
		$group = sanitize_key( $group );
		$normalized_group = array(
			'pluginname'     => 'dashboard',
			'dashboard'       => 'dashboard',
			'user-management' => 'users',
			'settings'        => 'settings',
			'tools'           => 'tools',
			'reports'         => 'reports',
		)[ $group ] ?? 'dashboard';

		LoggerHelper::write_log( sprintf( 'PluginName dashboard render triggered. Group=%s Tab=%s', $group, $tab ) );

		try {
			switch ( $normalized_group ) {
				case 'users':
					LoggerHelper::write_log( 'PluginName dashboard routed to users page.' );
					$this->render_users();
					return;
				case 'reports':
					LoggerHelper::write_log( 'PluginName dashboard routed to reports page.' );
					$this->render_reports();
					return;
				case 'settings':
					LoggerHelper::write_log( 'PluginName dashboard routed to settings page.' );
					$this->render_settings();
					return;
				case 'tools':
					LoggerHelper::write_log( 'PluginName dashboard routed to tools page.' );
					$this->render_tools();
					return;
				default:
					LoggerHelper::write_log( 'PluginName dashboard default render path selected.' );
					$this->dashboard_manager->render();
			}
		} catch ( \Throwable $e ) {
			LoggerHelper::write_log( 'PluginName dashboard render failed: ' . $e->getMessage() );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				wp_die( esc_html( $e->getMessage() ), __( 'PluginName dashboard error', 'pluginname' ), array( 'back_link' => true ) );
			}
		}
	}
	/**
	 * Dismiss the onboarding modal.
	 *
	 * This method handles the AJAX request to dismiss the onboarding modal for the PluginName plugin.
	 */
	public function dismiss_onboarding(): void {
		if ( ! AjaxHelper::authorized( 'pluginname_dismiss_onboarding', 'manage_options' ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to dismiss the PluginName onboarding modal.', 'pluginname' ) );
		}

		Settings::register_group( 'setup', array( 'first_install_complete' => false ) );
		Settings::set( 'first_install_complete', true );
		Settings::set( 'onboarding_steps_complete', 3 );

		AjaxHelper::success( array( 'dismissed' => true ) );
	}
	/**
	 * Render PluginName users page.
	 *
	 * This method is responsible for rendering the users page of the PluginName plugin.
	 * It delegates the rendering to the CustomerManager instance.
	 */
	public function render_users(): void {
		LoggerHelper::write_log( 'PluginName users render started.' );
		$this->user_manager->render();
		LoggerHelper::write_log( 'PluginName users render complete.' );
	}
	/**
	 * Render PluginName reports page.
	 *
	 * This method is responsible for rendering the reports page of the PluginName plugin.
	 * It delegates the rendering to the ReportsManager instance.
	 */
	public function render_reports(): void {
		LoggerHelper::write_log( 'PluginName reports render started.' );
		$this->reports_manager->render();
		LoggerHelper::write_log( 'PluginName reports render complete.' );
	}
	/**
	 * Render the settings page.
	 *
	 * This method is responsible for rendering the settings page of the PluginName plugin.
	 * It delegates the rendering to the SettingsManager instance.
	 */
	public function render_settings(): void {
		LoggerHelper::write_log( 'PluginName settings page render started.' );
		$this->settings_manager->render();
		LoggerHelper::write_log( 'PluginName settings page render complete.' );
	}
	/**
	 * Render the tools page.
	 *
	 * @return void
	 */
	public function render_tools(): void {
		LoggerHelper::write_log( 'PluginName tools page render started.' );
		$this->tools_manager->render();
		LoggerHelper::write_log( 'PluginName tools page render complete.' );
	}
	/**
	 * Render the analytics page.
	 *
	 * This method is responsible for rendering the analytics page of the PluginName plugin.
	 * It delegates the rendering to the AnalyticsManager instance.
	 */
	/**
	 * Get the capability for a given key, with a fallback.
	 *
	 * @param string $key The settings key to retrieve the capability for.
	 * @param string $fallback The fallback capability if the key is not set or invalid.
	 * @return string The capability associated with the key, or the fallback if not valid.
	 */
	public function capability( string $key, string $fallback ): string {
		$value   = Settings::get( $key, $fallback );
		$values  = is_array( $value ) ? $value : array( $value );
		$allowed = array_merge( array( 'manage_options', 'edit_posts', 'publish_posts', 'manage_categories', 'delete_posts' ), array_keys( Capabilities::definitions() ) );
		foreach ( $values as $value ) {
			$capability = SanitizationHelper::key( $value, $fallback );
			if ( in_array( $capability, $allowed, true ) ) {
				return $capability;
			}
		}
		return $fallback;
	}
}


