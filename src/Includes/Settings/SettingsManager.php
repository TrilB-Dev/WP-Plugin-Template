<?php
/**
 * SettingsManager class.
 *
 * @package PluginName\Includes\Settings
 */

namespace PluginName\Includes\Settings;

use PluginName\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsManager {
	/**
	 * Registered setting groups and their corresponding default settings.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $registered_groups = array();

	/**
	 * Registered setting keys and their corresponding groups.
	 * 
	 * @var array<string, string>
	 */
	private static array $registered_keys = array();
	/**
	 * Get the name of the settings table in the database.
	 *
	 * @return string The name of the settings table.
	 */
	public static function table_name(): string {
		return Database::table_name( 'settings' );
	}
	/**
	 * Get the name of the settings table in the database.
	 *
	 * @return string The name of the settings table.
	 */
	public static function install(): void {
		Database::install();

		foreach ( self::registered_defaults() as $group => $settings ) {
			$stored_settings = self::get_group( $group );
			if ( null === $stored_settings ) {
				$legacy_settings = self::get_legacy_group( $group );
				$stored_settings = is_array( $legacy_settings ) ? $legacy_settings : array();
			}

			self::set_group( $group, array_merge( $settings, $stored_settings ) );
			self::delete_legacy_group( $group );
		}
	}
	/**
	 * Retrieve a specific setting key from the database.
	 *
	 * @param string $key The setting key to retrieve.
	 * @param mixed  $default The default value to return if the key is not found.
	 * @return mixed The value of the setting key if found, otherwise the default value.
	 */
	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		foreach ( $settings as $group_settings ) {
			if ( is_array( $group_settings ) && array_key_exists( $key, $group_settings ) ) {
				return $group_settings[ $key ];
			}
		}
		return self::registered_default( $key, $default );
	}
	/**
	 * Set a specific setting key in the database.
	 *
	 * @param string $key The setting key to set.
	 * @param mixed  $value The value to set for the key.
	 * @return bool True if the key was successfully set, false otherwise.
	 */
	public static function set( string $key, $value ): bool {
		$group            = self::group_for_key( $key );
		$settings         = self::get_group( $group ) ?? array();
		$settings[ $key ] = $value;
		return self::set_group( $group, $settings );
	}
	/**
	 * Delete a specific setting key from the database.
	 *
	 * @param string $key The setting key to delete.
	 * @return bool True if the key was successfully deleted, false otherwise.
	 */
	public static function delete( string $key ): bool {
		$group    = self::group_for_key( $key );
		$settings = self::get_group( $group );
		if ( ! is_array( $settings ) || ! array_key_exists( $key, $settings ) ) {
			return false;
		}
		unset( $settings[ $key ] );
		return self::set_group( $group, $settings );
	}
	/**
	 * Check if a specific setting key exists in the database.
	 *
	 * @param string $key The setting key to check.
	 * @return bool True if the key exists, false otherwise.
	 */
	public static function has( string $key ): bool {
		foreach ( self::get_all() as $settings ) {
			if ( is_array( $settings ) && array_key_exists( $key, $settings ) ) {
				return true;
			}
		}

		return false;
	}
	/**
	 * Retrieve all settings from the database.
	 *
	 * @return array The array of all settings grouped by their respective groups.
	 */
	public static function get_all(): array {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return array();
		}

		$rows = $wpdb->get_results( 'SELECT setting_group, setting_value FROM ' . self::table_name(), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$settings = array();
		foreach ( $rows as $row ) {
			$group              = self::logical_group( $row['setting_group'] );
			$settings[ $group ] = maybe_unserialize( $row['setting_value'] );
		}
		return $settings;
	}
	/**
	 * Retrieve the default settings for all registered groups.
	 *
	 * @return array The default settings array.
	 */
	public static function defaults(): array {
		return array(
			'general' => array(
				'root_name'           => 'PluginName',
			),
			'access'  => array(),
			'tools'   => array(
				'debug_logging'   => false,
				'console_logging' => false,
			),
		);
	}
	/**
	 * Retrieve a group of settings from the database.
	 *
	 * @param string $group The group name to retrieve.
	 * @return array|null The settings array if found, null otherwise.
	 */
	public static function get_group( string $group ): ?array {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return null;
		}

		$value    = $wpdb->get_var( $wpdb->prepare( 'SELECT setting_value FROM ' . self::table_name() . ' WHERE setting_group = %s', self::storage_group( $group ) ) );
		$settings = $value === null ? null : maybe_unserialize( $value );
		return is_array( $settings ) ? $settings : null;
	}
	/**
	 * Set a group of settings in the database.
	 *
	 * @param string $group The group name to set.
	 * @param array $settings The settings array to store.
	 * @return bool True if the group was successfully set, false otherwise.
	 */
	public static function set_group( string $group, array $settings ): bool {
		global $wpdb;
		return false !== $wpdb->replace(
			self::table_name(),
			array(
				'setting_group' => self::storage_group( $group ),
				'setting_value' => maybe_serialize( $settings ),
				'autoload'      => 'yes',
				'updated_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}
	/**
	 * Register a settings group with default values.
	 *
	 * @param string $group The group name to register.
	 * @param array $defaults The default settings for the group.
	 * @return bool True if the group was successfully registered, false otherwise.
	 */
	public static function register_group( string $group, array $defaults = array() ): bool {
		$group = self::normalize_group( $group );
		if ( '' === $group ) {
			return false;
		}

		self::$registered_groups[ $group ] = array_merge( self::$registered_groups[ $group ] ?? array(), $defaults );
		foreach ( $defaults as $key => $default ) {
			$key = sanitize_key( (string) $key );
			if ( '' !== $key ) {
				self::$registered_keys[ $key ] = $group;
			}
		}
		return true;
	}
	/**
	 * Register a single setting key under a specific group with an optional default value.
	 *
	 * @param string $key The setting key to register.
	 * @param string $group The group under which the key should be registered.
	 * @param mixed $default The default value for the setting key.
	 * @return bool True if the key was successfully registered, false otherwise.
	 */
	public static function register_key( string $key, string $group, $default = null ): bool {
		$key = sanitize_key( $key );
		if ( '' === $key || ! self::register_group( $group ) ) {
			return false;
		}

		$group                                     = self::normalize_group( $group );
		self::$registered_keys[ $key ]             = $group;
		self::$registered_groups[ $group ][ $key ] = $default;
		return true;
	}
	/**
	 * Normalize the group name by ensuring it is a non-empty string.
	 *
	 * @param string $group The group name to normalize.
	 * @return string The normalized group name.
	 */
	private static function storage_group( string $group ): string {
		$group = self::normalize_group( $group );
		return str_starts_with( $group, 'wikipress_' ) ? $group : 'wikipress_' . $group;
	}
	/**
	 * Retrieve the logical group name from the storage group name.
	 *
	 * @param string $group The storage group name.
	 * @return string The logical group name.
	 */
	private static function logical_group( string $group ): string {
		return str_starts_with( $group, 'wikipress_' ) ? substr( $group, 10 ) : $group;
	}
	/**
	 * Retrieve the legacy settings group from the database.
	 *
	 * @param string $group The group name to retrieve.
	 * @return array|null The settings array if found, null otherwise.
	 */
	private static function get_legacy_group( string $group ): ?array {
		global $wpdb;

		if ( ! self::table_exists() ) {
			return null;
		}

		$value = $wpdb->get_var( $wpdb->prepare( 'SELECT setting_value FROM ' . self::table_name() . ' WHERE setting_group = %s', sanitize_key( $group ) ) );
		return $value === null ? null : maybe_unserialize( $value );
	}
	/**
	 * Check if the settings table exists in the database.
	 *
	 * @return bool True if the table exists, false otherwise.
	 */
	private static function table_exists(): bool {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! method_exists( $wpdb, 'get_var' ) ) {
			return false;
		}

		if ( ! method_exists( $wpdb, 'prepare' ) ) {
			return false;
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', self::table_name() );
		$table = $wpdb->get_var( $query );

		return is_string( $table ) && '' !== $table;
	}
	/**
	 * Delete the legacy settings group from the database.
	 *
	 * @param string $group The group name to delete.
	 */
	private static function delete_legacy_group( string $group ): void {
		global $wpdb;
		$wpdb->delete( self::table_name(), array( 'setting_group' => sanitize_key( $group ) ), array( '%s' ) );
	}
	/**
	 * Determine the group associated with a specific key.
	 *
	 * @param string $key The key to look up.
	 * @return string The group name associated with the key.
	 */
	private static function group_for_key( string $key ): string {
		$key = sanitize_key( $key );
		if ( isset( self::$registered_keys[ $key ] ) ) {
			return self::$registered_keys[ $key ];
		}
		if ( in_array( $key, array( 'create_wikis', 'write_pages', 'view_analytics', 'manage_plugins' ), true ) ) {
			return 'access';
		}
		if ( str_contains( $key, 'layout' ) ) {
			return 'layout';
		}
		if ( str_contains( $key, 'access' ) ) {
			return 'access';
		}
		if ( str_contains( $key, 'tool' ) ) {
			return 'tools';
		}
		return 'general';
	}

	/**
	 * Return core and extension defaults for activation and fallback reads.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function registered_defaults(): array {
		$defaults = self::defaults();
		foreach ( self::$registered_groups as $group => $settings ) {
			$defaults[ $group ] = array_merge( $defaults[ $group ] ?? array(), $settings );
		}

		return $defaults;
	}
	/**
	 * Retrieve the registered default value for a specific key, with a fallback if not found.
	 *
	 * @param string $key The key to retrieve the default for.
	 * @param mixed $fallback The fallback value if the key is not found.
	 * @return mixed The registered default value or the fallback.
	 */
	private static function registered_default( string $key, $fallback ) {
		$key = sanitize_key( $key );
		foreach ( self::registered_defaults() as $settings ) {
			if ( array_key_exists( $key, $settings ) ) {
				return $settings[ $key ];
			}
		}

		return $fallback;
	}
	/**
	 * Normalize the group name by removing the 'wikipress_' prefix if present.
	 *
	 * @param string $group The group name to normalize.
	 * @return string The normalized group name.
	 */
	private static function normalize_group( string $group ): string {
		$group = sanitize_key( $group );
		return str_starts_with( $group, 'wikipress_' ) ? substr( $group, 10 ) : $group;
	}
	/**
	 * Check if the settings table is ready for use.
	 *
	 * @return bool True if the table exists and is accessible, false otherwise.
	 */
	private static function table_ready(): bool {
		global $wpdb;

		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
			return false;
		}

		if ( ! method_exists( $wpdb, 'get_var' ) ) {
			return false;
		}

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', self::table_name() );
		$table = $wpdb->get_var( $query );

		return null !== $table && '' !== (string) $table;
	}
}



