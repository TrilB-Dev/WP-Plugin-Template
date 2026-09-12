<?php
/**
 * Settings class.
 *
 * @package PluginName\Includes\Settings
 */
namespace PluginName\Includes\Settings;

use PluginName\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {
	/**
	 * General settings group.
	 * 
	 * @since 1.0.0
	 */
	public const GENERAL = 'general';
	/**
	 * Layout settings group.
	 * 
	 * @since 1.0.0
	 */
	public const LAYOUT  = 'layout';
	/**
	 * Access settings group.
	 * 
	 * @since 1.0.0
	 */
	public const ACCESS  = 'access';
	/**
	 * Tools settings group.
	 * 
	 * @since 1.0.0
	 */
	public const TOOLS   = 'tools';
	/**
	 * General settings group.
	 * 
	 * @since 1.0.0
	 */
	public static function get( string $key, $default = null ) {
		return SettingsManager::get( $key, $default );
	}
	/**
	 * Retrieve a specific setting key from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to retrieve.
	 * @param mixed  $default The default value to return if the key is not found.
	 * @return mixed The value of the setting key if found, otherwise the default value.
	 */
	public static function get_string( string $key, string $default = '' ): string {
		return SanitizationHelper::text( self::get( $key, $default ), $default );
	}
	/**
	 * Retrieve a specific setting key as a sanitized key from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to retrieve.
	 * @param string $default The default value to return if the key is not found.
	 * @return string The sanitized key value of the setting key if found, otherwise the default value.
	 */
	public static function get_key( string $key, string $default = '' ): string {
		return SanitizationHelper::key( self::get( $key, $default ), $default );
	}

	/**
	 * Retrieve a specific setting key as a sanitized slug from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to retrieve.
	 * @param string $default The default value to return if the key is not found.
	 * @return string The sanitized slug value of the setting key if found, otherwise the default value.
	 */
	public static function get_slug( string $key, string $default = '' ): string {
		return SanitizationHelper::slug( self::get( $key, $default ), $default );
	}

	/**
	 * Retrieve a specific setting key as an integer from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to retrieve.
	 * @param int    $default The default value to return if the key is not found.
	 * @return int The integer value of the setting key if found, otherwise the default value.
	 */
	public static function get_int( string $key, int $default = 0 ): int {
		return SanitizationHelper::integer( self::get( $key, $default ), $default );
	}

	/**
	 * Retrieve a specific setting key as a boolean from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to retrieve.
	 * @param bool   $default The default value to return if the key is not found.
	 * @return bool The boolean value of the setting key if found, otherwise the default value.
	 */
	public static function get_bool( string $key, bool $default = false ): bool {
		$value = self::get( $key, $default );
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		$parsed = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		return null === $parsed ? $default : $parsed;
	}
	/**
	 * Set a specific setting key in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to set.
	 * @param mixed  $value The value to set for the key.
	 * @return bool True if the key was successfully set, false otherwise.
	 */
	public static function set( string $key, $value ): bool {
		return SettingsManager::set( $key, $value );
	}

	/**
	 * Delete a specific setting key from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to delete.
	 * @return bool True if the key was successfully deleted, false otherwise.
	 */
	public static function delete( string $key ): bool {
		return SettingsManager::delete( $key );
	}

	/**
	 * Check if a specific setting key exists in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to check.
	 * @return bool True if the key exists, false otherwise.
	 */
	public static function has( string $key ): bool {
		return SettingsManager::has( $key );
	}

	/**
	 * Retrieve all settings within a specific group from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group The group of settings to retrieve.
	 * @param array|null $default The default value to return if the group is not found.
	 * @return array|null The array of settings within the group if found, otherwise the default value.
	 */
	public static function get_group( string $group, ?array $default = null ): ?array {
		return SettingsManager::get_group( $group ) ?? $default;
	}

	/**
	 * Set all settings within a specific group in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group The group of settings to set.
	 * @param array  $settings The array of settings to set for the group.
	 * @return bool True if the group was successfully set, false otherwise.
	 */
	public static function set_group( string $group, array $settings ): bool {
		return SettingsManager::set_group( $group, $settings );
	}

	/**
	 * Register a new group of settings with default values in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $group The group of settings to register.
	 * @param array  $defaults The default values for the group.
	 * @return bool True if the group was successfully registered, false otherwise.
	 */
	public static function register_group( string $group, array $defaults = array() ): bool {
		return SettingsManager::register_group( $group, $defaults );
	}

	/**
	 * Register a new setting key within a specific group with a default value in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to register.
	 * @param string $group The group to which the setting key belongs.
	 * @param mixed  $default The default value for the setting key.
	 * @return bool True if the key was successfully registered, false otherwise.
	 */
	public static function register_key( string $key, string $group, $default = null ): bool {
		return SettingsManager::register_key( $key, $group, $default );
	}

	/**
	 * Retrieve all settings from the database.
	 *
	 * @since 1.0.0
	 *
	 * @return array An associative array of all settings.
	 */
	public static function get_all(): array {
		return SettingsManager::get_all();
	}
}



