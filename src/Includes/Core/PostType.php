<?php
/**
 * PostType class for managing custom post types in the plugin.
 *
 * @package PluginName\Includes\Core
 */
namespace PluginName\Includes\Core;

use PluginName\Includes\Settings\Settings;
use PluginName\Includes\Functions\Helpers\PermalinkHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PostType {

	/**
	 * PluginName page post type identifier.
	 *
	 * @var string
	 */
	public const PLUGINNAME = 'pluginname';
	/**
	 * Register the custom post types.
	 * 
	 * @since 1.0.0
	 */
	public function register(): void {
		register_post_type( self::PLUGINNAME, self::page_args() );
		add_filter( 'post_type_link', array( PermalinkHelper::class, 'filter_page_permalink' ), 10, 2 );
		PermalinkHelper::rewrite_rule();
	}
	/**
	 * Get the PluginName container post type identifier.
	 *
	 * @return string
	 */
	public static function get_pluginname_post_type_name(): string {
		return self::PLUGINNAME;
	}
	/**
	 * Get the public PluginName page post type identifier.
	 *
	 * @return string
	 */
	public static function get_post_type_name(): string {
		return self::PLUGINNAME;
	}
	/**
	 * Get the rewrite slug for the public PluginName page post type.
	 *
	 * @return string
	 */
	public static function page_rewrite_slug(): string {
		return self::setting_slug( 'root_slug', 'pluginname' );
	}
	/**
	 * Build the public PluginName page post type definition.
	 *
	 * @return array<string, mixed> Registration arguments.
	 */
	public static function page_args(): array {
		return apply_filters(
			'pluginname_page_post_type_args',
			array(
				'labels'          => array(
					'name'          => __( 'PluginName Pages', 'pluginname' ),
					'singular_name' => __( 'PluginName Page', 'pluginname' ),
					'add_new_item'  => __( 'Add New PluginName Page', 'pluginname' ),
					'edit_item'     => __( 'Edit PluginName Page', 'pluginname' ),
				),
				'public'          => true,
				'show_ui'         => false,
				'show_in_rest'    => true,
				'has_archive'     => false,
				'rewrite'         => array( 'slug' => self::page_rewrite_slug() ),
				'supports'        => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
				'capability_type' => array(  ),
				'map_meta_cap'    => true,
			),
			self::PLUGINNAME
		);
	}

	public static function get_post_type_names(): array {
		return array( self::PLUGINNAME );
	}

	private static function setting_slug( string $key, string $fallback ): string {
		$value = sanitize_title( (string) Settings::get( $key, $fallback ) );
		return $value !== '' ? $value : $fallback;
	}
}



