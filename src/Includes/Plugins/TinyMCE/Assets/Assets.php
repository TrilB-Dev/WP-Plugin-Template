<?php
/**
 * TinyMCE Editor Plugin Assets
 *
 * @package PluginName
 * @subpackage Plugins\TinyMCE\Assets
 * @since 1.0.0
 */

namespace PluginName\Includes\Plugins\TinyMCE\Assets;

use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Plugins\TinyMCE\Includes\Settings\Settings;

final class Assets {
    private LoaderHelper $loader;

    public function __construct( ?LoaderHelper $loader = null ) {
        $this->loader = $loader ?? new LoaderHelper();
    }

    /**
     * Constructor for the TinyMCE plugin assets.
     */
    public function register(): void {
        $this->loader->register_component( $this, [
            [ 'type' => 'filter', 'hook' => 'pluginname_admin_assets', 'callback' => 'register_admin_assets', 'accepted_args' => 2 ],
        ] )->run();
    }

    public function register_admin_assets( array $assets, string $context = '' ): array {
        $base_url = WIKIPRESS_URL . 'src/includes/Plugins/TinyMCE/Assets/tinymce/';

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
            'src' => WIKIPRESS_URL . 'src/includes/Plugins/TinyMCE/Assets/js/tinymce.js',
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

        if ( function_exists( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }

        return $assets;
    }
}