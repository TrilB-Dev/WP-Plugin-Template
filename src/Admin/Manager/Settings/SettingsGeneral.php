<?php
/**
 * Settings general fields.
 *
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
	 * Render the general settings fields.
	 *
	 * @param array $values The current values for the settings fields.
	 * @since 1.0.0
	 */
	public function render( array $values ): void {
		$fields = array(
			'default_licence_lifetime' => array(
				'label'       => __( 'Default licence lifetime', 'pluginname' ),
				'description' => __( 'Length of time a new licence remains valid before expiry.', 'pluginname' ),
				'tooltip'     => __( 'Use this as the default when issuing new licences.', 'pluginname' ),
			),
			'renewal_policy'           => array(
				'label'       => __( 'Renewal policy', 'pluginname' ),
				'description' => __( 'Choose how new licences are renewed or extended.', 'pluginname' ),
				'tooltip'     => __( 'The policy is used by your billing and compliance workflow.', 'pluginname' ),
			),
			'default_product'          => array(
				'label'       => __( 'Default product', 'pluginname' ),
				'description' => __( 'The primary product assigned to new licences.', 'pluginname' ),
				'tooltip'     => __( 'Every new licence can inherit this product unless overridden.', 'pluginname' ),
			),
			'validation_domain_mode'   => array(
				'label'       => __( 'Validation domain mode', 'pluginname' ),
				'description' => __( 'Controls how licences are bound to the customer install domain.', 'pluginname' ),
				'tooltip'     => __( 'This protects against unauthorised licence reuse across domains.', 'pluginname' ),
			),
			'allow_token_export'       => array(
				'label'       => __( 'Allow token export', 'pluginname' ),
				'description' => __( 'Allow privileged staff to export active licence data.', 'pluginname' ),
				'tooltip'     => __( 'Exports should remain encrypted and password-protected.', 'pluginname' ),
				'type'        => 'checkbox',
				'default'     => true,
			),
			'require_approval'         => array(
				'label'       => __( 'Require approval for new licences', 'pluginname' ),
				'description' => __( 'Require a second approval step before issuing a licence.', 'pluginname' ),
				'tooltip'     => __( 'Use this to tighten your internal approval workflow.', 'pluginname' ),
				'type'        => 'checkbox',
				'default'     => false,
			),
		);

		foreach ( $fields as $key => $field ) {
			$key   = SanitizationHelper::key( $key );
			$id    = 'pluginname-' . $key;
			$name  = 'pluginname_general[' . $key . ']';
			$value = $values[ $key ] ?? $field['default'] ?? '';
			?>
			<tr>
				<th scope="row"><?php echo FormFieldHelper::label( $id, $field['label'], $field ); ?></th>
				<td>
					<?php
					if ( 'checkbox' === ( $field['type'] ?? '' ) ) {
						echo FormFieldHelper::checkbox(
							$name,
							'1',
							$field['label'],
							array(
								'id'      => $id,
								'checked' => ! empty( $value ),
							)
						);
					} else {
						echo FormFieldHelper::text_input(
							$name,
							is_scalar( $value ) ? (string) $value : '',
							array( 'id' => $id )
						);
					}
					?>
				</td>
			</tr>
			<?php
		}
	}
}



