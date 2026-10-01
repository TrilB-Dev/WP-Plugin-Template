<?php
/**
 * Settings access restriction fields.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsAccess extends SettingsManager {
	/**
	 * Normalize the current values for the default-prefixed general settings fields.
	 *
	 * @param array<string, mixed> $values The incoming values.
	 * @return array<string, mixed> The normalized values.
	 */
	private function normalize_values( array $values ): array {
		return $values;
	}
	/**
	 * Render the access restriction settings fields.
	 *
	 * @param array $values The current values for the access restriction settings fields.
	 * @since 1.0.0
	 */
	public function render_page_content( array $values ): void {
		$values = $this->normalize_values( $values );
		$manage_users    = is_array( $values['issue_licences'] ?? null ) ? $values['issue_licences'] : array( $values['issue_licences'] ?? 'manage_options' );
		$manage_roles    = is_array( $values['revoke_licences'] ?? null ) ? $values['revoke_licences'] : array( $values['revoke_licences'] ?? 'manage_options' );
		$export_users    = is_array( $values['export_data'] ?? null ) ? $values['export_data'] : array( $values['export_data'] ?? 'manage_options' );
		$review_security = is_array( $values['review_security'] ?? null ) ? $values['review_security'] : array( $values['review_security'] ?? 'manage_options' );
		$role_options    = array(
			array( 
				'value' => 'manage_options', 
				'label' => __( 'Administrators', 'pluginname' ) 
			),
			array( 
				'value' => 'edit_posts', 
				'label' => __( 'Editors', 'pluginname' ) 
			),
			array( 
				'value' => 'publish_posts', 
				'label' => __( 'Authors', 'pluginname' ) 
			),
		);
		?>
		<form method="post" action="" class="pluginname-settings-form">
			<?php echo FormFieldHelper::input( 
				'action', 
				'pluginname_save_access_settings', 
				array( 
					'type' => 'hidden' 
				) 
			); ?>
			<?php echo FormFieldHelper::input( 
				'pluginname_access_nonce', 
				wp_create_nonce( 'pluginname_access' ), 
				array( 
					'type' => 'hidden' 
				) 
			); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-manage-users',
								__( 'Who can manage users?', 'pluginname' ),
								array(
									'description' => __( 'Minimum capability required to create, edit, and deactivate user accounts.', 'pluginname' ),
									'tooltip'     => __( 'Restrict user management to trusted roles only.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'pluginname_access[issue_licences]',
								array(
									'data' => $role_options,
									'selected' => sanitize_key( (string) ( $manage_users[0] ?? 'manage_options' ) ),
									'id' => 'pluginname-access-manage-users',
									'live_search' => true,
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-manage-roles',
								__( 'Who can manage user roles?', 'pluginname' ),
								array(
									'description' => __( 'Minimum capability required to assign or change WordPress roles for users.', 'pluginname' ),
									'tooltip'     => __( 'Role changes are sensitive and should be restricted to site administrators.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'pluginname_access[revoke_licences]',
								array(
									'data' => $role_options,
									'selected' => sanitize_key( (string) ( $manage_roles[0] ?? 'manage_options' ) ),
									'id' => 'pluginname-access-manage-roles',
									'live_search' => true,
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-export-users',
								__( 'Who can export user data?', 'pluginname' ),
								array(
									'description' => __( 'Minimum capability required to export user records and account data.', 'pluginname' ),
									'tooltip'     => __( 'Exports should be limited to trusted administrators and data managers.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'pluginname_access[export_data]',
								array(
									'data' => $role_options,
									'selected' => sanitize_key( (string) ( $export_users[0] ?? 'manage_options' ) ),
									'id' => 'pluginname-access-export-users',
									'live_search' => true,
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-access-review-activity',
								__( 'Who can review activity logs?', 'pluginname' ),
								array(
									'description' => __( 'Minimum capability required to inspect account validation and security activity.', 'pluginname' ),
									'tooltip'     => __( 'Security review should be restricted to administrators or trusted compliance roles.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'pluginname_access[review_security]',
								array(
									'data' => $role_options,
									'selected' => sanitize_key( (string) ( $review_security[0] ?? 'manage_options' ) ),
									'id' => 'pluginname-access-review-activity',
									'live_search' => true,
								)
							); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="mt-3 d-flex justify-content-end">
				<?php echo FormFieldHelper::button(
					__( 'Save Settings', 'pluginname' ),
					array(
						'type'  => 'submit',
						'class' => 'btn-primary',
						'name'  => 'pluginname_save_settings',
					)
				); ?>
			</div>
		</form>
		<?php
	}
}





