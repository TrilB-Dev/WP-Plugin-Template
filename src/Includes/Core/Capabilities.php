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
	private static array $extensions = [];

	/**
	 * Return the core and registered extension capability definitions.
	 *
	 * @return array<string, array{group: string, label: string, description: string}>
	 */
	public static function definitions(): array {
		return array_merge(
			[
				'pluginname_admin_view' => [ 'group' => 'PluginName Wikis', 'label' => __( 'View Wikis Administration', 'pluginname' ), 'description' => __( 'Allows access to the PluginName Wikis administration area.', 'pluginname' ) ],
				'pluginname_create' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Create Wikis', 'pluginname' ), 'description' => __( 'Allows creating Wikis.', 'pluginname' ) ],
				'pluginname_edit' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Edit Wikis', 'pluginname' ), 'description' => __( 'Allows editing Wikis.', 'pluginname' ) ],
				'pluginname_delete' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Delete Wikis', 'pluginname' ), 'description' => __( 'Allows deleting Wikis.', 'pluginname' ) ],
				'pluginname_publish' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Publish Wikis', 'pluginname' ), 'description' => __( 'Allows publishing Wikis.', 'pluginname' ) ],
				'pluginname_edit_published' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Edit Published Wikis', 'pluginname' ), 'description' => __( 'Allows editing published Wikis.', 'pluginname' ) ],
				'pluginname_delete_published' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Delete Published Wikis', 'pluginname' ), 'description' => __( 'Allows deleting published Wikis.', 'pluginname' ) ],
				'pluginname_edit_others' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Edit Others Wikis', 'pluginname' ), 'description' => __( 'Allows editing Wikis created by other users.', 'pluginname' ) ],
				'pluginname_delete_others' => [ 'group' => 'PluginName Wikis', 'label' => __( 'Delete Others Wikis', 'pluginname' ), 'description' => __( 'Allows deleting Wikis created by other users.', 'pluginname' ) ],
				'pluginname_admin_page_view' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'View Wiki Pages Administration', 'pluginname' ), 'description' => __( 'Allows access to the PluginName Wiki Pages administration area.', 'pluginname' ) ],
				'pluginname_page_create' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Create Wiki Pages', 'pluginname' ), 'description' => __( 'Allows creating Wiki Pages.', 'pluginname' ) ],
				'pluginname_page_edit' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Edit Wiki Pages', 'pluginname' ), 'description' => __( 'Allows editing Wiki Pages.', 'pluginname' ) ],
				'pluginname_page_delete' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Delete Wiki Pages', 'pluginname' ), 'description' => __( 'Allows deleting Wiki Pages.', 'pluginname' ) ],
				'pluginname_page_edit_others' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Edit Others Wiki Pages', 'pluginname' ), 'description' => __( 'Allows editing Wiki Pages created by other users.', 'pluginname' ) ],
				'pluginname_page_delete_others' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Delete Others Wiki Pages', 'pluginname' ), 'description' => __( 'Allows deleting Wiki Pages created by other users.', 'pluginname' ) ],
				'pluginname_page_publish' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Publish Wiki Pages', 'pluginname' ), 'description' => __( 'Allows publishing Wiki Pages.', 'pluginname' ) ],
				'pluginname_page_edit_published' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Edit Published Wiki Pages', 'pluginname' ), 'description' => __( 'Allows editing published Wiki Pages.', 'pluginname' ) ],
				'pluginname_page_delete_published' => [ 'group' => 'PluginName Wiki Pages', 'label' => __( 'Delete Published Wiki Pages', 'pluginname' ), 'description' => __( 'Allows deleting published Wiki Pages.', 'pluginname' ) ],
				'pluginname_settings_general_view' => [ 'group' => 'PluginName Settings', 'label' => __( 'View General Settings', 'pluginname' ), 'description' => __( 'Allows viewing general PluginName settings.', 'pluginname' ) ],
				'pluginname_settings_general_edit' => [ 'group' => 'PluginName Settings', 'label' => __( 'Edit General Settings', 'pluginname' ), 'description' => __( 'Allows editing general PluginName settings.', 'pluginname' ) ],
				'pluginname_settings_layout_view' => [ 'group' => 'PluginName Settings', 'label' => __( 'View Layout Settings', 'pluginname' ), 'description' => __( 'Allows viewing PluginName layout settings.', 'pluginname' ) ],
				'pluginname_settings_layout_edit' => [ 'group' => 'PluginName Settings', 'label' => __( 'Edit Layout Settings', 'pluginname' ), 'description' => __( 'Allows editing PluginName layout settings.', 'pluginname' ) ],
				'pluginname_settings_plugins_view' => [ 'group' => 'PluginName Settings', 'label' => __( 'View Plugin Settings', 'pluginname' ), 'description' => __( 'Allows viewing PluginName plugin settings.', 'pluginname' ) ],
				'pluginname_settings_plugins_int_view' => [ 'group' => 'PluginName Settings', 'label' => __( 'View Internal Plugin Settings', 'pluginname' ), 'description' => __( 'Allows viewing settings for internal PluginName plugins.', 'pluginname' ) ],
				'pluginname_settings_plugins_int_edit' => [ 'group' => 'PluginName Settings', 'label' => __( 'Edit Internal Plugin Settings', 'pluginname' ), 'description' => __( 'Allows editing settings for internal PluginName plugins.', 'pluginname' ) ],
				'pluginname_settings_plugins_ext_view' => [ 'group' => 'PluginName Settings', 'label' => __( 'View External Plugin Settings', 'pluginname' ), 'description' => __( 'Allows viewing settings for external PluginName plugins.', 'pluginname' ) ],
				'pluginname_settings_plugins_ext_edit' => [ 'group' => 'PluginName Settings', 'label' => __( 'Edit External Plugin Settings', 'pluginname' ), 'description' => __( 'Allows editing settings for external PluginName plugins.', 'pluginname' ) ],
				'pluginname_settings_access_view' => [ 'group' => 'PluginName Settings', 'label' => __( 'View Access Settings', 'pluginname' ), 'description' => __( 'Allows viewing PluginName access settings.', 'pluginname' ) ],
				'pluginname_settings_access_edit' => [ 'group' => 'PluginName Settings', 'label' => __( 'Edit Access Settings', 'pluginname' ), 'description' => __( 'Allows editing PluginName access settings.', 'pluginname' ) ],
				'pluginname_tools_import' => [ 'group' => 'PluginName Tools', 'label' => __( 'Import PluginName Data', 'pluginname' ), 'description' => __( 'Allows importing PluginName data.', 'pluginname' ) ],
				'pluginname_tools_export' => [ 'group' => 'PluginName Tools', 'label' => __( 'Export PluginName Data', 'pluginname' ), 'description' => __( 'Allows exporting PluginName data.', 'pluginname' ) ],
				'pluginname_tools_debug' => [ 'group' => 'PluginName Tools', 'label' => __( 'View Debug Tools', 'pluginname' ), 'description' => __( 'Allows using PluginName debug tools.', 'pluginname' ) ],
				'pluginname_tools_analytics' => [ 'group' => 'PluginName Tools', 'label' => __( 'View Analytics Tools', 'pluginname' ), 'description' => __( 'Allows viewing PluginName analytics.', 'pluginname' ) ],
			],
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