<?php
/**
 * Settings for the Font Awesome PluginName plugin.
 * 
 * @package    PluginName
 * @subpackage PluginName/Includes
 */
namespace PluginName\Includes\Plugins\FontAwesome\Includes\Settings;
use PluginName\Includes\Settings\Settings as BaseSettings;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;

final class Settings {
    public function register(): void {
        BaseSettings::register_group( 'fontawesome', [
            'fontawesome_source' => 'base',
            'fontawesome_kit_id' => '',
            'fontawesome_version' => '7.0.0',
        ] );
    }

    public static function source(): string {
        $source = BaseSettings::get_key( 'fontawesome_source', 'base' );
        return in_array( $source, [ 'base', 'kit' ], true ) ? $source : 'base';
    }

    public static function kit_id(): string {
        return BaseSettings::get_string( 'fontawesome_kit_id' );
    }

    public static function version(): string {
        return BaseSettings::get_string( 'fontawesome_version', '7.0.0' );
    }

    public function get_settings_page(): array {
        return [
            'slug' => 'fontawesome',
            'settings_group' => 'fontawesome',
            'label' => __( 'Font Awesome', 'pluginname' ),
            'title' => __( 'Font Awesome integration', 'pluginname' ),
            'layout' => 'table',
            'fields' => [
                [ 
                    'key' => 'fontawesome_source',
                    'label' => __( 'Icon source', 'pluginname' ),
                    'description' => __( 'Choose how PluginName loads Font Awesome icons.', 'pluginname' ),
                    'tooltip' => __( 'Use the base package for the bundled icons or a Kit when you need a custom Font Awesome configuration.', 'pluginname' ),
                    'tooltip_type' => 'info',
                    'type' => 'select',
                    'options' => [ 'base' => __( 'Base package', 'pluginname' ),
                    'kit' => __( 'Font Awesome Kit', 'pluginname' ) ],
                    'default' => 'base' 
                ],
                [ 
                    'key' => 'fontawesome_kit_id',
                    'label' => __( 'Kit ID', 'pluginname' ),
                    'description' => __( 'Enter the ID of your Font Awesome Kit.', 'pluginname' ),
                    'tooltip' => __( 'This value is used only when Icon source is set to Font Awesome Kit.', 'pluginname' ),
                    'type' => 'text',
                    'default' => '' 
                ],
                [ 
                    'key' => 'fontawesome_version',
                    'label' => __( 'Base package version', 'pluginname' ),
                    'description' => __( 'Set the version of the bundled Font Awesome package to load.', 'pluginname' ),
                    'tooltip' => __( 'Use a version supported by the installed Font Awesome assets.', 'pluginname' ),
                    'tooltip_type' => 'info',
                    'type' => 'text',
                    'default' => '7.0.0'
                ],
            ],
        ];
    }

    public function sanitize( $input ): array {
        $input = is_array( $input ) ? $input : [];
        $source = SanitizationHelper::key( $input['fontawesome_source'] ?? 'base', 'base' );
        $input['fontawesome_source'] = in_array( $source, [ 'base', 'kit' ], true ) ? $source : 'base';
        $input['fontawesome_kit_id'] = SanitizationHelper::text( $input['fontawesome_kit_id'] ?? '' );
        $input['fontawesome_version'] = SanitizationHelper::text( $input['fontawesome_version'] ?? '7.0.0', '7.0.0' );
        BaseSettings::set_group( 'fontawesome', $input );
        return $input;
    }
}