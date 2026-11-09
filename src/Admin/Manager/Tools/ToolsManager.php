<?php
/**
 * ToolsManager class for PluginName plugin.
 *
 * @package PluginName
 */
namespace PluginName\Admin\Manager\Tools;

use PluginName\Admin\Manager\Manager;
use PluginName\Admin\Manager\Tools\Debug;
use PluginName\Admin\Manager\Tools\Reset;
use PluginName\Admin\Manager\Tools\Import;
use PluginName\Admin\Manager\Tools\Export;
use PluginName\Admin\Manager\Tools\General;
use PluginName\Admin\Manager\Tools\Licencing;
use PluginName\Assets\Assets;
use PluginName\Includes\Functions\Helpers\RequestHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Includes\Functions\Helpers\PermissionHelper;


class ToolsManager extends Manager {
	/**
	 * The Page variable.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $page The page variable.
	 */
	protected string $page;

	/**
	 * `Constructor` method for the `ToolsManager` class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct( bool $initialize_tools = true ) {
		$this->page = 'tools';
	}
	/**
	 * Renders the tools page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render(): void {
		$tool = $this->resolve_requested_tool();
		$capabilities = array(
			'debug'  => 'pluginname_tools_debug',
			'reset'  => 'pluginname_tools_reset',
			'import' => 'pluginname_tools_import',
			'export' => 'pluginname_tools_export',
			'general' => 'pluginname_tools_general',
			'licencing' => 'pluginname_tools_licencing',
		);
		if ( ! PermissionHelper::can( $capabilities[ $tool ] ) ) {
			wp_die( esc_html__( 'You are not authorized to access this PluginName tool.', 'pluginname' ) );
		}
		$this->header( $this->title( $tool ) );
		$screen = $this->resolve_tool_screen( $tool );
		if ( null !== $screen ) {
			$screen->render_page_content();
		}
		$this->footer();
	}

	/**
	 * Resolve the active requested tool from either the legacy `tool` query or the
	 * menu-provided `tab` value.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function resolve_requested_tool(): string {
		$tool = RequestHelper::get_key( 'tool', '' );
		if ( '' === $tool ) {
			$tool = RequestHelper::get_key( 'tab', 'debug' );
		}

		$valid_tools = array( 'debug', 'reset', 'import', 'export', 'general', 'licencing' );
		return in_array( $tool, $valid_tools, true ) ? $tool : 'debug';
	}

	/**
	 * Resolve a tool screen lazily to avoid recursive child manager construction.
	 *
	 * @param string $tool Tool slug.
	 * @return object|null
	 * @since 1.0.0
	 */
	private function resolve_tool_screen( string $tool ): ?object {
		switch ( $tool ) {
			case 'debug':
				return new Debug();
			case 'import':
				return new Import();
			case 'general':
				return new General();
			case 'reset':
				return new Reset();
			case 'export':
				return new Export();
			case 'licencing':
				return new Licencing();
		}
	}
	/**
	 * Registers the assets for the tools page.
	 *
	 * @since 1.0.0
	 * @param Assets $assets The Assets instance.
	 * @return void
	 */
	public function register_assets( Assets $assets ): void {
		$this->register_page_assets( $assets, array( 'pluginname' ), 'tools' );
	}
	/**
	 * Returns the title for the given tool.
	 *
	 * @since 1.0.0
	 * @param string $tool The tool name.
	 * @return string The title for the tool.
	 */
	private function title( string $tool ): string {
		return array(
			'debug'  => __( 'Debug', 'pluginname' ),
			'reset'  => __( 'Reset', 'pluginname' ),
			'import' => __( 'Import', 'pluginname' ),
			'export' => __( 'Export', 'pluginname' ),
			'general' => __( 'General', 'pluginname' ),
			'licencing' => __( 'Licencing', 'pluginname' ),
		)[ $tool ];
	}
}




