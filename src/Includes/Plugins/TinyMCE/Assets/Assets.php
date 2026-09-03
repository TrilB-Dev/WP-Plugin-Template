<?php
/**
 * TinyMCE Editor Plugin Assets
 *
 * @package PluginName
 * @subpackage Plugins\TinyMCE\Assets
 * @since 1.0.0
 */

namespace PluginName\Includes\Plugins\TinyMCE\Assets;

use PluginName\Includes\Plugins\TinyMCE\Includes\Settings\Settings;
use PluginName\Includes\Functions\Helpers\ImageHelper;
use PluginName\Includes\Functions\Helpers\LoaderHelper;

final class Assets {
    /**
     * The loader helper instance.
     * 
     * @var LoaderHelper The loader helper instance.
     */
    private LoaderHelper $loader;
    /**
     * Constructor for the TinyMCE plugin assets.
     *
     * @param LoaderHelper|null $loader The loader helper instance.
     */
    public function __construct( ?LoaderHelper $loader = null ) {
        $this->loader = $loader ?? new LoaderHelper();
    }

    /**
     * Registers the TinyMCE plugin assets with the core assets manager.
     * 
     * @return void
     */
    public function register(): void {
        $this->loader->register_component( $this,
        [
            [
                'type' => 'filter',
                'hook' => 'pluginname_admin_assets',
                'callback' => 'register_admin_assets',
                'accepted_args' => 2,
            ],
        ] )->run();
    }
    /**
     * Registers the admin assets for the TinyMCE plugin.
     *
     * @param array  $assets The current assets.
     * @param string $context The context (e.g., 'frontend', 'admin').
     * @return array The updated assets with TinyMCE assets included.
     */
    public function register_admin_assets( $assets = [], string $context = 'admin' ): array {
        if ( ! is_array( $assets ) ) {
            $assets = [];
        }

        $base_url = PLUGINNAME_URL . 'src/Includes/Plugins/TinyMCE/Assets/tinymce/';

        $assets['styles'][] = [
            'handle' => 'pluginname-tinymce-skin',
            'src' => $base_url . 'skins/ui/' . Settings::ui_skin() . '/skin.min.css',
        ];
        $assets['scripts'][] = [
            'handle' => 'pluginname-tinymce',
            'src' => $base_url . 'tinymce.min.js',
            'in_footer' => true,
        ];
        $assets['scripts'][] = [
            'handle' => 'pluginname-tinymce-boot',
            'src' => PLUGINNAME_URL . 'src/Includes/Plugins/TinyMCE/Assets/js/tinymce.js',
            'deps' => [ 'pluginname-tinymce' ],
            'in_footer' => true,
            'localize' => [
                'object_name' => 'pluginnameTinyMCE',
                'data' => [
                    'mediaTitle' => __( 'Insert media', 'pluginname' ),
                    'mediaButton' => __( 'Insert into editor', 'pluginname' ),
                    'mediaTooltip' => __( 'Insert media', 'pluginname' ),
                ],
            ],
        ];

        $assets['enqueue_media'] = true;

        return $assets;
    }
    /**
     * Get an image asset URL from the core Images directory.
     *
     * @param string $file The image path relative to Assets/images.
     * @return string The image URL, or an empty string when the path is invalid.
     */
    public static function get_image( string $file ): string {
        return ImageHelper::get_image_url( 'pluginname-tinymce', $file );
    }
}