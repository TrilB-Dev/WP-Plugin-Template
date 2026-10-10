<?php
/**
 * Font Awesome plugin integration for PluginName.
 *
 * @package FontAwesome
 * @textdomain pluginname
 * @domainpath Languages
 * @author PluginName Team
 */

namespace PluginName\Includes\Plugins\FontAwesome;

use PluginName\Includes\Plugins\AssetsProviderInterface;
use PluginName\Includes\Plugins\I18nProviderInterface;
use PluginName\Includes\Plugins\PluginInterface;
use PluginName\Includes\Plugins\SettingsProviderInterface;
use PluginName\Includes\Plugins\SettingsPageProviderInterface;
use PluginName\Includes\Plugins\FontAwesome\Assets\Assets;
use PluginName\Includes\Plugins\FontAwesome\Includes\IconPicker;
use PluginName\Includes\Plugins\FontAwesome\API\FontAwesomeAPI;
use PluginName\Includes\Plugins\FontAwesome\Includes\Core\I18n;
use PluginName\Includes\Plugins\FontAwesome\Includes\Includes;
use PluginName\Includes\Core\WP\Activator;

final class FontAwesome implements PluginInterface, SettingsProviderInterface, SettingsPageProviderInterface, AssetsProviderInterface, I18nProviderInterface {
    /**
     * Singleton instance of the FontAwesome plugin.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * IconPicker instance for the FontAwesome plugin.
     *
     * @var IconPicker|null
     */
    private ?IconPicker $icon_picker = null;

    /**
     * Get the plugin slug.
     *
     * @return string
     */
    public function get_slug(): string {
        return 'pluginname-fontawesome';
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function get_name(): string {
        return 'FontAwesome';
    }

    /**
     * Get the plugin icon.
     *
     * @return array{0: string, 1: string}
     */
    public function get_icon(): array {
        return [ 'fab fa-font-awesome', '#74c1fcff' ];
    }

    /**
     * Get the plugin version.
     *
     * @return string
     */
    public function get_version(): string {

        return '1.0.0';
    }

    /**
     * Get the plugin author.
     *
     * @return string
     */
    public function get_author(): string {
        return 'TrilB.Dev Team';
    }

    /**
     * Get the plugin author URI.
     *
     * @return string
     */
    public function get_author_uri(): string {
        return 'https://trilb.dev/';
    }

    /**
     * Get the plugin description.
     *
     * @return string
     */
    public function get_description(): string {
        return __( 'Provides FontAwesome CDN & Kit enqueueing in Admin, Frontend & Login Page, also adds an enhanced icon picker, and styling APIs for PluginName.', 'pluginname' );
    }

    /**
     * Get the plugin URI.
     *
     * @return string
     */
    public function get_uri(): string {
        return 'https://trilb.dev/collection/web-extension/wordpress/pluginname';
    }

    /**
     * Get the plugin license.
     *
     * @return string
     */
    public function get_license(): string {
        return 'GPL-2.0-or-later';
    }

    /**
     * Check if the plugin is active.
     *
     * @return bool
     */
    public function is_active(): bool {
        return true;
    }

        /**
     * Private constructor to prevent direct instantiation.
     *
     * @since 1.0.0
     */
    private function __construct() {
    }
    /**
     * Initialize the FontAwesome plugin.
     *
     * @return void
     * @since 1.0.0
     */
    public function init(): void {

        FontAwesomeAPI::configure();
        $this->icon_picker = IconPicker::get_instance();
        Includes::get_instance()->init();
    }

    /**
     * Register plugin settings.
     *
     * @return void
     * @since 1.0.0
     */
    public function register_settings(): void {
        Includes::get_instance()->settings()->register();
    }

    /**
     * Get the settings page config.
     *
     * @return array
     * @since 1.0.0
     */
    public function get_settings_page(): array {
        return Includes::get_instance()->settings()->get_settings_page();
    }

    /**
     * Sanitize plugin settings.
     *
     * @param mixed $input
     * @return array
     * @since 1.0.0
     */
    public function sanitize_settings( $input ): array {
        return Includes::get_instance()->settings()->sanitize( $input );
    }

    /**
     * Register plugin assets.
     *
     * @return void
     * @since 1.0.0
     */
    public function register_assets(): void {
        ( new Assets() )->register();
    }

    /**
     * Load the text domain.
     *
     * @return void
     * @since 1.0.0
     */
    public function load_textdomain(): void {
        I18n::load_textdomain();
    }

    /**
     * Get the IconPicker instance.
     *
     * @return IconPicker|null
     * @since 1.0.0
     */
    public function get_icon_picker(): ?IconPicker {
        return $this->icon_picker;
    }

    /**
     * Get the singleton instance.
     *
     * @return self
     * @since 1.0.0
     */
    public static function get_instance(): self {
        return self::$instance ??= new self();
    }
}


