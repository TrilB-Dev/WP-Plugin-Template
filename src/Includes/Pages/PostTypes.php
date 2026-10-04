<?php
/**
 * PostTypes class for registering plugin post types.
 *
 * @package PluginName
 * @subpackage Includes\Pages
 * @since 1.0.0
 */
namespace PluginName\Includes\Pages;

use PluginName\Includes\Core\PostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class PostTypes {
	/**
	 * Register all page-layer post types.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function register(): void {
		self::postname_posttype();
	}

	/**
	 * Register the core PluginName mod post type.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function postname_posttype(): bool {
		return PostType::create(
			'pluginname_posttype',
			array(
				'label'           => __( 'Mods', 'pluginname' ),
				'description'     => __( 'Mod entries managed by PluginName.', 'pluginname' ),
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'taxonomies'      => array( 'pluginname_category', 'pluginname_tag' ),
				'public'          => true,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_position'   => 20,
				'menu_icon'       => 'dashicons-archive',
				'has_archive'     => true,
				'show_in_rest'    => true,
				'rewrite'         => array( 'slug' => 'mods' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);
	}
}

