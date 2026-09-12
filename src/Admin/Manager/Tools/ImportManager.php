<?php
/**
 * ImportManager class for PluginName plugin.
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

final class ImportManager extends Manager {
	/**
	 * Render the JSON import form below the tools settings form.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<form
			method="post"
			action="<?php echo esc_url( UrlHelper::admin_action( 'pluginname_import' ) ); ?>"
			enctype="multipart/form-data"
			class="card pluginname-import-form shadow-sm mt-4"
		>
			<?php echo wp_kses_post( FormFieldHelper::input( 'action', 'pluginname_import', array( 'type' => 'hidden' ) ) ); ?>
			<?php wp_nonce_field( 'pluginname_import' ); ?>
			<div class="card-body">
				<?php
				echo wp_kses_post(
					FormFieldHelper::label(
						'pluginname-import-file',
						__( 'Import licence JSON', 'pluginname' ),
						array(
							'description'  => __( 'Select a JSON export containing PluginName records, customer data, and validation metadata.', 'pluginname' ),
							'tooltip'      => __( 'Import should use a valid archive and, where required, a password-protected file to preserve security.', 'pluginname' ),
							'tooltip_icon' => 'fa-file-import',
						)
					)
				);
				echo wp_kses_post(
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
				echo wp_kses_post(
					FormFieldHelper::button(
						__( 'Import JSON', 'pluginname' ),
						array(
							'type'  => 'submit',
							'class' => 'btn-primary',
						)
					)
				);
				?>
			</div>
		</form>
		<?php
	}
}



