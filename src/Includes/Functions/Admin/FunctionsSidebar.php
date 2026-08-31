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
use PluginName\Includes\Functions\Helpers\PAMHelper;
use PluginName\Includes\Functions\Helpers\PASMHelper;
use PluginName\Includes\Plugins\AdminMenuProviderInterface;
use PluginName\Includes\Plugins\AdminSidebarProviderInterface;
use PluginName\Includes\Plugins\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns PluginName menu data and registration.
 *
 * Rendering remains in Admin and Sidebar. This class builds menu data,
 * applies extension filters, and calls the WordPress admin API.
 */
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

		foreach ( PAMHelper::filter( self::plugin_wordpress_menus() ) as $menu ) {
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
		$menus  = PASMHelper::filter( self::plugin_sidebar_menus() );

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

		foreach ( $groups as &$group ) {
			$group['items'] = array_filter(
				$group['items'],
				static fn ( array $item ): bool => '' === ( $capability = sanitize_key( (string) ( $item['capability'] ?? '' ) ) ) || current_user_can( $capability )
			);
		}
		unset( $group );

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

	/** @return array<int, array<string, mixed>> */
	private static function core_wordpress_menus( Admin $admin ): array {
		return [
			[
				'name'       => __( 'PluginName', 'pluginname' ),
				'slug'       => 'pluginname',
				'icon'       => 'dashicons-book-alt',
				'parent'     => '',
				'callback'   => [ $admin, 'render_dashboard' ],
				'capability' => 'pluginname_admin_view',
				'position'   => 30,
			],
			[
				'name'       => __( 'Dashboard', 'pluginname' ),
				'slug'       => 'pluginname',
				'parent'     => 'pluginname',
				'callback'   => [ $admin, 'render_dashboard' ],
				'capability' => 'pluginname_admin_view',
			],
			[
				'name'       => __( 'Manage Wiki', 'pluginname' ),
				'slug'       => 'pluginname-manage',
				'parent'     => 'pluginname',
				'callback'   => [ $admin, 'render_wikis' ],
				'capability' => 'pluginname_admin_view',
			],
			[
				'name'       => __( 'Settings', 'pluginname' ),
				'slug'       => 'pluginname-settings',
				'parent'     => 'pluginname',
				'callback'   => [ $admin, 'render_settings' ],
				'capability' => 'pluginname_settings_general_view',
			],
			[
				'name'       => __( 'Tools', 'pluginname' ),
				'slug'       => 'pluginname-tools',
				'parent'     => 'pluginname',
				'callback'   => [ $admin, 'render_tools' ],
				'capability' => 'pluginname_tools_debug',
			],
		];
	}

	/** @return array<string, array<string, mixed>> */
	private static function core_sidebar_groups(): array {
		return [
			'manage-wiki' => [
				'label' => __( 'Manage Wiki', 'pluginname' ),
				'icon'  => 'fa-solid fa-file-lines',
				'items' => [
					'pluginname-manage'                  => [ 'label' => __( 'Manage Wiki', 'pluginname' ), 'icon' => 'fa-solid fa-book-open-lines', 'capability' => 'pluginname_admin_view' ],
					'pluginname-manage&wiki=categories' => [ 'label' => __( 'Categories', 'pluginname' ), 'icon' => 'fa-book-open-lines-category', 'capability' => 'pluginname_edit' ],
					'pluginname-manage&wiki=tags'       => [ 'label' => __( 'Tags', 'pluginname' ), 'icon' => 'fa-kit fa-solid-book-open-lines-tag', 'capability' => 'pluginname_edit' ],
					'pluginname-manage&wiki=new'        => [ 'label' => __( 'New Wiki', 'pluginname' ), 'icon' => 'fa-kit fa-solid-book-open-lines-circle-plus', 'capability' => 'pluginname_create' ],
				],
			],
			'settings' => [
				'label' => __( 'Settings', 'pluginname' ),
				'icon'  => 'fa-solid fa-gear',
				'items' => [
					'pluginname-settings&tab=general'     => [ 'label' => __( 'General', 'pluginname' ), 'icon' => 'fa-solid fa-sliders', 'capability' => 'pluginname_settings_general_view' ],
					'pluginname-settings&tab=layout'      => [ 'label' => __( 'Layout', 'pluginname' ), 'icon' => 'fa-solid fa-table-columns', 'capability' => 'pluginname_settings_layout_view' ],
					'pluginname-settings&tab=plugins'     => [ 'label' => __( 'Plugins', 'pluginname' ), 'icon' => 'fa-solid fa-puzzle-piece', 'capability' => 'pluginname_settings_plugins_view' ],
					'pluginname-settings&tab=third-party' => [ 'label' => __( '3rd Party', 'pluginname' ), 'icon' => 'fa-solid fa-plug', 'capability' => 'pluginname_settings_plugins_ext_view' ],
					'pluginname-settings&tab=access'      => [ 'label' => __( 'Access', 'pluginname' ), 'icon' => 'fa-solid fa-user-shield', 'capability' => 'pluginname_settings_access_view' ],
				],
			],
			'tools' => [
				'label' => __( 'Tools', 'pluginname' ),
				'icon'  => 'fa-solid fa-toolbox',
				'items' => [
					'pluginname-tools&tool=debug'     => [ 'label' => __( 'Debug', 'pluginname' ), 'icon' => 'fa-solid fa-bug-slash', 'capability' => 'pluginname_tools_debug' ],
					'pluginname-tools&tool=import'    => [ 'label' => __( 'Import', 'pluginname' ), 'icon' => 'fa-solid fa-file-import', 'capability' => 'pluginname_tools_import' ],
					'pluginname-tools&tool=export'    => [ 'label' => __( 'Export', 'pluginname' ), 'icon' => 'fa-solid fa-file-export', 'capability' => 'pluginname_tools_export' ],
					'pluginname-tools&tool=analytics' => [ 'label' => __( 'Analytics', 'pluginname' ), 'icon' => 'fa-solid fa-chart-line', 'capability' => 'pluginname_tools_analytics' ],
				],
			],
		];
	}

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

	/** @return array<int, array<string, mixed>> */
	private static function plugin_wordpress_menus(): array {
		$menus = [];

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
					foreach ( $definition['children'] ?? [] as $child ) {
						if ( is_array( $child ) ) {
							$child['parent'] = $definition['menu_slug'] ?? '';
							$menus[] = self::normalize_wordpress_menu( $child );
						}
					}
				}
			} catch ( \Throwable $e ) {
				LoggerHelper::write_log( sprintf( 'PluginName plugin %s failed to provide WordPress menus: %s', $plugin->get_slug(), $e->getMessage() ) );
			}
		}

		return array_values( array_filter( $menus, static fn ( $menu ): bool => is_array( $menu ) ) );
	}

	/** @param array<string, mixed> $definition @return array<string, mixed> */
	private static function normalize_wordpress_menu( array $definition ): array {
		return [
			'name'       => $definition['menu_title'] ?? $definition['page_title'] ?? '',
			'slug'       => $definition['menu_slug'] ?? '',
			'icon'       => $definition['icon'] ?? 'dashicons-admin-generic',
			'parent'     => $definition['parent'] ?? '',
			'callback'   => $definition['callback'] ?? null,
			'capability' => $definition['capability'] ?? 'manage_options',
			'position'   => $definition['position'] ?? null,
		];
	}

	private static function admin_parent_slug( string $parent ): string {
		$parent = strtolower( sanitize_text_field( $parent ) );
		return (string) preg_replace( '/[^a-z0-9._-]/', '', $parent );
	}

	/** @return array<int, array<string, mixed>> */
	private static function plugin_sidebar_menus(): array {
		$menus = [];

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
						$menus[] = PASMHelper::define( $definition['label'] ?? '', $definition['slug'] ?? '', $definition['icon'] ?? '', '', $definition['capability'] ?? '' );
						foreach ( $definition['items'] ?? [] as $child ) {
							if ( is_array( $child ) ) {
								$menus[] = PASMHelper::define( $child['label'] ?? '', self::sidebar_slug( $child ), $child['icon'] ?? '', $definition['slug'] ?? '', $child['capability'] ?? '' );
							}
						}
						continue;
					}

					$menus[] = PASMHelper::define( $definition['label'] ?? '', self::sidebar_slug( $definition ), $definition['icon'] ?? '', $definition['parent'] ?? '', $definition['capability'] ?? '' );
				}
			} catch ( \Throwable $e ) {
				LoggerHelper::write_log( sprintf( 'PluginName plugin %s failed to provide sidebar menus: %s', $plugin->get_slug(), $e->getMessage() ) );
			}
		}

		return $menus;
	}

	/** @param array<string, mixed> $definition */
	private static function sidebar_slug( array $definition ): string {
		$page  = (string) ( $definition['page'] ?? $definition['slug'] ?? '' );
		$query = $definition['query'] ?? [];

		if ( ! is_array( $query ) || empty( $query ) ) {
			return $page;
		}

		return $page . '&' . http_build_query( array_filter( $query, 'is_scalar' ), '', '&', PHP_QUERY_RFC3986 );
	}

	/** @param array<string, array<string, mixed>> $groups @param array<string, mixed> $menu */
	private static function add_sidebar_group( array &$groups, array $menu ): void {
		$slug  = self::menu_slug( $menu );
		$label = (string) ( $menu['name'] ?? '' );
		$icon  = (string) ( $menu['icon'] ?? '' );

		if ( '' !== $slug && '' !== $label && '' !== $icon ) {
			$groups[ $slug ] = [ 'label' => $label, 'icon' => $icon, 'items' => [] ];
		}
	}

	/** @param array<string, array<string, mixed>> $groups @param array<string, mixed> $menu */
	private static function add_sidebar_item( array &$groups, string $parent, array $menu ): void {
		$slug  = (string) ( $menu['slug'] ?? '' );
		$label = (string) ( $menu['name'] ?? '' );
		$icon  = (string) ( $menu['icon'] ?? '' );

		$capability = sanitize_key( (string) ( $menu['capability'] ?? '' ) );
		if ( isset( $groups[ $parent ] ) && '' !== $slug && '' !== $label && '' !== $icon && ( '' === $capability || current_user_can( $capability ) ) ) {
			$groups[ $parent ]['items'][ $slug ] = [ 'label' => $label, 'icon' => $icon, 'capability' => $capability ];
		}
	}

	/** @param array<string, mixed> $menu */
	private static function parent_slug( array $menu ): string {
		return sanitize_key( (string) ( $menu['parent'] ?? '' ) );
	}

	/** @param array<string, mixed> $menu */
	private static function menu_slug( array $menu ): string {
		return sanitize_key( (string) ( $menu['slug'] ?? '' ) );
	}
}
