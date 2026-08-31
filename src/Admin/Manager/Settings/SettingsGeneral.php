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
	 */
	public function render( array $values ): void {
		$fields = [
			'root_name' => [ 'label' => __( 'PluginName Root Name', 'pluginname' ), 'description' => __( 'The name used for the main PluginName area.', 'pluginname' ), 'tooltip' => __( 'This name appears in the admin interface and generated titles.', 'pluginname' ) ],
			'root_description' => [ 'label' => __( 'PluginName Description', 'pluginname' ), 'description' => __( 'A short description for the PluginName knowledge base.', 'pluginname' ), 'tooltip' => __( 'This can be used by themes and integrations when describing the PluginName area.', 'pluginname' ), 'type' => 'textarea' ],
			'archive_title' => [ 'label' => __( 'Wiki Archive Title', 'pluginname' ), 'description' => __( 'The title shown on Wiki archive and index views.', 'pluginname' ), 'tooltip' => __( 'Use a concise title that makes the documentation area clear to visitors.', 'pluginname' ) ],
			'archive_description' => [ 'label' => __( 'Wiki Archive Description', 'pluginname' ), 'description' => __( 'Supporting text shown on Wiki archive and index views.', 'pluginname' ), 'tooltip' => __( 'A short introduction helps visitors understand what they can find in the Wiki.', 'pluginname' ), 'type' => 'textarea' ],
			'root_slug' => [ 'label' => __( 'PluginName Root Slug', 'pluginname' ), 'description' => __( 'The URL slug for the PluginName root.', 'pluginname' ), 'tooltip' => __( 'Use lowercase letters, numbers, and hyphens for the most reliable URLs.', 'pluginname' ) ],
			'category_slug' => [ 'label' => __( 'Custom Category Slug', 'pluginname' ), 'description' => __( 'The URL slug used for PluginName categories.', 'pluginname' ), 'tooltip' => __( 'Changing this value flushes the WordPress rewrite rules.', 'pluginname' ), 'tooltip_type' => 'info' ],
			'tag_slug' => [ 'label' => __( 'Custom Tags Slug', 'pluginname' ), 'description' => __( 'The URL slug used for PluginName tags.', 'pluginname' ), 'tooltip' => __( 'Changing this value flushes the WordPress rewrite rules.', 'pluginname' ), 'tooltip_type' => 'info' ],
			'permalink' => [ 'label' => __( 'PluginName Permalink', 'pluginname' ), 'description' => __( 'The permalink structure used by PluginName content.', 'pluginname' ), 'tooltip' => __( 'Choose a structure that remains readable and stable after publication.', 'pluginname' ) ],
			'enable_schema' => [ 'label' => __( 'Enable Documentation Schema', 'pluginname' ), 'description' => __( 'Allow PluginName themes and integrations to expose documentation metadata.', 'pluginname' ), 'tooltip' => __( 'Keep this enabled when search engines and integrations should understand the Wiki structure.', 'pluginname' ), 'type' => 'checkbox', 'default' => true ],
		];
		foreach ( $fields as $key => $field ) {
			$key = SanitizationHelper::key( $key );
			$id = 'pluginname-' . $key;
			$name = 'pluginname_general[' . $key . ']';
			$value = 'permalink' === $key ? PermalinkHelper::sanitize_pattern( $values[ $key ] ?? '' ) : SanitizationHelper::text( $values[ $key ] ?? $field['default'] ?? '' );
			echo '<tr><th scope="row">' . FormFieldHelper::label( $id, $field['label'], $field ) . '</th><td>';
			if ( 'textarea' === ( $field['type'] ?? '' ) ) {
				echo FormFieldHelper::textarea( $name, $value, [ 'id' => $id, 'rows' => 3 ] );
			} elseif ( 'checkbox' === ( $field['type'] ?? '' ) ) {
				echo FormFieldHelper::checkbox( $name, '1', $field['label'], [ 'id' => $id, 'checked' => ! empty( $values[ $key ] ?? $field['default'] ) ] );
			} else {
				echo FormFieldHelper::text_input( $name, $value, [ 'id' => $id, 'data-permalink-field' => 'permalink' === $key ? 'permalink' : null ] );
			}
			if ( 'permalink' === $key ) {
				echo '<div class="pluginname-permalink-tokens mt-2" aria-label="' . esc_attr__( 'Available permalink tokens', 'pluginname' ) . '">';
				foreach ( PermalinkHelper::token_definitions() as $token => $description ) {
					echo FormFieldHelper::button( $token, [
						'class' => 'btn-sm btn-outline-secondary me-1 mb-1',
						'type' => 'button',
						'attributes' => [
							'data-permalink-token' => $token,
							'title' => $description,
						],
					] );
				}
				echo '</div><div class="form-text">' . esc_html__( 'Click a token to add it to the pattern. Tokens are inserted with a trailing slash and reappear when removed.', 'pluginname' ) . '</div>';
			}
			echo '</td></tr>';
		}
	}
}
