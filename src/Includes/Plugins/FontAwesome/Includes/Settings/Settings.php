<?php
/**
 * Settings for the Font Awesome PluginName plugin.
 * 
 * @package    PluginName
 * @subpackage PluginName/includes
 */
namespace PluginName\Includes\Plugins\FontAwesome\Includes\Settings;
use PluginName\Includes\Settings\Settings as BaseSettings;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;

final class Settings {
    /**
     * Registers the Font Awesome settings group.
     *
     * @since 1.0.0
     */
    public function register(): void {
        BaseSettings::register_group( 'fontawesome', [
            'fontawesome_type' => 'cdn',
            'fontawesome_cdn_technology' => 'webfont',
            'fontawesome_kit_id' => '',
            'fontawesome_enable_icon_picker' => true,
        ] );
    }
    /**
     * Determines whether the icon picker is enabled.
     *
     * @return bool True if the icon picker is enabled, false otherwise.
     * @since 1.0.0
     */
    public static function enable_icon_picker(): bool {
        return BaseSettings::get_bool( 'fontawesome_enable_icon_picker', true );
    }

    /**
     * Retrieves the selected Font Awesome source type.
     *
     * @return string The source type.
     */
    public static function get_type(): string {
        $type = BaseSettings::get_key( 'fontawesome_type', 'cdn' );
        return in_array( $type, [ 'cdn', 'kit' ], true ) ? $type : 'cdn';
    }

    /**
     * Retrieves the configured Font Awesome kit ID.
     *
     * @return string The Font Awesome kit ID.
     */
    public static function get_kit_id(): string {
        return BaseSettings::get_string( 'fontawesome_kit_id', '' );
    }

    /**
     * Retrieves the selected Font Awesome CDN technology.
     *
     * @return string The CDN technology.
     */
    public static function get_cdn_technology(): string {
        $technology = BaseSettings::get_key( 'fontawesome_cdn_technology', 'webfont' );
        return in_array( $technology, [ 'webfont', 'svg' ], true ) ? $technology : 'webfont';
    }
    /**
     * Retrieves the settings page configuration for the Font Awesome plugin.
     *
     * @return array The settings page configuration.
     * @since 1.0.0
     */
    public function get_settings_page(): array {
        return [
            'slug' => 'fontawesome',
            'label' => __( 'FontAwesome', 'pluginname' ),
            'title' => __( 'FontAwesome integration', 'pluginname' ),
            'layout' => 'table',
            'fields' => [
                [
                    'key' => 'fontawesome_type',
                    'label' => __( 'FontAwesome Type', 'pluginname' ),
                    'description' => __( 'Select how fontawesome is called in PluginName.', 'pluginname' ),
                    'tooltip' => __( 'Choose between using the CDN or a Kit for FontAwesome.', 'pluginname' ),
                    'tooltip_type' => 'info',
                    'type' => 'select',
                    'class' => 'w-100',
                    'options' => [
                        'cdn' => __( 'CDN', 'pluginname' ),
                        'kit' => __( 'Kit', 'pluginname' ),
                    ],
                    'default' => 'cdn',
                ],
                [
                    'key' => 'fontawesome_cdn_technology',
                    'label' => __( 'FontAwesome CDN Technology', 'pluginname' ),
                    'description' => __( 'Select the technology used for the Font Awesome CDN.', 'pluginname' ),
                    'tooltip' => __( 'Choose between different CDN technologies for loading Font Awesome.', 'pluginname' ),
                    'tooltip_type' => 'info',
                    'type' => 'select',
                    'class' => 'w-100',
                    'options' => [
                        'svg' => __( 'SVG', 'pluginname' ),
                        'webfont' => __( 'Web Font', 'pluginname' ),
                    ],
                    'default' => 'webfont',
                    'visible_when' => [
                        'fontawesome_type' => 'cdn',
                    ],
                    'required' => [
                        'fontawesome_type' => 'cdn',
                    ],

                ],
                [
                    'key' => 'fontawesome_kit_id',
                    'label' => __( 'FontAwesome Kit ID', 'pluginname' ),
                    'description' => __( 'Enter the ID of your Font Awesome Kit if you selected "Kit" as the type.', 'pluginname' ),
                    'tooltip' => __( 'The FontAwesome Kit ID is required when using the Kit type.', 'pluginname' ),
                    'tooltip_type' => 'info',
                    'type' => 'text',
                    'class' => 'w-100',
                    'default' => '',
                    'visible_when' => [
                        'fontawesome_type' => 'kit',
                    ],
                    'required' => [
                        'fontawesome_type' => 'kit',
                    ],
                ],
                [
                    'key' => 'fontawesome_enable_icon_picker',
                    'label' => __( 'Enable icon picker', 'pluginname' ),
                    'description' => __( 'Enable the Font Awesome icon picker inside PluginName admin interfaces.', 'pluginname' ),
                    'tooltip' => __( 'This only controls the PluginName icon picker UI. The official Font Awesome admin bundle is still enqueued for the PluginName screens.', 'pluginname' ),
                    'tooltip_type' => 'info',
                    'type' => 'checkbox',
                    'default' => true,
                ],
            ],
        ];
    }
    /**
     * Sanitizes the input for the Font Awesome settings.
     *
     * @param array $input The input array to sanitize.
     * @return array The sanitized input array.
     * @since 1.0.0
     */
    public function sanitize( $input ): array {
        $input = is_array( $input ) ? $input : [];

        $type = SanitizationHelper::key( $input['fontawesome_type'] ?? 'cdn', 'cdn' );
        $input['fontawesome_type'] = in_array( $type, [ 'cdn', 'kit' ], true ) ? $type : 'cdn';

        if ( 'kit' === $input['fontawesome_type'] ) {
            $input['fontawesome_kit_id'] = trim( (string) ( $input['fontawesome_kit_id'] ?? '' ) );
        } else {
            $input['fontawesome_kit_id'] = '';
        }

        $input['fontawesome_enable_icon_picker'] = (bool) ( $input['fontawesome_enable_icon_picker'] ?? true );
        BaseSettings::set_group( 'fontawesome', $input );
        return $input;
    }
}

