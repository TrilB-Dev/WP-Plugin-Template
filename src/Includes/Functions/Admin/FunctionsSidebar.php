<?php
/**
 * PluginName menu registration and sidebar definitions.
 *
 * @package PluginName
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace PluginName\Includes\Functions\Admin;

use PluginName\Admin\Admin;
use PluginName\Includes\Functions\Helpers\LoggerHelper;
use PluginName\Includes\Functions\Helpers\LPAMHelper;
use PluginName\Includes\Functions\Helpers\LPASMHelper;
use PluginName\Includes\Plugins\AdminMenuProviderInterface;
use PluginName\Includes\Plugins\AdminSidebarProviderInterface;
use PluginName\Includes\Plugins\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FunctionsSidebar {
	/**
	 * Register the core WordPress menu followed by plugin-provided menus.
	 *
	 * @param Admin $admin Core admin callbacks and capability resolver.
	 * @return void
	 */
	public static function register_admin_menu( Admin $admin ): void {
		foreach ( self::core_wordpress_menus( $admin ) as $menu ) {
			self::register_wordpress_menu( $menu );
		}

		foreach ( LPAMHelper::filter( self::plugin_wordpress_menus() ) as $menu ) {
			self::register_wordpress_menu( $menu );
		}
	}

	/**
	 * Return the built-in and filtered PluginName sidebar groups.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_sidebar_groups(): array {
		$groups = self::core_sidebar_groups();
		$menus  = LPASMHelper::filter( self::plugin_sidebar_menus() );

		// Create parents first so children can target a parent in any order.
		foreach ( $menus as $menu ) {
			if ( '' === self::parent_slug( $menu ) ) {
				self::add_sidebar_group( $groups, $menu );
			}
		}

		foreach ( $menus as $menu ) {
			$parent = self::parent_slug( $menu );
			if ( '' !== $parent ) {
				self::add_sidebar_item( $groups, $parent, $menu );
			}
		}

		foreach ( $groups as $group_key => $group ) {
			$filtered_items = array();
			foreach ( $group['items'] as $item ) {
				$capability = sanitize_key( (string) ( $item['capability'] ?? '' ) );
				if ( '' === $capability || current_user_can( $capability ) ) {
					$filtered_items[] = $item;
				}
			}
			$groups[ $group_key ]['items'] = $filtered_items;
		}

		return array_filter( $groups, static fn ( array $group ): bool => ! empty( $group['items'] ) );
	}

	/**
	 * Get a PluginName sidebar page URL.
	 *
	 * @param string $slug Page slug, optionally followed by a query string.
	 * @return string
	 */
	public static function get_admin_sidebar_menu_page_url( string $slug ): string {
		return admin_url( 'admin.php?page=' . $slug );
	}

	/**
	 * Get the core WordPress menus for the plugin.
	 *
	 * @param Admin $admin Core admin callbacks and capability resolver.
	 * @return array<int, array<string, mixed>>
	 */
	private static function core_wordpress_menus( Admin $admin ): array {
		return array(
			array(
				'name'       => __( 'PluginName', 'pluginname' ),
				'slug'       => 'pluginname',
				'icon'       => 'dashicons-book-alt',
				'parent'     => '',
				'callback'   => array( $admin, 'render_dashboard' ),
				'capability' => 'pluginname_admin_view',
				'position'   => 30,
			),
			array(
				'name'       => __( 'Dashboard', 'pluginname' ),
				'slug'       => 'pluginname',
				'parent'     => 'pluginname',
				'callback'   => array( $admin, 'render_dashboard' ),
				'capability' => 'pluginname_admin_view',
			),
			array(
				'name'       => __( 'Licences', 'pluginname' ),
				'slug'       => 'pluginname-licences',
				'parent'     => 'pluginname',
				'callback'   => array( $admin, 'render_licences' ),
				'capability' => 'pluginname_licence_view',
			),
			array(
				'name'       => __( 'Add Licence Type', 'pluginname' ),
				'slug'       => 'pluginname-licence-types-add',
				'parent'     => 'pluginname-licences',
				'callback'   => array( $admin, 'render_licence_type_add' ),
				'capability' => 'pluginname_licence_issue',
			),
			array(
				'name'       => __( 'Manage Licence Types', 'pluginname' ),
				'slug'       => 'pluginname-licence-types',
				'parent'     => 'pluginname-licences',
				'callback'   => array( $admin, 'render_licence_types' ),
				'capability' => 'pluginname_licence_view',
			),
			array(
				'name'       => __( 'Manage Licences', 'pluginname' ),
				'slug'       => 'pluginname-licence-management',
				'parent'     => 'pluginname-licences',
				'callback'   => array( $admin, 'render_licence_management' ),
				'capability' => 'pluginname_licence_view',
			),
			array(
				'name'       => __( 'Settings', 'pluginname' ),
				'slug'       => 'pluginname-settings',
				'parent'     => 'pluginname',
				'callback'   => array( $admin, 'render_settings' ),
				'capability' => 'pluginname_settings_general_view',
			),
			array(
				'name'       => __( 'Tools', 'pluginname' ),
				'slug'       => 'pluginname-tools',
				'parent'     => 'pluginname',
				'callback'   => array( $admin, 'render_tools' ),
				'capability' => 'pluginname_tools_debug',
			),
		);
	}

	/**
	 * Get the core sidebar groups for the plugin.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function core_sidebar_groups(): array {
		return array(
			'licences' => array(
				'label' => __( 'Licences', 'pluginname' ),
				'icon'  => 'fa-solid fa-file-signature',
				'items' => array(
					'pluginname-licences'           => array(
						'label'      => __( 'Overview', 'pluginname' ),
						'icon'       => 'fa-solid fa-key',
						'capability' => 'pluginname_licence_view',
					),
					'pluginname-licence-types-add'  => array(
						'label'      => __( 'Add Licence Type', 'pluginname' ),
						'icon'       => 'fa-solid fa-square-plus',
						'capability' => 'pluginname_licence_issue',
					),
					'pluginname-licence-types'      => array(
						'label'      => __( 'Manage Licence Types', 'pluginname' ),
						'icon'       => 'fa-solid fa-list',
						'capability' => 'pluginname_licence_view',
					),
					'pluginname-licence-management' => array(
						'label'      => __( 'Manage Licences', 'pluginname' ),
						'icon'       => 'fa-solid fa-folder-open',
						'capability' => 'pluginname_licence_view',
					),
					'pluginname-tools&tool=export'  => array(
						'label'      => __( 'Export', 'pluginname' ),
						'icon'       => 'fa-solid fa-file-export',
						'capability' => 'pluginname_tools_export',
					),
					'pluginname-tools&tool=import'  => array(
						'label'      => __( 'Import', 'pluginname' ),
						'icon'       => 'fa-solid fa-file-import',
						'capability' => 'pluginname_tools_import',
					),
				),
			),
			'settings' => array(
				'label' => __( 'Settings', 'pluginname' ),
				'icon'  => 'fa-solid fa-gear',
				'items' => array(
					'pluginname-settings&tab=general' => array(
						'label'      => __( 'General', 'pluginname' ),
						'icon'       => 'fa-solid fa-sliders',
						'capability' => 'pluginname_settings_general_view',
					),
					'pluginname-settings&tab=access'  => array(
						'label'      => __( 'Access', 'pluginname' ),
						'icon'       => 'fa-solid fa-user-shield',
						'capability' => 'pluginname_settings_access_view',
					),
					'pluginname-settings&tab=plugins' => array(
						'label'      => __( 'Plugins', 'pluginname' ),
						'icon'       => 'fa-solid fa-puzzle-piece',
						'capability' => 'pluginname_settings_plugins_view',
					),
					'pluginname-settings&tab=third-party' => array(
						'label'      => __( '3rd Party', 'pluginname' ),
						'icon'       => 'fa-solid fa-plug',
						'capability' => 'pluginname_settings_plugins_ext_view',
					),
				),
			),
			'tools'    => array(
				'label' => __( 'Operations', 'pluginname' ),
				'icon'  => 'fa-solid fa-toolbox',
				'items' => array(
					'pluginname-tools&tool=debug'  => array(
						'label'      => __( 'Debug', 'pluginname' ),
						'icon'       => 'fa-solid fa-bug-slash',
						'capability' => 'pluginname_tools_debug',
					),
					'pluginname-tools&tool=reset'  => array(
						'label'      => __( 'Reset', 'pluginname' ),
						'icon'       => 'fa-solid fa-rotate',
						'capability' => 'pluginname_tools_reset',
					),
					'pluginname-tools&tool=import' => array(
						'label'      => __( 'Import', 'pluginname' ),
						'icon'       => 'fa-solid fa-file-import',
						'capability' => 'pluginname_tools_import',
					),
					'pluginname-tools&tool=export' => array(
						'label'      => __( 'Export', 'pluginname' ),
						'icon'       => 'fa-solid fa-file-export',
						'capability' => 'pluginname_tools_export',
					),
				),
			),
		);
	}
	/**
	 * Register a WordPress menu item.
	 *
	 * @param array<string, mixed> $menu The menu definition.
	 */
	private static function register_wordpress_menu( array $menu ): void {
		$callback   = $menu['callback'] ?? null;
		$slug       = sanitize_key( (string) ( $menu['slug'] ?? '' ) );
		$name       = (string) ( $menu['name'] ?? '' );
		$parent     = self::admin_parent_slug( (string) ( $menu['parent'] ?? '' ) );
		$capability = sanitize_key( (string) ( $menu['capability'] ?? 'manage_options' ) );

		if ( '' === $slug || '' === $name || ! is_callable( $callback ) ) {
			return;
		}

		if ( '' === $parent ) {
			add_menu_page( $name, $name, $capability, $slug, $callback, $menu['icon'] ?? 'dashicons-admin-generic', $menu['position'] ?? null );
			return;
		}

		add_submenu_page( $parent, $name, $name, $capability, $slug, $callback, $menu['position'] ?? null );
	}

	/**
	 * Get all WordPress menus provided by active plugins.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function plugin_wordpress_menus(): array {
		$menus = array();

		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if ( ! $plugin instanceof AdminMenuProviderInterface || ! $plugin->is_active() ) {
				continue;
			}

			try {
				foreach ( $plugin->get_admin_menu() as $definition ) {
					if ( ! is_array( $definition ) ) {
						continue;
					}

					$menus[] = self::normalize_wordpress_menu( $definition );
					foreach ( $definition['children'] ?? array() as $child ) {
						if ( is_array( $child ) ) {
							$child['parent'] = $definition['menu_slug'] ?? '';
							$menus[]         = self::normalize_wordpress_menu( $child );
						}
					}
				}
			} catch ( \Throwable $e ) {
				LoggerHelper::write_log( sprintf( 'PluginName plugin %s failed to provide WordPress menus: %s', $plugin->get_slug(), $e->getMessage() ) );
			}
		}

		return array_values( array_filter( $menus, static fn ( $menu ): bool => is_array( $menu ) ) );
	}

	/**
	 * Normalize a WordPress menu definition into a standard format.
	 *
	 * @param array<string, mixed> $definition The raw menu definition.
	 * @return array<string, mixed> The normalized menu definition.
	 */
	private static function normalize_wordpress_menu( array $definition ): array {
		return array(
			'name'       => $definition['menu_title'] ?? $definition['page_title'] ?? '',
			'slug'       => $definition['menu_slug'] ?? '',
			'icon'       => $definition['icon'] ?? 'dashicons-admin-generic',
			'parent'     => $definition['parent'] ?? '',
			'callback'   => $definition['callback'] ?? null,
			'capability' => $definition['capability'] ?? 'manage_options',
			'position'   => $definition['position'] ?? null,
		);
	}
	/**
	 * Normalize an admin parent slug.
	 *
	 * @param string $parent The raw parent slug.
	 * @return string The normalized parent slug.
	 */
	private static function admin_parent_slug( string $parent ): string {
		$parent = strtolower( sanitize_text_field( $parent ) );
		return (string) preg_replace( '/[^a-z0-9._-]/', '', $parent );
	}

	/**
	 * Get all sidebar menus provided by active plugins.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function plugin_sidebar_menus(): array {
		$menus = array();

		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if ( ! $plugin instanceof AdminSidebarProviderInterface || ! $plugin->is_active() ) {
				continue;
			}

			try {
				foreach ( $plugin->get_admin_sidebar() as $definition ) {
					if ( ! is_array( $definition ) ) {
						continue;
					}

					if ( 'group' === ( $definition['type'] ?? '' ) ) {
						$menus[] = LPASMHelper::define( $definition['label'] ?? '', $definition['slug'] ?? '', $definition['icon'] ?? '', '', $definition['capability'] ?? '' );
						foreach ( $definition['items'] ?? array() as $child ) {
							if ( is_array( $child ) ) {
								$menus[] = LPASMHelper::define( $child['label'] ?? '', self::sidebar_slug( $child ), $child['icon'] ?? '', $definition['slug'] ?? '', $child['capability'] ?? '' );
							}
						}
						continue;
					}

					$menus[] = LPASMHelper::define( $definition['label'] ?? '', self::sidebar_slug( $definition ), $definition['icon'] ?? '', $definition['parent'] ?? '', $definition['capability'] ?? '' );
				}
			} catch ( \Throwable $e ) {
				LoggerHelper::write_log( sprintf( 'PluginName plugin %s failed to provide sidebar menus: %s', $plugin->get_slug(), $e->getMessage() ) );
			}
		}

		return $menus;
	}

	/**
	 * Generate a sidebar slug from a menu definition.
	 *
	 * @param array<string, mixed> $definition The menu definition.
	 * @return string The generated sidebar slug.
	 */
	private static function sidebar_slug( array $definition ): string {
		$page  = (string) ( $definition['page'] ?? $definition['slug'] ?? '' );
		$query = $definition['query'] ?? array();

		if ( ! is_array( $query ) || empty( $query ) ) {
			return $page;
		}

		return $page . '&' . http_build_query( array_filter( $query, 'is_scalar' ), '', '&', PHP_QUERY_RFC3986 );
	}

	/**
	 * Add a sidebar group to the collection of groups.
	 *
	 * @param array<string, array<string, mixed>> $groups The collection of sidebar groups.
	 * @param array<string, mixed> $menu The menu definition for the group.
	 */
	private static function add_sidebar_group( array &$groups, array $menu ): void {
		$slug  = self::menu_slug( $menu );
		$label = (string) ( $menu['name'] ?? '' );
		$icon  = (string) ( $menu['icon'] ?? '' );

		if ( '' !== $slug && '' !== $label && '' !== $icon ) {
			$groups[ $slug ] = array(
				'label' => $label,
				'icon'  => $icon,
				'items' => array(),
			);
		}
	}

	/**
	 * Add a sidebar item to a specific group.
	 *
	 * @param array<string, array<string, mixed>> $groups The collection of sidebar groups.
	 * @param string $parent The parent group slug.
	 * @param array<string, mixed> $menu The menu definition for the item.
	 */
	private static function add_sidebar_item( array &$groups, string $parent, array $menu ): void {
		$slug  = (string) ( $menu['slug'] ?? '' );
		$label = (string) ( $menu['name'] ?? '' );
		$icon  = (string) ( $menu['icon'] ?? '' );

		$capability = sanitize_key( (string) ( $menu['capability'] ?? '' ) );
		if ( isset( $groups[ $parent ] ) && '' !== $slug && '' !== $label && '' !== $icon && ( '' === $capability || current_user_can( $capability ) ) ) {
			$groups[ $parent ]['items'][ $slug ] = array(
				'label'      => $label,
				'icon'       => $icon,
				'capability' => $capability,
			);
		}
	}

	/**
	 * Get the parent slug from a menu definition.
	 *
	 * @param array<string, mixed> $menu The menu definition.
	 * @return string The parent slug.
	 */
	private static function parent_slug( array $menu ): string {
		return sanitize_key( (string) ( $menu['parent'] ?? '' ) );
	}

	/**
	 * Get the menu slug from a menu definition.
	 *
	 * @param array<string, mixed> $menu The menu definition.
	 * @return string The menu slug.
	 */
	private static function menu_slug( array $menu ): string {
		return sanitize_key( (string) ( $menu['slug'] ?? '' ) );
	}
}



