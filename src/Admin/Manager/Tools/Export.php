<?php
/**
 * Export class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Tools;

use PluginName\Includes\Functions\Admin\FunctionsExport;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Functions\Helpers\UrlHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Export extends ToolsManager {
	/**
	 * Export handler instance.
	 *
	 * @var FunctionsExport
	 */
	private FunctionsExport $export_functions;

	/**
	 * Constructor for the Export class.
	 *
	 * @return void
	 */
	public function __construct( ?LoaderHelper $loader = null ) {
		parent::__construct( false );
		$this->export_functions = new FunctionsExport();
		( $loader ?? new LoaderHelper() )->register_component(
			$this->export_functions,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_pluginname_export',
					'callback' => 'export_data',
				),
			)
		)->run();
	}
	/**
	 * Render the export form.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<h2 class="h5"><?php esc_html_e( 'Export PluginName data', 'pluginname' ); ?></h2>
				<p class="text-secondary"><?php esc_html_e( 'Download a protected JSON export of PluginName records, customer meta, and validation data.', 'pluginname' ); ?></p>
				<?php echo wp_kses_post(
					FormFieldHelper::button(
						esc_html__( 'Export PluginName JSON', 'pluginname' ),
						array(
							'href'  => UrlHelper::admin_action_nonce( 'pluginname_export', 'pluginname_export' ),
							'class' => 'btn-outline-primary',
						)
					)
				); ?>
			</div>
		</div>
		<?php
	}
	/**
	 * Render the export page content.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<tr>
			<th scope="row"><?php echo wp_kses_post(
				FormFieldHelper::label(
					'pluginname-export',
					esc_html__( 'Import and export', 'pluginname' ),
					array(
						'description' => __( 'Export or import PluginName data as a password-protected JSON archive.', 'pluginname' ),
						'tooltip'     => __( 'Exports are protected with a WordPress nonce and should use a password whenever shared with partners.', 'pluginname' ),
					)
				)
			); ?></th>
			<td><?php echo wp_kses_post(
				FormFieldHelper::button(
					esc_html__( 'Export PluginName JSON', 'pluginname' ),
					array(
						'href'  => UrlHelper::admin_action_nonce( 'pluginname_export', 'pluginname_export' ),
						'class' => 'btn-outline-primary',
					)
				)
			); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php echo FormFieldHelper::label(
				'pluginname-database-manager',
				esc_html__( 'Database manager', 'pluginname' ),
				array(
					'description' => __( 'PluginName records are kept in the plugin database tables and managed through the core lifecycle.', 'pluginname' ),
					'tooltip'     => __( 'Manual database changes are not required for normal PluginName operations.', 'pluginname' ),
				)
			); ?></th>
			<td><?php esc_html_e( 'Managed automatically', 'pluginname' ); ?></td>
		</tr>
		<?php
	}
}




