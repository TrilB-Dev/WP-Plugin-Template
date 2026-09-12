<?php
/**
 * Admin email template for retired licence types.
 *
 * @package PluginName
 */

namespace PluginName\Includes\Email\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class LicenceTypeRetired extends EmailTemplate {
	/** @var string */
	protected string $template_name = 'LicenceTypeRetired';

	public function render( array $context = array() ): string {
		$values = array_merge( $this->context, $context );
		$name   = $this->text( $values['name'] ?? __( 'Licence type', 'pluginname' ) );
		if ( ! empty( $values['retired'] ) ) {
			$message = __( 'retired', 'pluginname' );
		} else {
			$message = __( 'reactivated', 'pluginname' );
		}

		/* translators: %s is the licence type status label. */
		$status_message = sprintf( __( 'This licence type has been %s.', 'pluginname' ), $message );

		$html = '<h2 style="margin: 0 0 12px;">' . esc_html__( 'Licence type status changed', 'pluginname' ) . '</h2>
            <p style="margin: 0 0 12px;">' . esc_html( $status_message ) . '</p>
            <p style="margin: 0;"><strong>' . esc_html__( 'Name', 'pluginname' ) . ':</strong> ' . esc_html( $name ) . '</p>';

		return $this->wrap( $html );
	}
}



