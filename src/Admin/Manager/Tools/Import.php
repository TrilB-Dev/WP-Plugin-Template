<?php
/**
 * Import class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Tools;

use PluginName\Includes\Functions\Admin\FunctionsImport;
use PluginName\Includes\Functions\Helpers\AlertHelper;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Functions\Helpers\UrlHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Import extends ToolsManager {
	/**
	 * Import handler instance.
	 *
	 * @var FunctionsImport
	 */
	private FunctionsImport $import_functions;

	/**
	 * Render the JSON import form below the tools settings form.
	 *
	 * @return void
	 */
	public function __construct( ?LoaderHelper $loader = null ) {
		parent::__construct( false );
		$this->import_functions = new FunctionsImport();
		( $loader ?? new LoaderHelper() )->register_component(
			$this->import_functions,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_pluginname_import',
					'callback' => 'import_data',
				),
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_pluginname_import_group_defaults',
					'callback' => 'import_group_defaults',
				),
			)
		)->run();
	}
	/**
	 * Render the import page content using the shared manager contract.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		$this->render();
	}

	/**
	 * Render the import form.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( '1' === (string) ( $_GET['default_groups_imported'] ?? '' ) ) {
			AlertHelper::render_admin_notice( \__( 'The default PluginName groups and taxonomy terms were imported successfully.', 'pluginname' ), 'success' );
		}
		if ( '1' === (string) ( $_GET['default_groups_import_failed'] ?? '' ) ) {
			AlertHelper::render_admin_notice( \__( 'The default PluginName groups could not be imported.', 'pluginname' ), 'error' );
		}
		if ( '1' === (string) ( $_GET['default_file_types_imported'] ?? '' ) ) {
			AlertHelper::render_admin_notice( \__( 'The default PluginName file types were imported successfully.', 'pluginname' ), 'success' );
		}
		if ( '1' === (string) ( $_GET['default_mod_statuses_imported'] ?? '' ) ) {
			AlertHelper::render_admin_notice( \__( 'The default PluginName mod statuses were imported successfully.', 'pluginname' ), 'success' );
		}
		?>
		<form
			method="post"
			action="<?php echo \esc_url( UrlHelper::admin_action( 'pluginname_import' ) ); ?>"
			enctype="multipart/form-data"
			class="card pluginname-import-form shadow-sm mt-4"
		>
			<?php echo \wp_kses_post( FormFieldHelper::input( 'action', 'pluginname_import', array( 'type' => 'hidden' ) ) ); ?>
			<?php \wp_nonce_field( 'pluginname_import' ); ?>
			<div class="card-body">
				<?php
				echo \wp_kses_post(
					FormFieldHelper::label(
						'pluginname-import-file',
						\__( 'Import PluginName JSON', 'pluginname' ),
						array(
							'description'  => \__( 'Select a JSON export containing PluginName records, customer data, and validation metadata.', 'pluginname' ),
							'tooltip'      => \__( 'Import should use a valid archive and, where required, a password-protected file to preserve security.', 'pluginname' ),
							'tooltip_icon' => 'fa-file-import',
						)
					)
				);
				echo \wp_kses_post(
					FormFieldHelper::input(
						'pluginname_import_file',
						'',
						array(
							'id'       => 'pluginname-import-file',
							'type'     => 'file',
							'class'    => 'mb-3',
							'accept'   => 'application/json,.json',
							'required' => true,
						)
					)
				);
				echo \wp_kses_post(
					FormFieldHelper::button(
						\__( 'Import PluginName JSON', 'pluginname' ),
						array(
							'type'  => 'submit',
							'class' => 'btn-primary',
						)
					)
				);
				?>
			</div>
		</form>
		<div class="card pluginname-import-form shadow-sm mt-4">
			<form
				method="post"
				action="<?php echo \esc_url( UrlHelper::admin_action( 'pluginname_import_group_defaults' ) ); ?>"
			>
				<?php \wp_nonce_field( 'pluginname_import_group_defaults', 'pluginname_import_group_defaults_nonce' ); ?>
				<input type="hidden" name="action" value="pluginname_import_group_defaults" />
				<input type="hidden" name="import_target" value="pluginname_file_type" />
				<div class="card-body">
					<h3 class="h5 mb-3"><?php \esc_html_e( 'Import File Type Defaults', 'pluginname' ); ?></h3>
					<p class="text-secondary mb-3"><?php \esc_html_e( 'This will clear the existing default file types and rebuild the default file types table from scratch.', 'pluginname' ); ?></p>
					<?php echo \wp_kses_post(
						FormFieldHelper::button(
							\__( 'Import File Types', 'pluginname' ),
							array(
								'type'  => 'submit',
								'class' => 'btn-warning',
							)
						)
					); ?>
				</div>
			</form>
		</div>
		<div class="card pluginname-import-form shadow-sm mt-4">
			<form
				method="post"
				action="<?php echo \esc_url( UrlHelper::admin_action( 'pluginname_import_group_defaults' ) ); ?>"
			>
				<?php \wp_nonce_field( 'pluginname_import_group_defaults', 'pluginname_import_group_defaults_nonce' ); ?>
				<input type="hidden" name="action" value="pluginname_import_group_defaults" />
				<input type="hidden" name="import_target" value="pluginname_mod_status" />
				<div class="card-body">
					<h3 class="h5 mb-3"><?php \esc_html_e( 'Import Status Defaults', 'pluginname' ); ?></h3>
					<p class="text-secondary mb-3"><?php \esc_html_e( 'This will clear the existing default status terms and rebuild the default statuses table from scratch.', 'pluginname' ); ?></p>
					<?php echo \wp_kses_post(
						FormFieldHelper::button(
							\__( 'Import Statuses', 'pluginname' ),
							array(
								'type'  => 'submit',
								'class' => 'btn-warning',
							)
						)
					); ?>
				</div>
			</form>
		</div> <!-- Close card for Import Statuses -->

		<div class="card pluginname-import-form shadow-sm mt-4">
			<div class="card-body">
				<h3 class="h5 mb-3"><?php \esc_html_e( 'Import Group Defaults', 'pluginname' ); ?></h3>
				<p class="text-secondary mb-3"><?php \esc_html_e( 'This will clear the existing default taxonomy terms and rebuild the default groups table from scratch.', 'pluginname' ); ?></p>
				<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#pluginname-import-default-groups-modal">
					<?php \esc_html_e( 'Import Group Defaults', 'pluginname' ); ?>
				</button>
			</div>
		</div>

		<div class="modal fade" id="pluginname-import-default-groups-modal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<form method="post" action="<?php echo \esc_url( UrlHelper::admin_action( 'pluginname_import_group_defaults' ) ); ?>">
					<?php \wp_nonce_field( 'pluginname_import_group_defaults', 'pluginname_import_group_defaults_nonce' ); ?>
					<input type="hidden" name="action" value="pluginname_import_group_defaults" />
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title"><?php \esc_html_e( 'Import Group Defaults', 'pluginname' ); ?></h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php \esc_attr_e( 'Close', 'pluginname' ); ?>"></button>
						</div>
						<div class="modal-body">
							<p><?php \esc_html_e( 'By importing the group defaults, all PluginName data entered by the user will be deleted and cannot be undone.', 'pluginname' ); ?></p>
							<?php echo FormFieldHelper::checkbox(
								'confirm_default_reset',
								'1',
								\__( 'I understand this will delete all user-entered data and cannot be undone.', 'pluginname' ),
								array(
									'id' => 'pluginname-confirm-default-reset',
								)
							); ?>
						</div>
						<div class="modal-footer d-flex justify-content-between">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php \esc_html_e( 'Cancel', 'pluginname' ); ?></button>
							<button type="submit" class="btn btn-danger"><?php \esc_html_e( 'Import', 'pluginname' ); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<?php
	}
}




