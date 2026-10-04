<?php
/**
 * Assets class for FontAwesome plugin.
 *
 * @package PluginName\Includes\Plugins\FontAwesome\Assets
 */
namespace PluginName\Includes\Plugins\FontAwesome\Assets;

use PluginName\Assets\Assets as RootAssets;
use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Plugins\FontAwesome\Includes\Settings\Settings as FontAwesomeSettings;

final class Assets extends RootAssets {
    /**
     * Registers the asset filters for the Font Awesome plugin.
     *
     * @since 1.0.0
     */
    public function register(): void {
        ( new LoaderHelper() )->register_component(
            $this,
            array(
                array(
                    'type' => 'filter',
                    'hook' => 'pluginname_admin_assets',
                    'callback' => 'register_admin_assets',
                    'accepted_args' => 2,
                ),
                array(
                    'type' => 'filter',
                    'hook' => 'pluginname_frontend_assets',
                    'callback' => 'register_frontend_assets',
                    'accepted_args' => 2,
                ),
            )
        )->run();
    }

    /**
     * Register Font Awesome asset definitions for the admin context.
     *
     * @param array  $assets The existing asset bundle.
     * @param string $context The current asset context.
     * @return array The updated asset bundle.
     */
    public function register_admin_assets( array $assets, string $context = 'admin' ): array {
        if ( 'admin' !== $context ) {
            return $assets;
        }

        $type = FontAwesomeSettings::get_type();
        $kit_id = trim( FontAwesomeSettings::get_kit_id() );

        if ( 'kit' === $type && '' !== $kit_id ) {
            $assets['scripts'][] = array(
                'handle' => 'pluginname-fontawesome-kit',
                'src' => 'https://kit.fontawesome.com/' . rawurlencode( $kit_id ) . '.js',
                'deps' => array( 'pluginname-bootstrap' ),
                'in_footer' => true,
            );
        } else {
            $assets['styles'][] = array(
                'handle' => 'pluginname-fontawesome-cdn-style',
                'src' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/css/fontawesome.min.css',
                'deps' => array( 'pluginname-bootstrap' ),
                'version' => '7.0.0',
            );
            $assets['scripts'][] = array(
                'handle' => 'pluginname-fontawesome-cdn-script',
                'src' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/js/fontawesome.min.js',
                'deps' => array( 'pluginname-bootstrap' ),
                'version' => '7.0.0',
                'in_footer' => true,
            );
        }

        if ( FontAwesomeSettings::enable_icon_picker() ) {
            $assets['styles'][] = array(
                'handle' => 'pluginname-fontawesome-icon-picker',
                'src' => PLUGINNAME_URL . 'src/Includes/Plugins/FontAwesome/Assets/dist/css/icon-picker.css',
                'deps' => array( 'pluginname-bootstrap' ),
                'version' => PLUGINNAME_VERSION,
            );
            $assets['scripts'][] = array(
                'handle' => 'pluginname-fontawesome-icon-picker',
                'src' => PLUGINNAME_URL . 'src/Includes/Plugins/FontAwesome/Assets/dist/js/icon-picker.js',
                'deps' => array( 'jquery' ),
                'version' => PLUGINNAME_VERSION,
                'in_footer' => true,
                'localize' => array(
                    'object_name' => 'pluginname_fa_picker',
                    'data' => array(
                        'ajax_url' => admin_url( 'admin-ajax.php' ),
                        'nonce' => wp_create_nonce( 'pluginname_fontawesome_picker' ),
                        'strings' => array(
                            'search_placeholder' => __( 'Search icons...', 'pluginname' ),
                            'no_icons_found' => __( 'No icons found', 'pluginname' ),
                            'loading' => __( 'Loading...', 'pluginname' ),
                            'select_icon' => __( 'Select Icon', 'pluginname' ),
                            'close' => __( 'Close', 'pluginname' ),
                        ),
                    ),
                ),
            );
        }

        return $assets;
    }

    /**
     * Register Font Awesome asset definitions for the frontend context.
     *
     * @param array  $assets The existing asset bundle.
     * @param string $context The current asset context.
     * @return array The updated asset bundle.
     */
    public function register_frontend_assets( array $assets, string $context = 'frontend' ): array {
        if ( 'frontend' !== $context ) {
            return $assets;
        }

        $type = FontAwesomeSettings::get_type();
        $kit_id = trim( FontAwesomeSettings::get_kit_id() );

        if ( 'kit' === $type && '' !== $kit_id ) {
            $assets['scripts'][] = array(
                'handle' => 'pluginname-fontawesome-kit',
                'src' => 'https://kit.fontawesome.com/' . rawurlencode( $kit_id ) . '.js',
                'deps' => array(),
                'in_footer' => true,
            );
            return $assets;
        }

        $assets['styles'][] = array(
            'handle' => 'pluginname-fontawesome-cdn-style',
            'src' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/css/fontawesome.min.css',
            'deps' => array(),
            'version' => '7.0.0',
        );
        $assets['scripts'][] = array(
            'handle' => 'pluginname-fontawesome-cdn-script',
            'src' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7/js/fontawesome.min.js',
            'deps' => array(),
            'version' => '7.0.0',
            'in_footer' => true,
        );

        return $assets;
    }
}


