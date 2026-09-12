<?php
/**
 * ResetManager class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */

namespace PluginName\Admin\Manager\Tools;

use PluginName\Admin\Manager\Manager;
use PluginName\Includes\Functions\Helpers\AjaxHelper;
use PluginName\Includes\Functions\Helpers\AlertHelper;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\LoaderHelper;
use PluginName\Includes\Functions\Helpers\PermissionHelper;
use PluginName\Includes\Functions\Helpers\RequestHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;
use PluginName\Includes\Plugins\PluginInterface;
use PluginName\Includes\Plugins\Plugins;
use PluginName\Includes\Plugins\SettingsPageProviderInterface;
use PluginName\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ResetManager extends Manager {
	/**
	 * Register hooks owned by the plugin reset tool.
	 *
	 * @since 1.0.0
	 * @param LoaderHelper|null $loader WordPress hook loader.
	 */
	public function __construct( ?LoaderHelper $loader = null ) {
		( $loader ?? new LoaderHelper() )->register_component(
			$this,
			array(
				array(
					'type'     => 'action',
					'hook'     => 'admin_post_pluginname_reset',
					'callback' => 'handle_reset',
				),
			)
		)->run();
	}

	/**
	 * Render the plugin reset tool content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_page_content(): void {
		if ( '1' === RequestHelper::get_text( 'reset_complete' ) ) {
			AlertHelper::render_admin_notice( __( 'The selected PluginName data was reset successfully.', 'pluginname' ), 'success' );
		}
		if ( '1' === RequestHelper::get_text( 'reset_failed' ) ) {
			AlertHelper::render_admin_notice( __( 'The selected PluginName data could not be reset.', 'pluginname' ), 'error' );
		}
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<h2 class="h5"><?php esc_html_e( 'Reset PluginName data', 'pluginname' ); ?></h2>
				<p class="text-secondary"><?php esc_html_e( 'Reset PluginName settings and registered plugin data to their factory values. This does not delete WordPress content.', 'pluginname' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php echo FormFieldHelper::input( 'action', 'pluginname_reset', array( 'type' => 'hidden' ) ); ?>
					<?php wp_nonce_field( 'pluginname_reset', 'pluginname_reset_nonce' ); ?>
					<?php echo FormFieldHelper::label( 'pluginname-reset-scope', __( 'Reset scope', 'pluginname' ) ); ?>
					<?php echo FormFieldHelper::select( 'scope', $this->scope_options(), 'core', array( 'id' => 'pluginname-reset-scope' ) ); ?>
					<fieldset class="mt-4" id="pluginname-reset-plugins" data-pluginname-reset-plugins hidden>
						<legend><?php esc_html_e( 'Plugin data', 'pluginname' ); ?></legend>
						<?php
						foreach ( $this->plugin_options() as $slug => $plugin ) {
							echo FormFieldHelper::checkbox( 'plugins[]', $slug, $plugin['name'], array( 'id' => 'pluginname-reset-' . $slug ) );
						}
						?>
					</fieldset>
					<div class="mt-4">
						<?php
						echo FormFieldHelper::checkbox(
							'confirm',
							'1',
							__( 'I understand that this action cannot be undone.', 'pluginname' ),
							array(
								'id'       => 'pluginname-reset-confirm',
								'required' => true,
							)
						);
						?>
					</div>
					<div class="mt-4">
						<?php
						echo FormFieldHelper::button(
							__( 'Reset selected data', 'pluginname' ),
							array(
								'type'  => 'submit',
								'class' => 'btn-danger',
							)
						);
						?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Process a plugin reset request.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_reset(): void {
		if ( ! AjaxHelper::is_method( 'POST' ) || ! PermissionHelper::can( 'pluginname_tools_reset' ) || ! AjaxHelper::has_valid_nonce( 'pluginname_reset', 'pluginname_reset_nonce' ) ) {
			wp_die( esc_html__( 'The reset request could not be authorized.', 'pluginname' ), '', array( 'response' => 403 ) );
		}
		if ( ! RequestHelper::boolean( $_POST, 'confirm' ) ) {
			$this->redirect( false );
		}
		$scope   = RequestHelper::key( $_POST, 'scope', 'core' );
		$groups  = $this->groups_for_scope( $scope, RequestHelper::array( $_POST, 'plugins' ) );
		$success = 'all' === $scope ? Settings::reset_all() : ( ! empty( $groups ) && Settings::reset_groups( $groups ) );
		$this->redirect( $success );
	}
	/**
	 * Redirects the user after a reset attempt.
	 *
	 * @param bool $success Whether the reset was successful.
	 *
	 * @since 1.0.0
	 */
	private function redirect( bool $success ): void {
		wp_safe_redirect( admin_url( 'admin.php?page=pluginname-tools&tool=reset&' . ( $success ? 'reset_complete=1' : 'reset_failed=1' ) ) );
		exit;
	}
	/**
	 * Returns the available reset scope options.
	 *
	 * @return array The available reset scope options.
	 *
	 * @since 1.0.0
	 */
	private function scope_options(): array {
		return array(
			'all'     => __( 'All PluginName data', 'pluginname' ),
			'core'    => __( 'PluginName core only', 'pluginname' ),
			'plugins' => __( 'Selected plugins', 'pluginname' ),
		);
	}

	/**
	 * Returns the available plugin options for reset.
	 *
	 * @return array The available plugin options.
	 *
	 * @since 1.0.0
	 */
	private function plugin_options(): array {
		$options = array();
		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if ( ! $plugin instanceof PluginInterface || ! $plugin instanceof SettingsPageProviderInterface ) {
				continue;
			}
			$page  = $plugin->get_settings_page();
			$group = SanitizationHelper::key( $page['settings_group'] ?? '' );
			if ( '' !== $group ) {
				$options[ sanitize_key( $plugin->get_slug() ) ] = array(
					'name'  => $plugin->get_name(),
					'group' => $group,
				);
			}
		}
		return $options;
	}
	/**
	 * Returns the reset groups for a given scope and selected plugins.
	 *
	 * @param string $scope The reset scope.
	 * @param array $plugins The selected plugins.
	 *
	 * @return array The reset groups.
	 *
	 * @since 1.0.0
	 */
	private function groups_for_scope( string $scope, array $plugins ): array {
		if ( 'core' === $scope ) {
			return Settings::core_groups();
		}
		if ( 'plugins' !== $scope ) {
			return array();
		}
		$options = $this->plugin_options();
		$groups  = array();
		foreach ( $plugins as $slug ) {
			if ( ! is_scalar( $slug ) ) {
				continue;
			}
			$slug = sanitize_key( (string) $slug );
			if ( isset( $options[ $slug ] ) ) {
				$groups[] = $options[ $slug ]['group'];
			}
		}
		return array_values( array_unique( $groups ) );
	}
}



