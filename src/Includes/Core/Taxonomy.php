<?php
/**
 * Core taxonomy definitions for PluginName.
 *
 * @package PluginName
 * @subpackage Includes\Core
 * @since 1.0.0
 */
namespace PluginName\Includes\Core;

use PluginName\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomy {
	/**
	 * Taxonomy identifiers for the plugin.
	 *
	 * @since 1.0.0
	 */
	public const CATEGORY = 'pluginname_category';
	/**
	 * Tag taxonomy identifier for the plugin.
	 *
	 * @since 1.0.0
	 */
	public const TAG      = 'pluginname_tag';
	/**
	 * Custom taxonomy identifier for the plugin.
	 *
	 * @since 1.0.0
	 */
	public const CUSTOM   = 'pluginname_custom';
	/**
	 * Register the plugin taxonomies.
	 *
	 * @since 1.0.0
	 */
	public function register(): void {
		register_taxonomy( self::CATEGORY, array( PostType::WIKI, PostType::PAGE ), self::category_args() );
		register_taxonomy( self::TAG, array( PostType::WIKI, PostType::PAGE ), self::tag_args() );
	}

	/**
	 * Build the hierarchical Wiki category taxonomy definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function category_args(): array {
		return apply_filters(
			'pluginname_category_taxonomy_args',
			array(
				'labels'       => array(
					'name'          => __( 'PluginName Categories', 'pluginname' ),
					'singular_name' => __( 'PluginName Category', 'pluginname' ),
				),
				'hierarchical' => true,
				'public'       => true,
				'show_ui'      => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => self::setting_slug( 'category_slug', 'wiki-category' ) ),
			),
			self::CATEGORY
		);
	}

	/**
	 * Build the non-hierarchical Wiki tag taxonomy definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function tag_args(): array {
		return apply_filters(
			'pluginname_tag_taxonomy_args',
			array(
				'labels'       => array(
					'name'          => __( 'PluginName Tags', 'pluginname' ),
					'singular_name' => __( 'PluginName Tag', 'pluginname' ),
				),
				'hierarchical' => false,
				'public'       => true,
				'show_ui'      => false,
				'show_in_rest' => true,
				'rewrite'      => array( 'slug' => self::setting_slug( 'tag_slug', 'wiki-tag' ) ),
			),
			self::TAG
		);
	}
	/**
	 * Get all taxonomy identifiers for the plugin.
	 *
	 * @return array<string> List of taxonomy identifiers.
	 */
	public static function get_taxonomy_names(): array {
		return array( self::CATEGORY, self::TAG );
	}
	/**
	 * Get the slug for a specific taxonomy setting.
	 *
	 * @param string $key The setting key.
	 * @param string $fallback The fallback value if the setting is not found.
	 * @return string The sanitized slug.
	 */
	private static function setting_slug( string $key, string $fallback ): string {
		$value = sanitize_title( (string) Settings::get( $key, $fallback ) );
		return $value !== '' ? $value : $fallback;
	}
}



