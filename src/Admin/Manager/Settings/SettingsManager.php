<?php
/**
 * SettingsManager class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Settings
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Settings;

use PluginName\Admin\Manager\Manager;
use PluginName\Assets\Assets;
use PluginName\Admin\Manager\Settings\SettingsAccess;
use PluginName\Admin\Manager\Settings\SettingsGeneral;
use PluginName\Admin\Manager\Settings\SettingsPlugins;
use PluginName\Includes\Functions\Helpers\RequestHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SettingsManager extends Manager {
	/**
	 * Plugin settings page manager instance.
	 *
	 * @var SettingsPlugins
	 */
	private SettingsPlugins $plugins_page;
	/**
	 * Current settings page slug.
	 *
	 * @var string
	 */
	protected $page;
	/**
	 * Constructor for the settings manager.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->page         = 'settings';
		$this->plugins_page = new SettingsPlugins();
	}
	/**
	 * Render the settings page.
	 *
	 * @since 1.0.0
	 */
	public function render(): void {
		$tab = sanitize_key( RequestHelper::get_key( 'tab', 'general' ) );
		$tab = $this->normalize_tab( $tab );

		$this->header( __( 'Settings', 'pluginname' ) );
		?>
		<div id="pluginname-settings-panel" data-current-tab="<?php echo esc_attr( $tab ); ?>">
			<?php $this->render_tab_content( $tab ); ?>
		</div>
		<?php
		$this->footer();
	}

	/**
	 * Render the content for a specific settings tab.
	 *
	 * @param string $tab The current tab slug.
	 * @since 1.0.0
	 */
	public function render_tab_content( string $tab ): void {
		$tab               = $this->normalize_tab( $tab );
		$view_capabilities = array(
			'general'     => array( 'pluginname_settings_general_view' ),
			'access'      => array( 'pluginname_settings_access_view' ),
			'plugins'     => array( 'pluginname_settings_plugins_view' ),
			'third-party' => array( 'pluginname_settings_plugins_view', 'pluginname_settings_plugins_ext_view' ),
		);

		if ( $this->plugins_page->has_settings_page( $tab ) && ! $this->plugins_page->can_view_settings_page( $tab ) ) {
			wp_die( esc_html__( 'You are not authorized to view these PluginName settings.', 'pluginname' ) );
		}

		$can_view = true;
		foreach ( $view_capabilities[ $tab ] ?? array() as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				$can_view = false;
				break;
			}
		}

		if ( ! $can_view ) {
			wp_die( esc_html__( 'You are not authorized to view these PluginName settings.', 'pluginname' ) );
		}
		?>
		<div class="pluginname-settings-tab-content" role="tabpanel">
			<?php if ( 'general' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h2 class="h5 mb-1"><?php esc_html_e( 'Licence configuration', 'pluginname' ); ?></h2>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Set the default commercial rules for generated licences, expiry, and validation.', 'pluginname' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsGeneral() )->render( array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php elseif ( 'access' === $tab ) : ?>
				<div class="card shadow-sm">
					<div class="card-body">
						<div class="mb-3">
							<h2 class="h5 mb-1"><?php esc_html_e( 'Access control', 'pluginname' ); ?></h2>
							<p class="text-secondary mb-0"><?php esc_html_e( 'Define who can issue, revoke, export, review, and manage licences.', 'pluginname' ); ?></p>
						</div>
						<table class="form-table" role="presentation"><tbody>
							<?php ( new SettingsAccess() )->render( array() ); ?>
						</tbody></table>
					</div>
				</div>
			<?php else : ?>
				<?php $this->plugins_page->render( $tab ); ?>
			<?php endif; ?>
		</div>
		<?php
	}
	/**
	 * Normalize the tab slug to ensure it is valid.
	 *
	 * @param string $tab The tab slug to normalize.
	 * @return string The normalized tab slug.
	 * @since 1.0.0
	 */
	private function normalize_tab( string $tab ): string {
		$allowed = array( 'general', 'access', 'plugins', 'third-party' );
		if ( in_array( $tab, $allowed, true ) || $this->plugins_page->has_settings_page( $tab ) ) {
			return $tab;
		}
		return 'general';
	}
	/**
	 * Register the assets for the settings page.
	 *
	 * @param Assets $assets The assets manager instance.
	 * @since 1.0.0
	 */
	public function register_assets( Assets $assets ): void {
		$settings_assets              = $this->assets( 'settings' );
		$settings_assets['scripts'][] = array(
			'handle'    => 'pluginname-admin-plugins',
			'src'       => PLUGINNAME_URL . 'src/Assets/dist/js/plugins.admin.js',
			'deps'      => array( 'pluginname-bootstrap' ),
			'in_footer' => true,
		);
		$assets->register_page( 'pluginname-settings', $settings_assets );
	}
}



