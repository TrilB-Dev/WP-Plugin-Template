<?php
/**
 * Layout settings fields.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsLayout extends SettingsManager {
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
	 * Render the layout settings fields.
	 *
	 * @param array $values The current values for the layout settings fields.
	 * @since 1.0.0
	 */
	public function render_page_content( array $values ): void {
		$values = $this->normalize_values( $values );
		$login_title = is_scalar( $values['login_registration_title'] ?? '' ) ? (string) $values['login_registration_title'] : ( get_bloginfo( 'name' ) ?: __( 'PluginName', 'pluginname' ) );
		$site_logo   = is_scalar( $values['site_logo'] ?? '' ) ? (string) $values['site_logo'] : '';
		$user_menu   = (string) ( $values['frontend_user_menu'] ?? 'horizontal' );
		$ajax_profile = ! empty( $values['ajax_submission_user_profile'] );
		$elementor_integration = ! empty( $values['elementor_integration'] );
		$user_menu_options = array(
			'horizontal' => __( 'Horizontal', 'pluginname' ),
			'vertical'   => __( 'Vertical', 'pluginname' ),
		);
		?>
		<form method="post" action="" class="pluginname-settings-form">
			<?php echo FormFieldHelper::input( 
				'action', 
				'pluginname_save_layout_settings', 
				array( 
					'type' => 'hidden' 
				) 
			); ?>
			<?php echo FormFieldHelper::input( 
				'pluginname_layout_nonce', 
				wp_create_nonce( 'pluginname_layout' ), 
				array( 
					'type' => 'hidden' 
				) 
			); ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-layout-login-registration-title',
								__( 'Login & Registration Title', 'pluginname' ),
								array(
									'description' => __( 'Set the title displayed across the frontend login and registration experience.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input(
								'pluginname_layout[login_registration_title]',
								$login_title,
								array(
									'id' => 'pluginname-layout-login-registration-title',
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-layout-site-logo',
								__( 'Site Logo', 'pluginname' ),
								array(
									'description' => __( 'Choose a logo for the frontend member UI.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<div class="input-group">
								<?php echo FormFieldHelper::text_input(
									'pluginname_layout[site_logo]',
									$site_logo,
									array(
										'id' => 'pluginname-layout-site-logo',
										'placeholder' => __( 'Select a logo image', 'pluginname' ),
									)
								); ?>
								<?php echo FormFieldHelper::button(
									__( 'Select image', 'pluginname' ),
									array(
										'type' => 'button',
										'class' => 'btn btn-outline-secondary pluginname-media-button',
										'data-target' => 'pluginname-layout-site-logo',
									)
								); ?>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-layout-frontend-user-menu',
								__( 'Frontend user UI menu', 'pluginname' ),
								array(
									'description' => __( 'Choose whether the user menu is displayed horizontally or vertically.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::bootstrap_select(
								'pluginname_layout[frontend_user_menu]',
								array(
									'data' => $user_menu_options,
									'selected' => $user_menu,
									'id' => 'pluginname-layout-frontend-user-menu',
									'live_search' => true,
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-layout-ajax-submission-user-profile',
								__( 'Ajax Submission user profile', 'pluginname' ),
								array(
									'description' => __( 'Submit profile updates via AJAX without a full page reload.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::switch(
								'pluginname_layout[ajax_submission_user_profile]',
								'1',
								__( 'Ajax Submission user profile', 'pluginname' ),
								array(
									'id' => 'pluginname-layout-ajax-submission-user-profile',
									'checked' => $ajax_profile,
								)
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label(
								'pluginname-layout-elementor-integration',
								__( 'Elementor Integration', 'pluginname' ),
								array(
									'description' => __( 'Enable integration with Elementor.', 'pluginname' ),
									'tooltip' => __( 'Enable integration with Elementor to create your own custom templates.', 'pluginname' ),
								)
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::switch(
								'pluginname_layout[elementor_integration]',
								'1',
								__( 'Elementor Integration', 'pluginname' ),
								array(
									'id' => 'pluginname-layout-elementor-integration',
									'checked' => $elementor_integration,
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


