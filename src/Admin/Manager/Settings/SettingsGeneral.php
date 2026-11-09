<?php
/**
 * Settings general fields.
 * @package PluginName
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\PermalinkHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsGeneral {
	/**
	 * Render general PluginName settings fields.
	 *
	 * @param array<string, mixed> $values Current settings.
	 * @return void
	 * @since 1.0.0
	 */
	public function render( array $values ): void {
		$edit_capabilities = array(
			'general' => 'pluginname_settings_general_edit',
		);
		$can_edit = isset( $edit_capabilities[ 'general' ] ) && current_user_can( $edit_capabilities[ 'general' ] );
		$root_name = isset( $values['root_name'] ) ? SanitizationHelper::text( (string) $values['root_name'] ) : '';
		$root_description = isset( $values['root_description'] ) ? SanitizationHelper::text( (string) $values['root_description'] ) : '';
		$archive_title = isset( $values['archive_title'] ) ? SanitizationHelper::text( (string) $values['archive_title'] ) : '';
		$archive_description = isset( $values['archive_description'] ) ? SanitizationHelper::text( (string) $values['archive_description'] ) : '';
		$root_slug = isset( $values['root_slug'] ) ? SanitizationHelper::key( (string) $values['root_slug'] ) : '';
		$category_slug = isset( $values['category_slug'] ) ? SanitizationHelper::key( (string) $values['category_slug'] ) : '';
		$tag_slug = isset( $values['tag_slug'] ) ? SanitizationHelper::key( (string) $values['tag_slug'] ) : '';
		$permalink = isset( $values['permalink'] ) ? PermalinkHelper::sanitize_pattern( (string) $values['permalink'] ) : '';
		$token_definitions = array(
			'%mod_root%'   => __( 'The root of PluginName.', 'pluginname' ),
			'%mod_type%'   => __( 'The Mod type taxonomy.', 'pluginname' ),
			'%mod_tags%'   => __( 'The Mod tags taxonomy.', 'pluginname' ),
			'%mod_groups%' => __( 'The custom Mod taxonomies.', 'pluginname' ),
			'%mod_post%'   => __( 'The Mod post type.', 'pluginname' ),
		);
		$enable_schema = ! empty( $values['enable_schema'] ?? true );
		?>
		<?php if ( $can_edit ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pluginname-settings-form">
			<input type="hidden" name="action" value="pluginname_save_general_settings" />
			<input type="hidden" name="pluginname_tab" value="general" />
			<?php wp_nonce_field( 'pluginname_save_general_settings' ); ?>
		<?php endif; ?>
			<table class="form-table table align-middle" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-root-name', 
								__( 'PluginName Root Name', 'pluginname' ), 
								[
									'description' => __( 'The name used for the main PluginName area.', 'pluginname' ),
									'tooltip' => __( 'This name appears in the admin interface and generated titles.', 'pluginname' ),
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'pluginname_general[root_name]', 
								$root_name, 
								[ 
									'id' => 'pluginname-root-name',
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-root-description', 
								__( 'PluginName Description', 'pluginname' ), 
								[
									'description' => __( 'A short description for the PluginName knowledge base.', 'pluginname' ),
									'tooltip' => __( 'This can be used by themes and integrations when describing the PluginName area.', 'pluginname' ),
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::textarea( 
								'pluginname_general[root_description]', 
								$root_description, 
								[ 
									'id' => 'pluginname-root-description', 
									'rows' => 3,
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-archive-title', 
								__( 'PluginName Archive Title', 'pluginname' ), 
								[
									'description' => __( 'The title shown on PluginName archive and index views.', 'pluginname' ),
									'tooltip' => __( 'Use a concise title that makes the documentation area clear to visitors.', 'pluginname' ),
								] ); ?></th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'pluginname_general[archive_title]', 
								$archive_title, 
								[ 
									'id' => 'pluginname-archive-title',
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-archive-description', 
								__( 'PluginName Archive Description', 'pluginname' ), 
								[
									'description' => __( 'Supporting text shown on PluginName archive and index views.', 'pluginname' ),
									'tooltip' => __( 'A short introduction helps visitors understand what they can find in the PluginName area.', 'pluginname' ),
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::textarea( 
								'pluginname_general[archive_description]', 
								$archive_description, 
								[ 
									'id' => 'pluginname-archive-description', 
									'rows' => 3,
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-root-slug', 
								__( 'PluginName Root Slug', 'pluginname' ), 
								[
									'description' => __( 'The URL slug for the PluginName root.', 'pluginname' ),
									'tooltip' => __( 'Use lowercase letters, numbers, and hyphens for the most reliable URLs.', 'pluginname' ),
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'pluginname_general[root_slug]', 
								$root_slug, 
								[ 
									'id' => 'pluginname-root-slug',
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-category-slug', 
								__( 'Custom Category Slug', 'pluginname' ), 
								[
									'description' => __( 'The URL slug used for PluginName categories.', 'pluginname' ),
									'tooltip' => __( 'Changing this value flushes the WordPress rewrite rules.', 'pluginname' ),
									'tooltip_type' => 'info',
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'pluginname_general[category_slug]', 
								$category_slug, 
								[ 
									'id' => 'pluginname-category-slug',
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-tag-slug', 
								__( 'Custom Tags Slug', 'pluginname' ), 
								[
									'description' => __( 'The URL slug used for PluginName tags.', 'pluginname' ),
									'tooltip' => __( 'Changing this value flushes the WordPress rewrite rules.', 'pluginname' ),
									'tooltip_type' => 'info',
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'pluginname_general[tag_slug]', 
								$tag_slug, 
								[ 
									'id' => 'pluginname-tag-slug',
									'class' => 'w-100',
								] 
							); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-permalink', 
								__( 'PluginName Permalink', 'pluginname' ), 
								[
									'description' => __( 'The permalink structure used by PluginName content.', 'pluginname' ),
									'tooltip' => __( 'Choose a structure that remains readable and stable after publication.', 'pluginname' ),
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::text_input( 
								'pluginname_general[permalink]', 
								$permalink, 
								[ 
									'id' => 'pluginname-permalink', 
									'data-permalink-field' => 'permalink',
									'class' => 'w-100',
								] 
							); ?>
							<div class="pluginname-permalink-tokens mt-2" aria-label="<?php echo esc_attr__( 'Available permalink tokens', 'pluginname' ); ?>">
								<?php foreach ( $token_definitions as $token => $description ) : ?>
									<?php echo FormFieldHelper::button(
										$token,
										[
											'class' => 'btn-sm btn-outline-secondary me-1 mb-1',
											'type' => 'button',
											'attributes' => [
												'data-permalink-token' => $token,
												'title' => $description,
											],
										]
									); ?>
								<?php endforeach; ?>
							</div>
							<div class="form-text"><?php echo esc_html__( 'Click a token to add it to the pattern. Tokens are inserted with a trailing slash and reappear when removed.', 'pluginname' ); ?></div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php echo FormFieldHelper::label( 
								'pluginname-enable-schema', 
								__( 'Enable PluginName Schema', 'pluginname' ), 
								[
									'description' => __( 'Allow themes and integrations to expose PluginName metadata.', 'pluginname' ),
									'tooltip' => __( 'Keep this enabled when search engines and integrations should understand the PluginName structure.', 'pluginname' ),
								] 
							); ?>
						</th>
						<td>
							<?php echo FormFieldHelper::checkbox( 
								'pluginname_general[enable_schema]', 
								'1', 
								__( 'Enable PluginName Schema', 'pluginname' ), 
								[ 
									'id' => 'pluginname-enable-schema', 
									'checked' => $enable_schema 
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


