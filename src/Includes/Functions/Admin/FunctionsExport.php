<?php
/**
 * Export-related admin functions for PluginName.
 *
 * @package PluginName
 * @subpackage Includes\Functions\Admin
 * @since 1.0.0
 */
namespace PluginName\Includes\Functions\Admin;

use PluginName\Includes\Tools\DataTransfer;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class FunctionsExport {

    /**
     * Export PluginName data as a JSON file.
     *
     * @return void
     */
    public function export_data(): void {
        if ( ! current_user_can( 'pluginname_tools_export' ) ) {
            wp_die( esc_html__( 'You are not allowed to export PluginName data.', 'pluginname' ), 403 );
        }
        check_admin_referer( 'pluginname_export' );
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=pluginname-export-' . gmdate( 'Y-m-d' ) . '.json' );
        echo wp_json_encode( DataTransfer::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        exit;
    }
}