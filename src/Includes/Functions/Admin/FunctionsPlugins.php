<?php
/**
 * Plugin-related admin functions for PluginName.
 *
 * @package PluginName
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace PluginName\Includes\Functions\Admin;

use PluginName\Includes\Functions\Helpers\AjaxHelper;
use PluginName\Includes\Functions\Helpers\AlertHelper;
use PluginName\Includes\Plugins\PluginInterface;
use PluginName\Includes\Plugins\Plugins;
use PluginName\Includes\Plugins\SettingsPageProviderInterface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class FunctionsPlugins {
    /**
     * Toggle the enabled state of a PluginName plugin.
     *
     * @return void
     */
    public function toggle_plugin(): void {
        if ( ! AjaxHelper::authorized( 'pluginname_plugin_toggle', 'pluginname_settings_plugins_int_edit' ) ) {
            AjaxHelper::unauthorized( __( 'You are not authorized to manage PluginName plugins.', 'pluginname' ) );
        }

        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $enabled = ! empty( $_POST['enabled'] );
        $plugin = Plugins::get_instance()->get_registered_plugins()[ $slug ] ?? null;

        if ( ! $plugin instanceof PluginInterface ) {
            AjaxHelper::error( [ 'message' => __( 'The requested PluginName plugin was not found.', 'pluginname' ) ], 404 );
        }
		if ( ! $this->is_internal_plugin( $plugin ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to manage external PluginName plugins.', 'pluginname' ) );
		}

        if ( ! Plugins::get_instance()->set_plugin_enabled( $slug, $enabled ) ) {
            AjaxHelper::error( [ 'message' => __( 'The PluginName plugin state could not be saved.', 'pluginname' ) ], 500 );
        }

        AjaxHelper::success( [ 'slug' => $slug, 'enabled' => $enabled ] );
    }

    /**
     * Save settings submitted from a PluginName plugin modal.
     *
     * @return void
     */
    public function save_plugin_settings(): void {
        $slug = sanitize_key( wp_unslash( $_POST['slug'] ?? '' ) );
        $plugin = Plugins::get_instance()->get_registered_plugins()[ $slug ] ?? null;
        if ( ! $plugin instanceof PluginInterface || ! $plugin instanceof SettingsPageProviderInterface ) {
            $message = __( 'The requested PluginName plugin settings were not found.', 'pluginname' );
            AjaxHelper::error( [ 'message' => $message, 'alert' => AlertHelper::get_admin_notice( $message, 'error' ) ], 404 );
        }
        $capability = $this->is_internal_plugin( $plugin ) ? 'pluginname_settings_plugins_int_edit' : 'pluginname_settings_plugins_ext_edit';
        if ( ! AjaxHelper::authorized( 'pluginname_plugin_settings', $capability ) ) {
            $message = __( 'You are not authorized to save PluginName plugin settings.', 'pluginname' );
            AjaxHelper::error( [ 'message' => $message, 'alert' => AlertHelper::get_admin_notice( $message, 'error' ) ], 403 );
        }

        $input = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];
        $settings = $plugin->sanitize_settings( $input );

        AjaxHelper::success(
            [
                'slug' => $slug,
                'settings' => $settings,
                'message' => __( 'Plugin settings saved successfully.', 'pluginname' ),
                'alert' => AlertHelper::get_admin_notice( __( 'Plugin settings saved successfully.', 'pluginname' ), 'success' ),
            ]
        );
    }

    private function is_internal_plugin( PluginInterface $plugin ): bool {
        return 0 === strpos( get_class( $plugin ), 'PluginName\\Includes\\Plugins\\' );
    }

    /**
     * Collect settings pages from enabled PluginName plugins.
     *
     * @return array<int, array{provider: SettingsPageProviderInterface, slug: string, label: string, title: string, fields: array}>
     */
    public function plugin_settings_pages(): array {
        $pages = [];
        foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
            if ( ! $plugin instanceof PluginInterface || ! $plugin instanceof SettingsPageProviderInterface || ! Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() ) ) {
                continue;
            }

            $page = $plugin->get_settings_page();
            if ( empty( $page['slug'] ) || empty( $page['label'] ) || empty( $page['fields'] ) ) {
                continue;
            }

            $page['provider'] = $plugin;
            $pages[] = $page;
        }
        return $pages;
    }
}
