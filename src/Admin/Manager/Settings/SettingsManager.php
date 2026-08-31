<?php
/**
 * SettingsManager class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Admin\Manager\Manager;
use PluginName\Assets\Assets;
use PluginName\Includes\Settings\Settings;
use PluginName\Admin\Manager\Settings\SettingsPlugins;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class SettingsManager extends Manager {
    /**
     * The SettingsGeneral instance.
     *
     * @since 1.0.0
     * @access private
     * @var SettingsGeneral $general_page The SettingsGeneral instance.
     */
    private SettingsGeneral $general_page;
    /**
     * The SettingsLayout instance.
     *
     * @since 1.0.0
     * @access private
     * @var SettingsLayout $layout_page The SettingsLayout instance.
     */
    private SettingsLayout $layout_page;
    /**
     * The SettingsAccess instance.
     *
     * @since 1.0.0
     * @access private
     * @var SettingsAccess $access_page The SettingsAccess instance.
     */
    private SettingsAccess $access_page;
    /**
     * The SettingsPlugins instance.
     *
     * @since 1.0.0
     * @access private
     * @var SettingsPlugins $plugins_page The SettingsPlugins instance.
     */
    private SettingsPlugins $plugins_page;

    /**
     * The Page variable.
     *
     * @since 1.0.0
     * @access protected
     * @var string $page The page variable.
     */
    protected $page;
    /**
     * `Constructor` method for the `DashboardManager` class. 
     *
     * @since 1.0.0
     * @return void
     */

    public function __construct() {
        /**
         * Set the page variable to 'dashboard'.
         *
         * @since 1.0.0
         */
        $this->page = 'dashboard';
        /**
         * Initialize the General Settings pages.
         *
         * @since 1.0.0
         */
        $this->general_page = new SettingsGeneral();
        /**
         * Initialize the Layout Settings pages.
         *
         * @since 1.0.0
         */
        $this->layout_page = new SettingsLayout();
        /**
         * Initialize the Access Settings page.
         *
         * @since 1.0.0
         */
        $this->access_page = new SettingsAccess();
        /**
         * Initialize the Plugins Settings pages.
         *
         * @since 1.0.0
         */
        $this->plugins_page = new SettingsPlugins();
    }
    /**
     * Renders the settings page.
     *
     * @since 1.0.0
     * @return void
     */
    public function render(): void {
        $tab = SanitizationHelper::key( wp_unslash( $_GET['tab'] ?? 'general' ), 'general' );
        $layout_section = SanitizationHelper::key( wp_unslash( $_GET['layout_section'] ?? 'general' ), 'general' );
        $tab = $this->normalize_tab( $tab );
        $tab_context = [
            'general' => [ 'description' => __( 'Configure PluginName names, URL slugs, and permalink settings.', 'pluginname' ), 'tooltip' => __( 'These settings affect how PluginName content is identified and linked throughout the site.', 'pluginname' ) ],
            'layout' => [ 'description' => __( 'Choose which navigation and page layout features PluginName displays.', 'pluginname' ), 'tooltip' => __( 'Layout settings control the visitor-facing PluginName interface.', 'pluginname' ) ],
            'access' => [ 'description' => __( 'Set the minimum WordPress capabilities required for PluginName tasks.', 'pluginname' ), 'tooltip' => __( 'Choose carefully so editors and administrators retain the access they need.', 'pluginname' ) ],
            'plugins' => [ 'description' => __( 'View the PluginName plugins installed on this site.', 'pluginname' ), 'tooltip' => __( 'Plugin-specific configuration is available from each plugin settings page when provided.', 'pluginname' ) ],
            'third-party' => [ 'description' => __( 'View third-party plugins installed on this site.', 'pluginname' ), 'tooltip' => __( 'Third-party plugin settings are managed through WordPress or the plugin author’s own settings page.', 'pluginname' ) ],
        ];
        $this->header( __( 'Settings', 'pluginname' ) );
        echo '<div id="pluginname-settings-panel" data-current-tab="' . esc_attr( $tab ) . '" data-current-section="' . esc_attr( $layout_section ) . '">';
        $this->render_tab_content( $tab, $layout_section );
        echo '</div>';
        $this->footer();
    }

    /**
     * Render the settings panel returned by the AJAX tab loader.
     * @since 1.0.0
     * @param string $tab The tab to render.
     */
    public function render_tab_content( string $tab, string $layout_section = 'general' ): void {
        $tab = $this->normalize_tab( $tab );
        $view_capabilities = [
            'general' => [ 'pluginname_settings_general_view' ],
            'layout' => [ 'pluginname_settings_layout_view' ],
            'access' => [ 'pluginname_settings_access_view' ],
            'plugins' => [ 'pluginname_settings_plugins_view' ],
            'third-party' => [ 'pluginname_settings_plugins_view', 'pluginname_settings_plugins_ext_view' ],
        ];
        $can_view = true;
        foreach ( $view_capabilities[ $tab ] ?? [] as $capability ) {
            if ( ! current_user_can( $capability ) ) {
                $can_view = false;
                break;
            }
        }
        if ( ! $can_view ) {
            wp_die( esc_html__( 'You are not authorized to view these PluginName settings.', 'pluginname' ) );
        }
        $edit_capabilities = [
            'general' => 'pluginname_settings_general_edit',
            'layout' => 'pluginname_settings_layout_edit',
            'access' => 'pluginname_settings_access_edit',
        ];
        $can_edit = isset( $edit_capabilities[ $tab ] ) && current_user_can( $edit_capabilities[ $tab ] );
        $groups = Settings::get_all();
        $values = $groups[ $tab ] ?? [];
        echo '<div class="pluginname-settings-tab-content" role="tabpanel">';
        $tab_context = [
            'general' => [ 'description' => __( 'Configure PluginName names, URL slugs, and permalink settings.', 'pluginname' ), 'tooltip' => __( 'These settings affect how PluginName content is identified and linked throughout the site.', 'pluginname' ) ],
            'layout' => [ 'description' => __( 'Choose which navigation and page layout features PluginName displays.', 'pluginname' ), 'tooltip' => __( 'Layout settings control the visitor-facing PluginName interface.', 'pluginname' ) ],
            'access' => [ 'description' => __( 'Set the minimum WordPress capabilities required for PluginName tasks.', 'pluginname' ), 'tooltip' => __( 'Choose carefully so editors and administrators retain the access they need.', 'pluginname' ) ],
            'plugins' => [ 'description' => __( 'View the PluginName plugins installed on this site.', 'pluginname' ), 'tooltip' => __( 'Plugin-specific configuration is available from each plugin settings page when provided.', 'pluginname' ) ],
            'third-party' => [ 'description' => __( 'View third-party plugins installed on this site.', 'pluginname' ), 'tooltip' => __( 'Third-party plugin settings are managed through WordPress or the plugin author’s own settings page.', 'pluginname' ) ],
        ];
        if ( isset( $tab_context[ $tab ] ) ) {
            echo '<p class="text-secondary mb-4">' . esc_html( $tab_context[ $tab ]['description'] ) . ' ' . FormFieldHelper::label( 'pluginname-settings-context', __( 'Settings information', 'pluginname' ), [ 'tooltip' => $tab_context[ $tab ]['tooltip'], 'tooltip_type' => 'info', 'tooltip_icon' => 'fa-circle-info', 'class' => 'visually-hidden' ] ) . '</p>';
        }
        if ( in_array( $tab, [ 'plugins', 'third-party' ], true ) ) {
            $this->plugins_page->render( $tab );
            echo '</div>';
            return;
        }
        if ( $can_edit ) {
            echo '<form method="post" action="options.php" class="pluginname-settings-form card shadow-sm">';
        } else {
            echo '<div class="pluginname-settings-form card shadow-sm">';
        }
        settings_fields( 'pluginname_settings' );
        echo '<div class="card-body">';
        if ( 'layout' === $tab ) {
            $this->layout_page->render( $values, SanitizationHelper::key( $layout_section, 'general' ) );
        } else {
            echo '<table class="form-table table align-middle"><tbody>';
        }
        if ( 'general' === $tab ) {
            $this->general_page->render( $values );
        } elseif ( 'access' === $tab ) {
            $this->access_page->render( $values );
        } elseif ( $this->plugins_page->has_settings_page( $tab ) ) {
            $this->plugins_page->render_settings_page( $tab, $values );
        }
        if ( 'layout' !== $tab ) {
            echo '</tbody></table>';
        }
        if ( $can_edit ) {
            echo FormFieldHelper::button( __( 'Save Changes', 'pluginname' ), [
                'type' => 'submit',
                'name' => 'submit',
                'class' => 'btn-primary',
            ] );
        }
        echo '</div>' . ( $can_edit ? '</form>' : '' );
        echo '</div>';
    }
    /**
     * Normalize the tab name to ensure it is valid.
     *
     * @since 1.0.0
     * @param string $tab The tab name to normalize.
     * @return string The normalized tab name.
     */
    private function normalize_tab( string $tab ): string {
        $allowed = [ 'general', 'layout', 'access', 'tools', 'plugins', 'third-party' ];
        if ( in_array( $tab, $allowed, true ) || $this->plugins_page->has_settings_page( $tab ) ) {
            return $tab;
        }
        return 'general';
    }
    /**
     * Register assets for the settings page.
     *
     * @since 1.0.0
     * @param Assets $assets The Assets instance to register assets with.
     */
    public function register_assets( Assets $assets ): void {
        $settings_assets = $this->assets( 'settings' );
        $settings_assets['scripts'][] = [
            'handle' => 'pluginname-admin-plugins',
            'src' => WIKIPRESS_URL . 'src/Assets/dist/js/admin.plugins.js',
            'deps' => [ 'pluginname-bootstrap' ],
            'in_footer' => true,
        ];
        $assets->register_page( 'pluginname-settings', $settings_assets );
    }

}
