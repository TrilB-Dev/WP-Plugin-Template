<?php

namespace PluginName\Includes\Core;

use PluginName\Includes\Settings\Settings;
use PluginName\Includes\Functions\Helpers\PermalinkHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class PostType {
    public const WIKI = 'pluginname';
    public const PAGE = 'pluginname';
    public const WIKI_CAPABILITY = 'pluginname_wiki';
    public const WIKI_CAPABILITY_PLURAL = 'pluginname_wikis';
    public const PAGE_CAPABILITY = 'pluginname_page';
    public const PAGE_CAPABILITY_PLURAL = 'pluginname_pages';

    public function register(): void {
        register_post_type( self::WIKI, self::wiki_args() );
        register_post_type( self::PAGE, self::page_args() );
        add_filter( 'post_type_link', [ PermalinkHelper::class, 'filter_page_permalink' ], 10, 2 );
        PermalinkHelper::rewrite_rule();
    }

    public static function get_post_type_name(): string {
        return self::PAGE;
    }

    public static function page_rewrite_slug(): string {
        return self::setting_slug( 'root_slug', 'wiki' );
    }

    /**
     * Build the Wiki container post type definition.
     *
     * @return array<string, mixed> Registration arguments.
     */
    public static function wiki_args(): array {
        return apply_filters( 'pluginname_wiki_post_type_args', [
            'labels' => [
                'name' => __( 'Wikis', 'pluginname' ),
                'singular_name' => __( 'Wiki', 'pluginname' ),
                'add_new_item' => __( 'Add New Wiki', 'pluginname' ),
                'edit_item' => __( 'Edit Wiki', 'pluginname' ),
            ],
            'public' => false,
            'show_ui' => false,
            'show_in_rest' => true,
            'supports' => [ 'title', 'editor', 'author', 'thumbnail', 'revisions' ],
            'capability_type' => [ self::WIKI_CAPABILITY, self::WIKI_CAPABILITY_PLURAL ],
            'map_meta_cap' => true,
        ], self::WIKI );
    }

    /**
     * Build the public Wiki page post type definition.
     *
     * @return array<string, mixed> Registration arguments.
     */
    public static function page_args(): array {
        return apply_filters( 'pluginname_page_post_type_args', [
            'labels' => [
                'name' => __( 'Wiki Pages', 'pluginname' ),
                'singular_name' => __( 'Wiki Page', 'pluginname' ),
                'add_new_item' => __( 'Add New Wiki Page', 'pluginname' ),
                'edit_item' => __( 'Edit Wiki Page', 'pluginname' ),
            ],
            'public' => true,
            'show_ui' => false,
            'show_in_rest' => true,
            'has_archive' => false,
            'rewrite' => [ 'slug' => self::page_rewrite_slug() ],
            'supports' => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ],
            'capability_type' => [ self::PAGE_CAPABILITY, self::PAGE_CAPABILITY_PLURAL ],
            'map_meta_cap' => true,
        ], self::PAGE );
    }

    public static function get_post_type_names(): array {
        return [ self::WIKI, self::PAGE ];
    }

    private static function setting_slug( string $key, string $fallback ): string {
        $value = sanitize_title( (string) Settings::get( $key, $fallback ) );
        return $value !== '' ? $value : $fallback;
    }
}
