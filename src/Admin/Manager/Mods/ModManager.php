<?php
/**
 * Base manager for PluginName mod admin screens.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Mods
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Mods;

use PluginName\Admin\Manager\Manager;
use PluginName\Assets\Assets;
use PluginName\Includes\Functions\Helpers\PermissionHelper;
use PluginName\Includes\Functions\Helpers\RequestHelper;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

final class ModManager extends Manager {
    /**
     * The page slug for the mod manager group.
     *
     * @var string
     */
    protected string $page = 'mods';

    /**
     * Supported mod tabs.
     *
     * @var array<int, string>
     */
    protected array $tabs = array( 'dashboard', 'groups', 'changelog', 'files', 'mods' );

    /**
     * Render the mod manager shell.
     *
     * @return void
     */
    public function render(): void {
        $tab = $this->normalize_tab( RequestHelper::get_key( 'tab', 'dashboard' ) );
        $capabilities = array(
            'dashboard' => 'pluginname_mods_dashboard',
            'groups'    => 'pluginname_mods_groups',
            'changelog' => 'pluginname_mods_changelog',
            'files'     => 'pluginname_mods_files',
            'mods'      => 'pluginname_mods_manage',
        );

        if ( ! PermissionHelper::can( $capabilities[ $tab ] ?? 'pluginname_mods_dashboard' ) ) {
            wp_die( esc_html__( 'You are not authorized to access this PluginName mod screen.', 'pluginname' ) );
        }

        $this->header( $this->title( $tab ) );
        $this->render_tab_content( $tab );
        $this->footer();
    }

    /**
     * Render the content for a specific tab.
     *
     * @param string $tab Tab slug.
     * @return void
     */
    public function render_tab_content( string $tab ): void {
        $tab = $this->normalize_tab( $tab );

        $instances = array(
            'dashboard' => new ModDashboard(),
            'groups'    => new ModGroups(),
            'changelog' => new ModChangeLogs(),
            'files'     => new ModFiles(),
            'mods'      => new Mods(),
        );

        if ( ! isset( $instances[ $tab ] ) ) {
            return;
        }

        $instance = $instances[ $tab ];
        if ( method_exists( $instance, 'render_page_content' ) ) {
            $instance->render_page_content();
            return;
        }

        if ( method_exists( $instance, 'render' ) ) {
            $instance->render();
        }
    }

    /**
     * Render the specific page content for a screen class.
     *
     * @return void
     */
    public function render_page_content(): void {
    // Intentionally left for concrete screen classes.
    }

    /**
     * Normalize a route alias.
     *
     * @param string $tab Raw tab.
     * @return string
     */
    protected function normalize_tab( string $tab ): string {
        $aliases = array(
            'overview'   => 'dashboard',
            'dashboard'  => 'dashboard',
            'manage'     => 'mods',
            'mod'        => 'mods',
            'groups'     => 'groups',
            'changelog'  => 'changelog',
            'change-log' => 'changelog',
            'files'      => 'files',
            'file'       => 'files',
            'list'       => 'mods',
            );

        return $this->normalize_route( $tab, $aliases, 'dashboard' );
    }

    /**
     * Registers admin assets for the mod manager.
     *
     * @param Assets $assets Asset registry.
     * @return void
     */
    public function register_assets( Assets $assets ): void {
        $this->register_page_assets( 
            $assets, 
            array( 'pluginname-mods' ), 
            'mods' 
        );
    }

    /**
     * Get the page title for a given tab.
     *
     * @param string $tab Tab slug.
     * @return string
     */
    protected function title( string $tab ): string {
        $labels = array(
            'dashboard' => __( 'Mod Dashboard', 'pluginname' ),
            'groups'    => __( 'Mod Groups', 'pluginname' ),
            'changelog' => __( 'Change Logs', 'pluginname' ),
            'files'     => __( 'Mod Files', 'pluginname' ),
            'mods'      => __( 'Mods', 'pluginname' ),
        );

        return $labels[ $tab ] ?? $labels['dashboard'];
    }
}

