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
use PluginName\Admin\Manager\Settings\SettingsEmail;
use PluginName\Admin\Manager\Settings\SettingsGeneral;
use PluginName\Admin\Manager\Settings\SettingsPlugins;
use PluginName\Admin\Manager\Settings\SettingsLayout;
use PluginName\Admin\Manager\Settings\SettingsSecurity;
use PluginName\Includes\Functions\Helpers\AjaxHelper;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\RequestHelper;
use PluginName\Includes\Functions\Admin\FunctionsSettings;
use PluginName\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsManager extends Manager {
	/**
	 * Plugin settings page manager instance.
	 *
	 * @var SettingsPlugins
	 */
	private SettingsPlugins $plugins_page;
	/**
	 * FunctionsSettings instance.
	 *
	 * @var FunctionsSettings
	 */
	private FunctionsSettings $functions_settings;
	/**
	 * Current settings page slug.
	 *
	 * @var string
	 */
	protected string $page;
	/**
	 * Constructor for the settings manager.
	 */
	public function __construct() {
		/*
		 * Initialize the settings manager.
		 * 
		 * @since 1.0.0
		 */
		$this->page         = 'settings';
		/*
		 * Initialize the plugin pages and functions settings instances.
		 * 
		 * @since 1.0.0
		 */
		$this->plugins_page = new SettingsPlugins();
		/*
		 * Initialize the functions settings instance.
		 * 
		 * @since 1.0.0
		 */
		$this->functions_settings = new FunctionsSettings();
	}
	/**
	 * Renders the settings page.
	 *
	 * @return void
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
	 * Renders the content for a specific settings tab.
	 *
	 * @param string $tab The tab to render.
	 * @return void
	 */
	public function render_tab_content( string $tab ): void {
		$tab               = $this->normalize_tab( $tab );
		$view_capabilities = array(
			'general'     => array( 'pluginname_settings_general_view' ),
			'email'       => array( 'pluginname_settings_email_view' ),
			'access'      => array( 'pluginname_settings_access_view' ),
			'layout'      => array( 'pluginname_settings_layout_view' ),
			'security'    => array( 'pluginname_settings_security_view' ),
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

		$values = Settings::get_group( $tab, array() ) ?? array();
		$pages  = array(
			'general'     => array(
				'title'    => __( 'PluginName configuration', 'pluginname' ),
				'copy'     => __( 'Set the default commercial rules for generated licences, expiry, and validation.', 'pluginname' ),
				'instance' => new SettingsGeneral(),
			),
			'email'       => array(
				'title'    => __( 'Email settings', 'pluginname' ),
				'copy'     => __( 'Configure the default email settings for generated customer notifications.', 'pluginname' ),
				'instance' => new SettingsEmail(),
			),
			'access'      => array(
				'title'    => __( 'Access control', 'pluginname' ),
				'copy'     => __( 'Define who can issue, revoke, export, review, and manage licences.', 'pluginname' ),
				'instance' => new SettingsAccess(),
			),
			'layout'      => array(
				'title'    => __( 'Layout settings', 'pluginname' ),
				'copy'     => __( 'Control the default frontend account and member experience.', 'pluginname' ),
				'instance' => new SettingsLayout(),
			),
			'security'    => array(
				'title'    => __( 'Security', 'pluginname' ),
				'copy'     => __( 'Define the security settings for managing licences.', 'pluginname' ),
				'instance' => new SettingsSecurity(),
			),
		);
		?>
		<div class="pluginname-settings-tab-content" role="tabpanel">
			<?php if ( isset( $pages[ $tab ] ) ) : ?>
				<?php $this->render_tab_panel( $tab, $pages[ $tab ]['title'], $pages[ $tab ]['copy'], $pages[ $tab ]['instance'], $values ); ?>
			<?php else : ?>
				<?php $this->plugins_page->render( $tab ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Load a settings tab payload for an AJAX request.
	 *
	 * @return void
	 */
	public function load_tab(): void {
		$tab             = sanitize_key( RequestHelper::get_key( 'tab', 'general' ) );
		$view_capability = array(
			'general'     => 'pluginname_settings_general_view',
			'access'      => 'pluginname_settings_access_view',
			'layout'      => 'pluginname_settings_layout_view',
			'email'       => 'pluginname_settings_email_view',
			'security'    => 'pluginname_settings_security_view',
			'plugins'     => 'pluginname_settings_plugins_view',
			'third-party' => 'pluginname_settings_plugins_ext_view',
		)[ $tab ] ?? 'pluginname_settings_general_view';

		if ( ! AjaxHelper::authorized( 'pluginname_settings_tabs', $view_capability ) ) {
			AjaxHelper::unauthorized( __( 'You are not authorized to load PluginName settings.', 'pluginname' ) );
		}

		ob_start();
		$this->render_tab_content( $tab );
		$html = (string) ob_get_clean();
		AjaxHelper::success(
			array(
				'html' => $html,
				'tab'  => $tab,
			)
		);
	}

	/**
	 * Render a settings tab panel using the manager/page component pattern.
	 *
	 * @param string $title The panel title.
	 * @param string $copy The helper copy.
	 * @param object $instance The settings page instance.
	 * @param array  $values The values for the tab.
	 * @return void
	 */
	private function render_tab_panel( string $tab, string $title, string $copy, object $instance, array $values ): void {
		?>
		<div class="card shadow-sm">
			<div class="card-body">
				<div class="mb-3">
					<h5 class="h5 mb-1"><?php echo esc_html( $title ); ?></h5>
					<p class="text-secondary mb-0"><?php echo esc_html( $copy ); ?></p>
				</div>
					<?php if ( method_exists( $instance, 'render_page_content' ) ) : ?>
						<?php $instance->render_page_content( $values ); ?>
					<?php elseif ( method_exists( $instance, 'render' ) ) : ?>
						<?php $instance->render( $values ); ?>
					<?php endif; ?>
			</div>
		</div>
		<?php
	}
	/**
	 * Normalizes the tab value to ensure it is valid.
	 *
	 * @param string $tab The tab to normalize.
	 * @return string The normalized tab.
	 */
	private function normalize_tab( string $tab ): string {
		$allowed = array( 'general', 'layout', 'email', 'access', 'security', 'plugins', 'third-party' );
		if ( in_array( $tab, $allowed, true ) || $this->plugins_page->has_settings_page( $tab ) ) {
			return $tab;
		}
		return 'general';
	}
	/**
	 * Registers the assets for the settings page.
	 *
	 * @param Assets $assets The assets manager instance.
	 * @return void
	 */
	public function register_assets( Assets $assets ): void {
		$settings_assets              = $this->assets( 'settings' );
		$settings_assets['scripts'][] = array(
			'handle'    => 'pluginname-admin-plugins',
			'src'       => PLUGINNAME_ASSETS_URL . '/dist/js/admin.plugins.js',
			'deps'      => array( 'pluginname-bootstrap' ),
			'in_footer' => true,
		);
		$assets->register_page( 'pluginname-settings', $settings_assets );
	}
}



