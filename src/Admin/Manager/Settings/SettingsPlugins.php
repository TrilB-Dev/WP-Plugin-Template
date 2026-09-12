<?php
/**
 * SettingsPlugins class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Includes\Settings\Settings;
use PluginName\Includes\Plugins\Plugins;
use PluginName\Includes\Plugins\PluginInterface;
use PluginName\Includes\Plugins\SettingsPageProviderInterface;

final class SettingsPlugins {
	/**
	 * Check if a settings page exists for the given slug.
	 *
	 * @param string $slug The slug of the settings page.
	 * @return bool True if the settings page exists, false otherwise.
	 */
	public function has_settings_page( string $slug ): bool {
		return isset( $this->settings_pages()[ $slug ] );
	}

	/**
	 * Check whether the current user can view a provider settings page.
	 *
	 * @param string $slug The settings page slug.
	 * @return bool True when the page is public to the current settings user.
	 */
	public function can_view_settings_page( string $slug ): bool {
		$page = $this->settings_pages()[ $slug ] ?? null;
		if ( ! is_array( $page ) ) {
			return false;
		}

		$capability = SanitizationHelper::key(
			$page['capability'] ?? 'pluginname_settings_plugins_int_view'
		);
		return '' !== $capability && current_user_can( $capability );
	}
	/**
	 * Render the settings page for the given slug.
	 *
	 * @param string $slug The slug of the settings page.
	 * @param array  $values The current values of the settings.
	 */
	public function render_settings_page( string $slug, array $values ): void {
		$page = $this->settings_pages()[ $slug ] ?? null;
		if ( ! is_array( $page ) ) {
			return;
		}
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $page['title'] ?? $page['label'] ); ?></th>
			<td>
				<?php foreach ( $page['fields'] as $field ) : ?>
					<?php
					$key = SanitizationHelper::key( $field['key'] ?? '' );
					if ( '' === $key ) {
						continue;
					}
					$default = array_key_exists( 'default', $field ) ? $field['default'] : false;
					$name    = 'pluginname_' . SanitizationHelper::key( $page['slug'] ) . '[' . $key . ']';
					$value   = $values[ $key ] ?? $default;
					$type    = SanitizationHelper::key( $field['type'] ?? 'checkbox', 'checkbox' );
					?>
					<div class="mb-3">
						<?php echo FormFieldHelper::label(
							'pluginname-' . $key,
							(string) ( $field['label'] ?? $key ),
							array(
								'description'  => (string) ( $field['description'] ?? '' ),
								'tooltip'      => (string) ( $field['tooltip'] ?? '' ),
								'tooltip_type' => SanitizationHelper::key(
									$field['tooltip_type'] ?? 'question',
									'question'
								),
								'tooltip_icon' => (string) ( $field['tooltip_icon'] ?? '' ),
							)
						); ?>
						<?php if ( 'select' === $type ) : ?>
							<?php echo FormFieldHelper::select(
								$name,
								(array) ( $field['options'] ?? array() ),
								$value,
								array( 'id' => 'pluginname-' . $key )
							); ?>
						<?php elseif ( 'text' === $type ) : ?>
							<?php echo FormFieldHelper::input(
								$name,
								is_scalar( $value ) ? (string) $value : '',
								array(
									'id'   => 'pluginname-' . $key,
									'type' => 'text',
								)
							); ?>
						<?php elseif ( 'custom' === $type ) : ?>
							<?php $render = $field['render'] ?? null; if ( is_callable( $render ) ) { call_user_func( $render, $value, $name, 'pluginname-' . $key ); } ?>
						<?php else : ?>
							<?php echo FormFieldHelper::checkbox(
								$name,
								'1',
								'',
								array(
									'id'      => 'pluginname-' . $key,
									'checked' => ! empty( $value ),
								)
							); ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</td>
		</tr>
		<?php
	}
	/**
	 * Render the settings page for the given tab.
	 *
	 * @param string $tab The tab to render.
	 */
	public function render( string $tab ): void {
		if ( 'third-party' === $tab ) {
			$this->render_third_party_plugins();
			return;
		}

		$page = $this->settings_pages()[ $tab ] ?? null;
		if (
			is_array( $page )
			&& ! empty( $page['render_page'] )
			&& is_callable( $page['render_page'] )
		) {
			call_user_func( $page['render_page'] );
			return;
		}

		$this->render_pluginname_plugins();
	}
	/**
	 * Get the registered settings pages from enabled plugins.
	 *
	 * @return array An associative array of registered settings pages.
	 */
	private function settings_pages(): array {
		$pages = array();
		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if (
				! $plugin instanceof PluginInterface
				|| ! $plugin instanceof SettingsPageProviderInterface
				|| ! Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() )
			) {
				continue;
			}

			$page = $plugin->get_settings_page();
			if (
				empty( $page['slug'] )
				|| empty( $page['label'] )
				|| ( empty( $page['fields'] ) && empty( $page['render_page'] ) )
			) {
				continue;
			}
			$pages[ SanitizationHelper::key( $page['slug'] ) ] = $page;

			foreach ( $page['tabs'] ?? array() as $tab ) {
				if (
					! is_array( $tab )
					|| empty( $tab['slug'] )
					|| empty( $tab['label'] )
					|| empty( $tab['render_page'] )
					|| ! is_callable( $tab['render_page'] )
				) {
					continue;
				}

				$pages[ SanitizationHelper::key( $tab['slug'] ) ] = array_merge(
					$page,
					$tab,
					array( 'fields' => $tab['fields'] ?? array() )
				);
			}
		}
		return $pages;
	}
	/**
	 * Render the PluginName plugins section.
	 *
	 * @since 1.0.0
	 */
	private function render_pluginname_plugins(): void {
		?>
		<div class="row g-4">
			<?php foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) : ?>
				<?php if ( $plugin instanceof PluginInterface && $this->can_view_plugin( $plugin ) ) : ?>
					<?php $this->render_pluginname_plugin_card( $plugin ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
	/**
	 * Render the third-party plugins section.
	 *
	 * @since 1.0.0
	 */
	private function render_third_party_plugins(): void {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		?>
		<div class="row g-4">
			<?php foreach ( get_plugins() as $file => $plugin ) : ?>
				<?php if ( function_exists( 'plugin_basename' ) && plugin_basename( PLUGINNAME_FILE ) === $file ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php $this->render_third_party_plugin_card( $file, $plugin ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
	/**
	 * Render a card for a third-party plugin.
	 *
	 * @param string $file The plugin file path.
	 * @param array  $plugin The plugin data.
	 */
	private function render_pluginname_plugin_card( $plugin ): void {
		$enabled       = Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() );
		$settings_page = $plugin instanceof SettingsPageProviderInterface
			? $plugin->get_settings_page()
			: array();
		$modal_id      = SanitizationHelper::key( $plugin->get_slug() );
		$can_edit      = $this->can_edit_plugin( $plugin );
		?>
		<div class="col-12 col-md-6 col-xl-4 d-flex">
			<article class="card pluginname-plugin-card shadow-sm h-100 w-100">
				<div class="card-header d-flex align-items-center gap-2">
					<?php /* translators: %s is the plugin name. */ ?>
					<?php
					echo FormFieldHelper::switch(
						'pluginname-plugin-status',
						'1',
						'',
						array(
							'id'               => 'pluginname-plugin-status-' . SanitizationHelper::key( $plugin->get_slug() ),
							'checked'          => $enabled,
							'disabled'         => ! $can_edit,
							'data-pluginname-plugin-toggle' => 'true',
							'data-plugin-slug' => $plugin->get_slug(),
							'aria-label'       => sprintf(
								/* translators: %s is the plugin name. */
								__( 'Enable %s', 'pluginname' ),
								$plugin->get_name()
							),
						)
					);
					?>
					<span class="fw-semibold"><?php echo esc_html( $plugin->get_name() ); ?></span>
				</div>
				<div class="card-body d-flex flex-column">
					<?php $this->render_plugin_icon( $plugin ); ?>
					<p class="card-text text-secondary mt-3"><?php echo esc_html( $plugin->get_description() ); ?></p>
					<p class="card-text mb-2"><span class="text-secondary"><?php esc_html_e( 'Author:', 'pluginname' ); ?></span> <?php echo esc_html( $plugin->get_author() ); ?></p>
					<p class="card-text mb-2"><span class="text-secondary"><?php esc_html_e( 'Version:', 'pluginname' ); ?></span> <?php echo esc_html( $plugin->get_version() ); ?></p>
					<p class="card-text mb-3"><span class="text-secondary"><?php esc_html_e( 'Docs:', 'pluginname' ); ?></span> <a href="<?php echo esc_url( $plugin->get_uri() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View documentation', 'pluginname' ); ?></a></p>
					<?php if ( ! empty( $settings_page['fields'] ) ) : ?>
						<?php
						echo FormFieldHelper::button(
							__( 'Settings', 'pluginname' ),
							array(
								'type'           => 'button',
								'class'          => 'btn-primary mt-auto',
								'data-bs-toggle' => 'modal',
								'data-bs-target' => '#' . $modal_id,
							)
						);
						?>
					<?php endif; ?>
				</div>
			</article>
		</div>
		<?php

		if ( ! empty( $settings_page['fields'] ) ) {
			$this->render_plugin_settings_modal( $plugin, $settings_page, $modal_id, $can_edit );
		}
	}

	/**
	 * Render a plugin icon from its declared icon variant.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 */
	private function render_plugin_icon( PluginInterface $plugin ): void {
		$icon = $plugin->get_icon();

		if ( is_array( $icon ) && ! empty( $icon[0] ) ) {
			$split_icon_classes = preg_split( '/\s+/', trim( $icon[0] ) );
			$icon_classes       = array_filter(
				array_map( 'sanitize_html_class', is_array( $split_icon_classes ) ? $split_icon_classes : array() )
			);
			$icon_class         = implode( ' ', $icon_classes );
			if ( '' === $icon_class ) {
				$icon_class = 'dashicons dashicons-admin-plugins';
			}
			$color = isset( $icon[1] ) ? sanitize_hex_color( $icon[1] ) : '';
			$style = $color ? 'color: ' . esc_attr( $color ) . ';' : '';

			printf(
				'<i class="pluginname-plugin-icon %1$s" style="%2$s" aria-hidden="true"></i>',
				esc_attr( $icon_class ),
				esc_attr( $style )
			);
			return;
		}

		if ( is_string( $icon ) && '' !== $icon ) {
			printf( '<img src="%1$s" class="pluginname-plugin-icon" alt="" aria-hidden="true" />', esc_url( $icon ) );
			return;
		}
		?>

		<span class="pluginname-plugin-icon dashicons dashicons-admin-plugins" aria-hidden="true"></span>';
		<?php
	}
	/**
	 * Render a card for a third-party plugin.
	 *
	 * @param string $file The plugin file path.
	 * @param array  $plugin The plugin data.
	 */
	private function render_plugin_settings_modal(
		PluginInterface $plugin,
		array $settings_page,
		string $modal_id,
		bool $can_edit
	): void {
		$settings_group = SanitizationHelper::key( $settings_page['settings_group'] ?? $settings_page['slug'] );
		$values         = Settings::get_group( $settings_group, array() ) ?? array();
		?>
		<div class="modal fade pluginname-plugin-settings-modal" id="<?php echo esc_attr( $modal_id ); ?>" tabindex="-1" aria-labelledby="<?php echo esc_attr( $modal_id . '-label' ); ?>" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title fs-5" id="<?php echo esc_attr( $modal_id . '-label' ); ?>"><?php echo esc_html( $plugin->get_name() ); ?></h2>
						<?php echo FormFieldHelper::button(
							'',
							array(
								'class'          => 'btn-close',
								'type'           => 'button',
								'data-bs-dismiss' => 'modal',
								'aria-label'     => __( 'Close', 'pluginname' ),
								'raw'            => true,
							),
						); ?>
					</div>
					<div class="modal-body">
						<section class="pluginname-plugin-modal-info mb-4" aria-labelledby="<?php echo esc_attr( $modal_id . '-info' ); ?>">
							<h3 class="h6" id="<?php echo esc_attr( $modal_id . '-info' ); ?>"><?php esc_html_e( 'Plugin information', 'pluginname' ); ?></h3>
							<p class="text-secondary mb-3"><?php echo esc_html( $plugin->get_description() ); ?></p>
							<dl class="row mb-0 small">
								<dt class="col-sm-3 text-secondary"><?php esc_html_e( 'Author', 'pluginname' ); ?></dt>
								<dd class="col-sm-9"><?php echo esc_html( $plugin->get_author() ); ?></dd>
								<dt class="col-sm-3 text-secondary"><?php esc_html_e( 'Version', 'pluginname' ); ?></dt>
								<dd class="col-sm-9"><?php echo esc_html( $plugin->get_version() ); ?></dd>
								<dt class="col-sm-3 text-secondary"><?php esc_html_e( 'License', 'pluginname' ); ?></dt>
								<dd class="col-sm-9 mb-0"><?php echo esc_html( $plugin->get_license() ); ?></dd>
							</dl>
						</section>
						<form class="pluginname-plugin-settings-form" data-plugin-settings-form data-plugin-slug="<?php echo esc_attr( $plugin->get_slug() ); ?>" data-internal-pluginname-fields>
							<h3 class="h6 mb-3"><?php echo esc_html( $settings_page['title'] ?? $settings_page['label'] ); ?></h3>
							<fieldset <?php disabled( ! $can_edit ); ?>>
								<?php $this->render_plugin_settings_fields( $settings_page, $values, $modal_id ); ?>
							</fieldset>
						</form>
					</div>
					<div class="modal-footer">
						<?php echo FormFieldHelper::button( __( 'Cancel', 'pluginname' ), array( 'class' => 'btn-secondary', 'type' => 'button', 'data-bs-dismiss' => 'modal' ) ); ?>
						<?php if ( $can_edit ) : ?>
							<?php echo FormFieldHelper::button( __( 'Save', 'pluginname' ), array( 'class' => 'btn-primary', 'type' => 'button', 'data-plugin-settings-save' => 'true' ) ); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Render a card for a plugin.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 */
	private function can_view_plugin( PluginInterface $plugin ): bool {
		$capability = $this->is_internal_plugin( $plugin )
			? 'pluginname_settings_plugins_int_view'
			: 'pluginname_settings_plugins_ext_view';
		return current_user_can( $capability );
	}
	/**
	 * Check if the current user can edit the settings of a plugin.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 * @return bool True if the user can edit, false otherwise.
	 */
	private function can_edit_plugin( PluginInterface $plugin ): bool {
		$capability = $this->is_internal_plugin( $plugin )
			? 'pluginname_settings_plugins_int_edit'
			: 'pluginname_settings_plugins_ext_edit';
		return current_user_can( $capability );
	}
	/**
	 * Check if a plugin is an internal PluginName plugin.
	 *
	 * @param PluginInterface $plugin The plugin instance.
	 * @return bool True if the plugin is internal, false otherwise.
	 */
	private function is_internal_plugin( PluginInterface $plugin ): bool {
		return 0 === strpos( get_class( $plugin ), 'PluginName\\Includes\\Plugins\\' );
	}
	/**
	 * Render the settings fields for a plugin's settings page.
	 *
	 * @param array  $settings_page The settings page configuration.
	 * @param array  $values The current values of the settings.
	 * @param string $prefix The prefix for the field IDs.
	 */
	private function render_plugin_settings_fields( array $settings_page, array $values, string $prefix ): void {
		$layout = SanitizationHelper::key( $settings_page['layout'] ?? 'box', 'box' );
		$layout = in_array(
			$layout,
			array( 'table', 'box' ),
			true
		) ? $layout : 'box';

		if ( 'table' === $layout ) {
			?>
			<div class="pluginname-plugin-settings-fields pluginname-plugin-settings-fields-table"><table class="table align-middle"><tbody>
			<?php
		} else {
			?>
			<div class="pluginname-plugin-settings-fields pluginname-plugin-settings-fields-box">
			<?php
		}

		foreach ( $settings_page['fields'] as $field ) {
			$key = SanitizationHelper::key( $field['key'] ?? '' );
			if ( '' === $key ) {
				continue;
			}

			$default            = array_key_exists( 'default', $field ) ? $field['default'] : false;
			$value              = $values[ $key ] ?? $default;
			$type               = SanitizationHelper::key( $field['type'] ?? 'checkbox', 'checkbox' );
			$id                 = SanitizationHelper::key( $prefix . '-' . $key );
			$name               = 'settings[' . $key . ']';
			$wrapper_attributes = array();
			if ( ! empty( $field['wrapper_class'] ) ) {
				$wrapper_attributes['class'] = (string) $field['wrapper_class'];
			}
			if ( ! empty( $field['wrapper_attributes'] ) && is_array( $field['wrapper_attributes'] ) ) {
				$wrapper_attributes = array_merge( $wrapper_attributes, $field['wrapper_attributes'] );
			}
			if ( ! empty( $field['visible_when'] ) && is_array( $field['visible_when'] ) ) {
				$wrapper_attributes['data-pluginname-visible-when'] = wp_json_encode( $field['visible_when'] );
			}
			$wrapper_attributes = FormFieldHelper::attributes_to_string( $wrapper_attributes );
			$label              = FormFieldHelper::label(
				$id,
				(string) ( $field['label'] ?? $key ),
				array(
					'tooltip'      => (string) ( $field['tooltip'] ?? '' ),
					'tooltip_type' => SanitizationHelper::key(
						$field['tooltip_type'] ?? 'question',
						'question'
					),
					'tooltip_icon' => (string) ( $field['tooltip_icon'] ?? '' ),
				)
			);
			if ( 'table' === $layout ) {
				?>
				<tr<?php echo $wrapper_attributes ? ' ' . $wrapper_attributes : ''; ?>>
					<th scope="row" class="w-50"><?php echo wp_kses_post( $label ); ?></th>
					<td>
			<?php } else { ?>
				<article class="pluginname-plugin-settings-field card h-100"<?php echo $wrapper_attributes ? ' ' . $wrapper_attributes : ''; ?>>
					<div class="card-body">
						<div class="pluginname-plugin-settings-field-header d-flex align-items-start justify-content-between gap-3">
							<?php echo wp_kses_post( $label ); ?>
				<?php
				if ( 'checkbox' === $type ) {
					echo FormFieldHelper::switch(
						$name,
						'1',
						'',
						array(
							'id'            => $id,
							'checked'       => ! empty( $value ),
							'wrapper_class' => 'ms-auto flex-shrink-0',
						)
					);
				}
				?>
						</div>
						<?php if ( ! empty( $field['description'] ) ) { ?>
							<p class="pluginname-plugin-settings-field-description text-secondary mb-3">
								<?php echo esc_html( (string) $field['description'] ); ?>
							</p>
						<?php } ?>
					</div>
				</article>
				<?php
			}
			if ( 'custom' === $type && ! empty( $field['render'] ) && is_callable( $field['render'] ) ) {
				call_user_func( $field['render'], $value, $name, $id );
			} elseif ( 'table' === $layout && 'select' === $type ) {
				echo FormFieldHelper::select(
					$name,
					(array) ( $field['options'] ?? array() ),
					$value,
					array(
						'id'         => $id,
						'attributes' => $field['attributes'] ?? array(),
					)
				);
			} elseif ( 'table' === $layout && 'multiselect' === $type ) {
				echo FormFieldHelper::bootstrap_multiselect(
					$name,
					array(
						'id'                  => $id,
						'data'                => (array) ( $field['options'] ?? array() ),
						'selected'            => (array) $value,
						'dropup_auto'         => $field['dropup_auto'] ?? true,
						'show_tick'           => $field['show_tick'] ?? null,
						'selection_indicator' => $field['selection_indicator'] ?? null,
						'attributes'          => $field['attributes'] ?? array(),
					)
				);
			} elseif ( 'table' === $layout && 'text' === $type ) {
				echo FormFieldHelper::input(
					$name,
					is_scalar( $value ) ? (string) $value : '',
					array(
						'id'   => $id,
						'type' => 'text',
					)
				);
			} elseif ( 'table' === $layout ) {
				echo FormFieldHelper::checkbox(
					$name,
					'1',
					'',
					array(
						'id'      => $id,
						'checked' => ! empty( $value ),
					)
				);
			} elseif ( 'select' === $type ) {
				echo FormFieldHelper::select(
					$name,
					(array) ( $field['options'] ?? array() ),
					$value,
					array(
						'id'         => $id,
						'attributes' => $field['attributes'] ?? array(),
					)
				);
			} elseif ( 'multiselect' === $type ) {
				echo FormFieldHelper::bootstrap_multiselect(
					$name,
					array(
						'id'                  => $id,
						'data'                => (array) ( $field['options'] ?? array() ),
						'selected'            => (array) $value,
						'dropup_auto'         => $field['dropup_auto'] ?? true,
						'show_tick'           => $field['show_tick'] ?? null,
						'selection_indicator' => $field['selection_indicator'] ?? null,
						'attributes'          => $field['attributes'] ?? array(),
					)
				);
			} elseif ( in_array( $type, array( 'text', 'email', 'url', 'number' ), true ) ) {
				echo FormFieldHelper::input(
					$name,
					is_scalar( $value ) ? (string) $value : '',
					array(
						'id'   => $id,
						'type' => $type,
					)
				);
			} elseif ( 'textarea' === $type ) {
				$textarea_value = is_scalar( $value )
					? (string) $value
					: wp_json_encode( $value, JSON_PRETTY_PRINT );
				echo FormFieldHelper::textarea(
					$name,
					(string) $textarea_value,
					array(
						'id'   => $id,
						'rows' => 6,
					)
				);
			}
			echo 'table' === $layout ? '</td></tr>' : '</div></article>';
		}

		if ( 'table' === $layout ) {
			?>
			</tbody>
		</table>
	</div>
		<?php } else { ?>
			</div>
			<?php
		}
	}
	/**
	 * Render a card for a third-party plugin.
	 *
	 * @param string $file The plugin file path.
	 * @param array  $plugin The plugin data.
	 */
	private function render_third_party_plugin_card( string $file, array $plugin ): void {
		$active = function_exists( 'is_plugin_active' ) && is_plugin_active( $file );
		?>
		<div class="col-12 col-md-6 col-xl-6 d-flex">
			<article class="card pluginname-plugin-card shadow-sm h-100 w-100">
				<div class="card-header d-flex align-items-center gap-2">
					<?php /* translators: %s is the plugin name. */ ?>
					<?php
					echo FormFieldHelper::switch(
						'pluginname-third-party-status',
						'1',
						'',
						array(
							'id'         => 'pluginname-third-party-status-' . SanitizationHelper::key( $file ),
							'checked'    => $active,
							'disabled'   => true,
							'aria-label' => sprintf(
								/* translators: %s is the plugin name. */
								__( 'Enable %s', 'pluginname' ),
								$plugin['Name'] ?? $file
							),
						)
					);
					?>
					<span class="fw-semibold">
						<?php echo esc_html( $plugin['Name'] ?? $file ); ?>
					</span>
				</div>
				<div class="card-body d-flex flex-column">
					<span class="pluginname-plugin-icon dashicons dashicons-admin-plugins" aria-hidden="true"></span>
					<p class="card-text text-secondary mt-3">
						<?php echo esc_html( $plugin['Description'] ?? __( 'No description provided.', 'pluginname' ) ); ?>
					</p>
					<p class="card-text mb-2">
						<span class="text-secondary">
							<?php esc_html_e( 'Author:', 'pluginname' ); ?>
						</span>
						<?php echo esc_html( $plugin['AuthorName'] ?? wp_strip_all_tags( $plugin['Author'] ?? __( 'Unknown', 'pluginname' ) ) ); ?>
					</p>
					<p class="card-text mb-2">
						<span class="text-secondary">
							<?php esc_html_e( 'Version:', 'pluginname' ); ?>
						</span>
						<?php echo esc_html( $plugin['Version'] ?? __( 'Unknown', 'pluginname' ) ); ?>
					</p>
					<p class="card-text mb-3">
						<span class="text-secondary">
							<?php esc_html_e( 'Docs:', 'pluginname' ); ?>
						</span> 
						<?php if ( ! empty( $plugin['PluginURI'] ) ) : ?>
							<a href="<?php echo esc_url( $plugin['PluginURI'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'View documentation', 'pluginname' ); ?>
							</a>
						<?php else : ?>
							<?php esc_html_e( 'Not available', 'pluginname' ); ?>
						<?php endif; ?>
					</p>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="btn btn-primary mt-auto">
						<?php esc_html_e( 'Settings', 'pluginname' ); ?>
					</a>
				</div>
			</article>
		</div>
		<?php
	}
}



