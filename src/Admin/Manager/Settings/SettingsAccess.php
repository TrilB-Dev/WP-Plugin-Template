<?php
/**
 * Settings access restriction fields.
 *
 * @package TrilBDev
 * @subpackage Admin\Manager\Settings
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsAccess {
	/**
	 * Render access restriction fields.
	 *
	 * @param array<string, mixed> $values Current settings.
	 * @return void
	 */
	public function render( array $values ): void {
		$can_edit = current_user_can( 'manage_options' ) || current_user_can( 'pluginname_settings_access_edit' );
		$options = [
			[ 'value' => 'manage_options', 'label' => __( 'Administrators', 'pluginname' ) ],
			[ 'value' => 'edit_posts', 'label' => __( 'Editors', 'pluginname' ) ],
			[ 'value' => 'publish_posts', 'label' => __( 'Authors', 'pluginname' ) ],
		];
		$create_mods = $values['create_mods'] ?? [ 'manage_options' ];
		$write_pages = $values['write_pages'] ?? [ 'manage_options' ];
		$view_analytics = $values['view_analytics'] ?? [ 'manage_options' ];
		$manage_plugins = $values['manage_plugins'] ?? [ 'manage_options' ];
		?>
		<?php if ( $can_edit ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pluginname-settings-form">
			<input type="hidden" name="action" value="pluginname_save_access_settings" />
			<input type="hidden" name="pluginname_tab" value="access" />
			<?php wp_nonce_field( 'pluginname_save_access_settings' ); ?>
		<?php endif; ?>
			<table class="form-table table align-middle" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-create-mods',
								__( 'Who can create mods?', 'pluginname' ),
								[
									'description' => __( 'Choose the minimum capability required to create PluginName mods.', 'pluginname' ),
									'tooltip' => __( 'Users without this capability cannot create new mods.', 'pluginname' ),
								]
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_multiselect(
								'pluginname_access[create_mods]',
								[
									'id' => 'pluginname-access-create-mods',
									'data' => $options,
									'selected' => array_values( 
										array_filter( 
											array_map( 
												'sanitize_key', 
												(array) $create_mods 
											) 
										) 
									),
								]
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-write-pages',
								__( 'Who can write mod pages?', 'pluginname' ),
								[
									'description' => __( 'Choose the minimum capability required to create or edit mod pages.', 'pluginname' ),
									'tooltip' => __( 'This controls editing access to mod page content.', 'pluginname' ),
								]
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_multiselect(
								'pluginname_access[write_pages]',
								[
									'id' => 'pluginname-access-write-pages',
									'data' => $options,
									'selected' => array_values( 
										array_filter( 
											array_map( 
												'sanitize_key', 
												(array) $write_pages 
											) 
										) 
									),
								]
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-view-analytics',
								__( 'Who can check analytics?', 'pluginname' ),
								[
									'description' => __( 'Choose the minimum capability required to view PluginName analytics.', 'pluginname' ),
									'tooltip' => __( 'Analytics data is shown only to users who meet this capability.', 'pluginname' ),
									'tooltip_type' => 'info',
								]
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_multiselect(
								'pluginname_access[view_analytics]',
								[
									'id' => 'pluginname-access-view-analytics',
									'data' => $options,
									'selected' => array_values( 
										array_filter( 
											array_map( 
												'sanitize_key', 
												(array) $view_analytics 
											) 
										) 
									),
								]
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-manage-plugins',
								__( 'Who can manage plugins?', 'pluginname' ),
								[
									'description' => __( 'Choose the minimum capability required to manage PluginName plugins.', 'pluginname' ),
									'tooltip' => __( 'Use a trusted administrator-level capability for plugin management.', 'pluginname' ),
									'tooltip_icon' => 'fa-shield-halved',
								]
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_multiselect(
								'pluginname_access[manage_plugins]',
								[
									'id' => 'pluginname-access-manage-plugins',
									'data' => $options,
									'selected' => array_values( 
										array_filter( 
											array_map( 
												'sanitize_key', 
												(array) $manage_plugins 
											) 
										) 
									),
								]
							); ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php if ( $can_edit ) : ?>
			<div class="pluginname-settings-actions d-flex justify-content-end mt-3">
				<?php echo FormFieldHelper::button(
					__( 'Save Changes', 'pluginname' ),
					[
						'type' => 'submit',
						'name' => 'submit',
						'class' => 'btn-primary',
					]
				); ?>
			</div>
		</form>
		<?php endif; ?>
		<?php
	}
}


