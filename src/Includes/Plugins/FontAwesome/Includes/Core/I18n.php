<?php
/**
 * This file manages the internationalization functionality of the plugin.
 * 
 * 
 * 
 * @package PluginName\Includes\Plugins\FontAwesome\Includes
 * @since 1.0.0
 */

namespace PluginName\Includes\Plugins\FontAwesome\Includes\Core;

final class I18n {
    public static function load_textdomain(): void {
        load_plugin_textdomain(
            'pluginname',
            false,
            dirname( plugin_basename( PLUGINNAME_FILE ) ) . '/src/Includes/Plugins/FontAwesome/Language/'
        );
    }
}