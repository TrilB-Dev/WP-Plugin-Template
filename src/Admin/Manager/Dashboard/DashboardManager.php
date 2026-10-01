<?php
/**
 * DashboardManager class for the PluginName plugin.
 *
 * @package PluginName
 */
namespace PluginName\Admin\Manager\Dashboard;

use PluginName\Admin\Manager\Manager;
use PluginName\Assets\Assets;
use PluginName\Includes\UserManagement\Groups\Groups;
use PluginName\Includes\UserManagement\Roles\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DashboardManager extends Manager {
	/**
	 * The slug for the dashboard page.
	 *
	 * @var string
	 */
	protected string $page;

	/**
	 * Constructor for the DashboardManager class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->page = 'pluginname';
	}

	/**
	 * Render the dashboard page via the main dashboard UI class.
	 *
	 * @since 1.0.0
	 */
	public function render(): void {
		( new DashboardMain() )->render_page_content();
	}

	/**
	 * Register the assets required for the dashboard page.
	 *
	 * @param Assets $assets The assets manager instance.
	 * @since 1.0.0
	 */
	public function register_assets( Assets $assets ): void {
		$this->register_page_assets( $assets, array( 'pluginname' ), 'dashboard' );
	}
}





