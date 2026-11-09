<?php
/**
 * Role registration for PluginName.
 *
 * @package PluginName\Includes\Core
 * @since 1.0.0
 */
namespace PluginName\Includes\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Roles {
	/**
	 * Role identifiers for PluginName.
	 * 
	 * @since 1.0.0
	 * @var string The role identifier for the manager role.
	 */
	public const MANAGER_ROLE = 'pluginname_manager';
	public const AUTHOR_ROLE = 'pluginname_author';


	/**
	 * Install the default PluginName roles used by PluginName.
	 *
	 * @return void
	 */
	public static function install(): void {
		if ( ! function_exists( 'add_role' ) || ! function_exists( 'get_role' ) ) {
			return;
		}

		self::manager_role();
		self::author_role();
	}
	/**
	 * Get the manager role identifier.
	 *
	 * @since 1.0.0
	 * @return string The manager role identifier.
	 */
	public static function manager_role(): string {
		if ( ! get_role( self::MANAGER_ROLE ) ) {
			add_role(
				self::MANAGER_ROLE,
				__( 'Mod Manager', 'pluginname' ),
				array(
					'read'                       => true,
					'add_users'       			 => true,
					'edit_users'        		 => true,
					'create_users'      		 => true,
					'delete_users'  			 => true,
					'pluginname_suspend_users'  	 => true,
					'pluginname_ban_users'  	 	 => true,
				)
			);
		}
		return self::MANAGER_ROLE;
	}
	/**
	 * Get the author role identifier.
	 *
	 * @since 1.0.0
	 * @return string The author role identifier.
	 */
	public static function author_role(): string {
		if ( ! get_role( self::AUTHOR_ROLE ) ) {
			add_role(
				self::AUTHOR_ROLE,
				__( 'Mod Author', 'pluginname' ),
				array(
					'read' => true,
					'pluginname_manager_mods_view' => true,
					'pluginname_manager_mods_edit' => true,
					'pluginname_manager_mods_delete' => true,
					'pluginname_manager_groups_view' => true,
				)
			);
		}
		return self::AUTHOR_ROLE;
	}
}




