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
use PluginName\Includes\Functions\Helpers\AjaxHelper;
use PluginName\Includes\Core\Capabilities;
use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Functions\Helpers\RequestHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Includes\Functions\Admin\FunctionsSidebar;
use PluginName\Assets\Assets;
use PluginName\Admin\Manager\Tools\ToolsManager;
use PluginName\Admin\Manager\Dashboard\DashboardManager;
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
	 * ToolsManager instance for managing tools-related admin pages.
	 *
	 * @var ToolsManager
	 */
	private ToolsManager $tools_manager;
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
		/**
		 * Initialize the admin managers and register their assets.
		 * 
		 * @since 1.0.0
		 */
		$this->dashboard_manager = new DashboardManager();
		/**
		 * Initialize the settings manager.
		 *
		 * @since 1.0.0
		 */
		$this->settings_manager = new SettingsManager();
		/**
		 * Initialize the tools manager.
		 *
		 * @since 1.0.0
		 */
		$this->tools_manager = new ToolsManager();
		/**
		 * Initialize the plugin functions manager.
		 *
		 * @since 1.0.0
		 */
		$this->plugin_functions = new FunctionsPlugins();
		/**
		 * Initialize the loader helper.
		 *
		 * @since 1.0.0
		 */
		$this->loader = new LoaderHelper();
		/**
		 * Initialize the assets manager.
		 *
		 * @since 1.0.0
		 */
		$this->assets = $assets;
		/**
		 * Register assets for the admin managers.
		 *
		 * @since 1.0.0
		 */
		$this->dashboard_manager->register_assets( $assets );
		/**
		 * Register assets for the settings manager.
		 *
		 * @since 1.0.0
		 */
		$this->settings_manager->register_assets( $assets );
		/**
		 * Register assets for the tools manager.
		 *
		 * @since 1.0.0
		 */
		$this->tools_manager->register_assets( $assets );
		/**
		 * Register assets for the plugin functions manager.
		 *
		 * @since 1.0.0
		 */
		$this->loader->register_component(
			$this,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'wp_ajax_pluginname_load_settings_tab',
					'callback' => 'load_settings_tab',
				),
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
		FunctionsSidebar::register_admin_menu( $this );
	}
	/**
	 * Render the dashboard page.
	 *
	 * This method is responsible for rendering the dashboard page of the PluginName plugin.
	 * It delegates the rendering to the DashboardManager instance.
	 */
	public function render_dashboard(): void {
		$this->dashboard_manager->render();
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
	 * Render the settings page.
	 *
	 * This method is responsible for rendering the settings page of the PluginName plugin.
	 * It delegates the rendering to the SettingsManager instance.
	 */
	public function render_settings(): void {
		$this->settings_manager->render();
	}
	/**
	 * Render the tools page.
	 *
	 * @return void
	 */
	public function render_tools(): void {
		$this->tools_manager->render();
	}
	/**
	 * Render the analytics page.
	 *
	 * This method is responsible for rendering the analytics page of the PluginName plugin.
	 * It delegates the rendering to the AnalyticsManager instance.
	 */
	public function load_settings_tab(): void {
		$tab             = RequestHelper::get_key( 'tab', 'general' );
		$view_capability = array(
			'general'     => 'pluginname_settings_general_view',
			'layout'      => 'pluginname_settings_layout_view',
			'access'      => 'pluginname_settings_access_view',
			'plugins'     => 'pluginname_settings_plugins_view',
			'third-party' => 'pluginname_settings_plugins_ext_view',
		)[ $tab ] ?? 'pluginname_settings_general_view';
		if ( ! AjaxHelper::authorized( 'pluginname_settings_tabs', $view_capability ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to load PluginName settings.', 'pluginname' ) );
		}

		$layout_section = RequestHelper::get_key( 'layout_section', 'general' );
		ob_start();
		$this->settings_manager->render_tab_content( $tab, $layout_section );
		$html = (string) ob_get_clean();
		AjaxHelper::success(
			array(
				'html'           => $html,
				'tab'            => $tab,
				'layout_section' => $layout_section,
			)
		);
	}
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



