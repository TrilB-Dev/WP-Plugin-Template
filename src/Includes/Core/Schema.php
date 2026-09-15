<?php
/**
 * Schema class for managing core and extension database schema definitions.
 *
 * @package PluginName\Includes\Core
 * @since 1.0.0
 */
namespace PluginName\Includes\Core;

use PluginName\Includes\Core\WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Schema {
    /**
     * Register all core database tables.
     *
     * @return void
     */
    public static function register_tables(): void {
        self::settings_table();
        self::analytics_table();
        self::logs_table();
    }
    /**
     * Register the settings table schema.
     *
     * @return string
     */
    public static function settings_table(): string {
        Database::register_core_table(
            'settings',
            static function ( string $table_name, string $charset ) {
                return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                setting_key varchar(120) NOT NULL,
                setting_value longtext DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY setting_key (setting_key)
            ) {$charset};";
            }
        );

        return "settings";
    }
    /**
     * Register the analytics table schema.
     *
     * @return string
     */
    public static function analytics_table(): string {
        Database::register_core_table(
            'analytics',
            static function ( string $table_name, string $charset ) {
                return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                event_key varchar(120) NOT NULL,
                event_value longtext DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY event_key (event_key)
            ) {$charset};";
            }
        );

        return "analytics";
    }
    /**
     * Register the logs table schema.
     *
     * @return string
     */
    public static function logs_table(): string {
        Database::register_core_table(
            'logs',
            static function ( string $table_name, string $charset ) {
                return "CREATE TABLE {$table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                log_level varchar(32) NOT NULL,
                message longtext NOT NULL,
                context longtext DEFAULT NULL,
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY log_level (log_level),
                KEY created_at (created_at)
            ) {$charset};";
            }
        );

        return "logs";
    }
}