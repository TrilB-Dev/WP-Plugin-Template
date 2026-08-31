<?php

namespace PluginName\Includes\Plugins\FontAwesome\Assets;

use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Plugins\FontAwesome\Includes\Settings\Settings as FontAwesomeSettings;

final class Assets {
    private LoaderHelper $loader;

    public function __construct( ?LoaderHelper $loader = null ) {
        $this->loader = $loader ?? new LoaderHelper();
    }

    public function register(): void {
        $this->loader->register_component( $this, [
            [ 'type' => 'action', 'hook' => 'admin_enqueue_scripts', 'callback' => 'enqueue_admin_assets' ],
        ] )->run();
    }

    public function enqueue_admin_assets( string $hook_suffix = '' ): void {
        $this->enqueue_fontawesome_vendor_assets( $hook_suffix );
        $this->enqueue_icon_picker();
    }

    private function enqueue_fontawesome_vendor_assets( string $hook_suffix ): void {
        $page = sanitize_key( $_GET['page'] ?? '' );
        if ( false === strpos( $hook_suffix, 'pluginname' ) && 0 !== strpos( $page, 'pluginname' ) ) {
            return;
        }

        $source = FontAwesomeSettings::source();
        $kit_id = FontAwesomeSettings::kit_id();
        $use_kit = '' !== $kit_id;

        if ( $use_kit ) {
            foreach ( [ 'font-awesome-kit', 'font-awesome-cdn' ] as $handle ) {
                wp_dequeue_style( $handle );
                wp_dequeue_script( $handle );
            }
        }

        wp_add_inline_script(
            'pluginname-admin-ui',
            'window.pluginnameFontAwesomeSettings = ' . wp_json_encode( [
                'source' => $source,
                'kit_id' => $kit_id,
            ] ) . ';',
            'before'
        );

        if ( $use_kit ) {
            return;
        }

        $handle = 'kit' === $source ? 'font-awesome-kit' : 'font-awesome-cdn';
        if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
                wp_enqueue_style( $handle );
        }

        if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
            wp_enqueue_script( $handle );
        }
    }

    public function enqueue_icon_picker(): void {
        if ( ! $this->should_enqueue_icon_picker() ) {
            return;
        }

        wp_enqueue_style(
            'pluginname-fontawesome-icon-picker',
            WIKIPRESS_URL . 'src/includes/Plugins/FontAwesome/Assets/dist/css/icon-picker.css',
            [],
            WIKIPRESS_VERSION
        );
        wp_enqueue_script(
            'pluginname-fontawesome-icon-picker',
            WIKIPRESS_URL . 'src/includes/Plugins/FontAwesome/Assets/dist/js/icon-picker.js',
            [ 'jquery' ],
            WIKIPRESS_VERSION,
            true
        );

        wp_localize_script( 'pluginname-fontawesome-icon-picker', 'pluginname_fa_picker', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pluginname_fontawesome_picker' ),
            'strings' => [
                'search_placeholder' => __( 'Search icons...', 'pluginname' ),
                'no_icons_found' => __( 'No icons found', 'pluginname' ),
                'loading' => __( 'Loading...', 'pluginname' ),
                'select_icon' => __( 'Select Icon', 'pluginname' ),
                'close' => __( 'Close', 'pluginname' ),
            ],
        ] );
    }

    private function should_enqueue_icon_picker(): bool {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return false;
        }

        return strpos( $screen->id, 'pluginname' ) !== false
            || in_array( $screen->id, [ 'post', 'page', 'custom_css', 'customize' ], true );
    }
}