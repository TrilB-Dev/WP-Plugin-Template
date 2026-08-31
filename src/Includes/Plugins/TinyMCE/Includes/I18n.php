<?php
/**
 * Language internationalization (i18n) for the TinyMCE plugin.
 * @package PluginName
 * @subpackage Plugins\TinyMCE\Includes
 * @since 1.0.0
 * 
 */
namespace PluginName\Includes\Plugins\TinyMCE\Includes;

class I18n {
    /**
     * Loads the plugin's text domain for translation.
     */
    public static function load_textdomain(): void {
        load_plugin_textdomain(
            'pluginname',
            false,
            dirname( plugin_basename( WIKIPRESS_FILE ) ) . '/src/includes/Plugins/TinyMCE/Language/'
        );
    }
}