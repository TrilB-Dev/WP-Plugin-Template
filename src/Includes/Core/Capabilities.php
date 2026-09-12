<?php

namespace PluginName\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {
	/**
	 * Capability definitions contributed by PluginName extensions.
	 *
	 * @var array<string, array{group: string, label: string, description: string}>
	 */
	private static array $extensions = array();

	/**
	 * Return the core and registered extension capability definitions.
	 *
	 * @return array<string, array{group: string, label: string, description: string}>
	 */
	public static function definitions(): array {
		return array_merge(
			array(
				'pluginname_admin_view'                => array(
					'group'       => 'PluginName Licence',
					'label'       => __( 'View Licence Administration', 'pluginname' ),
					'description' => __( 'Allows access to the PluginName dashboard and admin pages.', 'pluginname' ),
				),
				'pluginname_dashboard_view'            => array(
					'group'       => 'PluginName Licence',
					'label'       => __( 'View Licence Dashboard', 'pluginname' ),
					'description' => __( 'Allows viewing the PluginName dashboard and summary status.', 'pluginname' ),
				),
				'pluginname_settings_general_view'     => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'View Licence Settings', 'pluginname' ),
					'description' => __( 'Allows viewing the general licence management settings.', 'pluginname' ),
				),
				'pluginname_settings_general_edit'     => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'Edit Licence Settings', 'pluginname' ),
					'description' => __( 'Allows editing the licence management settings.', 'pluginname' ),
				),
				'pluginname_settings_access_view'      => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'View Access Controls', 'pluginname' ),
					'description' => __( 'Allows viewing who can do what inside PluginName.', 'pluginname' ),
				),
				'pluginname_settings_access_edit'      => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'Edit Access Controls', 'pluginname' ),
					'description' => __( 'Allows changing licence access roles and permission boundaries.', 'pluginname' ),
				),
				'pluginname_settings_security_view'    => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'View Security Settings', 'pluginname' ),
					'description' => __( 'Allows viewing security and export protection settings.', 'pluginname' ),
				),
				'pluginname_settings_security_edit'    => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'Edit Security Settings', 'pluginname' ),
					'description' => __( 'Allows editing export passwords, encryption controls, and security flags.', 'pluginname' ),
				),
				'pluginname_tools_import'              => array(
					'group'       => 'PluginName Tools',
					'label'       => __( 'Import Licence Data', 'pluginname' ),
					'description' => __( 'Allows importing licence exports into the system securely.', 'pluginname' ),
				),
				'pluginname_tools_export'              => array(
					'group'       => 'PluginName Tools',
					'label'       => __( 'Export Licence Data', 'pluginname' ),
					'description' => __( 'Allows exporting licence records using encryption and a password.', 'pluginname' ),
				),
				'pluginname_tools_debug'               => array(
					'group'       => 'PluginName Tools',
					'label'       => __( 'View Debug Tools', 'pluginname' ),
					'description' => __( 'Allows using PluginName debug and diagnostics tools.', 'pluginname' ),
				),
				'pluginname_tools_reset'               => array(
					'group'       => 'PluginName Tools',
					'label'       => __( 'Reset Licence Data', 'pluginname' ),
					'description' => __( 'Allows resetting or clearing licence records and related data.', 'pluginname' ),
				),
				'pluginname_settings_plugins_view'     => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'View Plugin Settings', 'pluginname' ),
					'description' => __( 'Allows viewing PluginName plugin settings.', 'pluginname' ),
				),
				'pluginname_settings_plugins_int_view' => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'View Internal Plugin Settings', 'pluginname' ),
					'description' => __( 'Allows viewing settings for internal PluginName plugins.', 'pluginname' ),
				),
				'pluginname_settings_plugins_int_edit' => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'Edit Internal Plugin Settings', 'pluginname' ),
					'description' => __( 'Allows editing settings for internal PluginName plugins.', 'pluginname' ),
				),
				'pluginname_settings_plugins_ext_view' => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'View External Plugin Settings', 'pluginname' ),
					'description' => __( 'Allows viewing settings for external PluginName plugins.', 'pluginname' ),
				),
				'pluginname_settings_plugins_ext_edit' => array(
					'group'       => 'PluginName Settings',
					'label'       => __( 'Edit External Plugin Settings', 'pluginname' ),
					'description' => __( 'Allows editing settings for external PluginName plugins.', 'pluginname' ),
				),
			),
			self::$extensions
		);
	}

	/**
	 * Register definitions contributed by a plugin and install any missing caps.
	 *
	 * @param array<string, array{group: string, label: string, description: string}> $definitions Definitions to add.
	 * @return void
	 */
	public static function extend( array $definitions ): void {
		self::$extensions = array_merge( self::$extensions, $definitions );
		self::install();
	}

	/**
	 * Install missing capabilities without removing administrator customizations.
	 *
	 * @return void
	 */
	public static function install(): void {
		$administrator = get_role( 'administrator' );
		if ( ! $administrator ) {
			return;
		}

		foreach ( array_keys( self::definitions() ) as $capability ) {
			if ( ! $administrator->has_cap( $capability ) ) {
				$administrator->add_cap( $capability );
			}
		}
	}
}



