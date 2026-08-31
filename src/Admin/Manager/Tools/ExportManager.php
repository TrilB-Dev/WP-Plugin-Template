<?php
/**
 * ExportManager class for PluginName plugin.
 * 
 * @package PluginName
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Tools;

use PluginName\Admin\Manager\Manager;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\UrlHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class ExportManager extends Manager {
    /**
     * Render the JSON export form below the tools settings form.
     *
     * @return void
     */
    public function __construct() {
    }
    /**
     * Render the JSON export form below the tools settings form.
     *
     * @return void
     */
    public function render_page_content(): void {
        echo '<div class="card shadow-sm"><div class="card-body"><h2 class="h5">' . esc_html__( 'Export PluginName data', 'pluginname' ) . '</h2><p class="text-secondary">' . esc_html__( 'Download your PluginName content and settings as a JSON file.', 'pluginname' ) . '</p>';
        echo wp_kses_post( FormFieldHelper::button( esc_html__( 'Export PluginName JSON', 'pluginname' ), [ 'href' => UrlHelper::admin_action_nonce( 'pluginname_export', 'pluginname_export' ), 'class' => 'btn-outline-primary' ] ) );
        echo '</div></div>';
    }

    /**
     * Render export and database tool fields.
     *
     * @return void
     */
    public function render(): void {
        echo '<tr><th scope="row">' . wp_kses_post( FormFieldHelper::label( 'pluginname-export', esc_html__( 'Import and export', 'pluginname' ), [ 'description' => __( 'Export or import PluginName content and settings as JSON.', 'pluginname' ), 'tooltip' => __( 'Exports are protected with a WordPress nonce.', 'pluginname' ) ] ) ) . '</th><td>' . wp_kses_post( FormFieldHelper::button( esc_html__( 'Export PluginName JSON', 'pluginname' ), [ 'href' => UrlHelper::admin_action_nonce( 'pluginname_export', 'pluginname_export' ), 'class' => 'btn-outline-primary' ] ) ) . '</td></tr>';
        echo '<tr><th scope="row">' . FormFieldHelper::label( 'pluginname-database-manager', esc_html__( 'Database manager', 'pluginname' ), [ 'description' => __( 'The settings table is managed automatically during plugin activation.', 'pluginname' ), 'tooltip' => __( 'Manual database changes are not required for normal PluginName operation.', 'pluginname' ) ] ) . '</th><td>' . esc_html__( 'Managed automatically', 'pluginname' ) . '</td></tr>';
    }
}