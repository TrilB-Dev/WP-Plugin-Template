<?php
/**
 * DashboardManager class for the PluginName plugin.
 *
 * @package PluginName
 */
namespace PluginName\Admin\Manager\Dashboard;

use PluginName\Admin\Manager\Manager;
use PluginName\Assets\Assets;
use PluginName\Includes\Licence\LicenceManager;
use PluginName\Includes\Plugins\DashboardProviderInterface;
use PluginName\Includes\Plugins\Plugins;
use PluginName\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DashboardManager extends Manager {

	/**
	 * The slug for the dashboard page.
	 *
	 * @var string
	 */
	protected $page;
	/**
	 * Constructor for the DashboardManager class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->page = 'dashboard';
	}
	/**
	 * Render the dashboard page.
	 *
	 * @since 1.0.0
	 */
	public function render(): void {
		$this->header( __( 'Licence Dashboard', 'pluginname' ) );

		if ( ! Settings::get_bool( 'first_install_complete', false ) ) {
			$this->render_onboarding_modal();
		}

		$this->render_summary();
		$this->render_cards();
		$this->footer();
	}
	/**
	 * Render the onboarding modal for first-time setup.
	 *
	 * @since 1.0.0
	 */
	private function render_onboarding_modal(): void {
		Settings::register_group(
			'setup',
			array(
				'first_install_complete'    => false,
				'onboarding_steps_complete' => 0,
			)
		);
		?>
		<div class="modal fade pluginname-onboarding-modal" id="pluginname-onboarding-modal" tabindex="-1" aria-labelledby="pluginname-onboarding-title" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content border-0 shadow">
					<div class="modal-header border-0 pb-0">
						<div>
							<span class="badge text-bg-primary-subtle text-primary mb-2"><?php esc_html_e( 'First-time setup', 'pluginname' ); ?></span>
							<h2 class="h3 mb-0" id="pluginname-onboarding-title"><?php esc_html_e( 'Welcome to PluginName', 'pluginname' ); ?></h2>
						</div>
						<?php echo FormFieldHelper::button(
							'',
							array(
								'class'          => 'btn-close',
								'type'           => 'button',
								'data-bs-dismiss' => 'modal',
								'aria-label'     => __( 'Close onboarding', 'pluginname' ),
							)
						); ?>
					</div>
					<div class="modal-body py-4">
						<div class="pluginname-onboarding-step" data-step="1">
							<p class="text-secondary mb-3"><?php esc_html_e( 'Let’s configure the essentials for your first licence workflow.', 'pluginname' ); ?></p>
							<div class="row g-3">
								<div class="col-md-4">
									<div class="card h-100 border-0 bg-light">
										<div class="card-body d-flex flex-column">
											<div class="text-primary mb-2"><span class="dashicons dashicons-admin-generic"></span></div>
											<h3 class="h6"><?php esc_html_e( 'General settings', 'pluginname' ); ?></h3>
											<p class="small text-secondary flex-grow-1 mb-3"><?php esc_html_e( 'Review product defaults, validation rules, and licence behaviour.', 'pluginname' ); ?></p>
											<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Open settings', 'pluginname' ); ?></a>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="card h-100 border-0 bg-light">
										<div class="card-body d-flex flex-column">
											<div class="text-primary mb-2"><span class="dashicons dashicons-cart"></span></div>
											<h3 class="h6"><?php esc_html_e( 'Payments', 'pluginname' ); ?></h3>
											<p class="small text-secondary flex-grow-1 mb-3"><?php esc_html_e( 'Connect PayPal to unlock checkout, subscriptions, and payment flows.', 'pluginname' ); ?></p>
											<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-paypal' ) ); ?>"><?php esc_html_e( 'Connect PayPal', 'pluginname' ); ?></a>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="card h-100 border-0 bg-light">
										<div class="card-body d-flex flex-column">
											<div class="text-primary mb-2"><span class="dashicons dashicons-shield"></span></div>
											<h3 class="h6"><?php esc_html_e( 'Access control', 'pluginname' ); ?></h3>
											<p class="small text-secondary flex-grow-1 mb-3"><?php esc_html_e( 'Define who can issue, manage, and review licences and customer permissions.', 'pluginname' ); ?></p>
											<a class="btn btn-sm btn-outline-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-settings&tab=access' ) ); ?>"><?php esc_html_e( 'Manage access', 'pluginname' ); ?></a>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="pluginname-onboarding-step d-none" data-step="2">
							<h3 class="h5 mb-3"><?php esc_html_e( 'Recommended setup checklist', 'pluginname' ); ?></h3>
							<ul class="list-group list-group-flush">
								<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
									<span><?php esc_html_e( 'Set the default licence and validation settings for your product catalog.', 'pluginname' ); ?></span>
									<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Open', 'pluginname' ); ?></a>
								</li>
								<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
									<span><?php esc_html_e( 'Review access roles and who can manage licences, tools, and settings.', 'pluginname' ); ?></span>
									<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-settings&tab=access' ) ); ?>"><?php esc_html_e( 'Open', 'pluginname' ); ?></a>
								</li>
								<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-3">
									<span><?php esc_html_e( 'Connect your PayPal app to enable checkout and subscription purchase flows.', 'pluginname' ); ?></span>
									<a class="btn btn-sm btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-paypal' ) ); ?>"><?php esc_html_e( 'Connect', 'pluginname' ); ?></a>
								</li>
							</ul>
						</div>
						<div class="pluginname-onboarding-step d-none" data-step="3">
							<h3 class="h5 mb-3"><?php esc_html_e( 'You are ready to launch', 'pluginname' ); ?></h3>
							<div class="alert alert-success border-0 bg-success-subtle text-success-emphasis mb-3">
								<?php esc_html_e( 'Your licence platform is now configured for first issue, customer validation, and secure product access.', 'pluginname' ); ?>
							</div>
							<div class="d-flex flex-wrap gap-2">
								<a class="btn btn-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-licences' ) ); ?>"><?php esc_html_e( 'Issue a licence', 'pluginname' ); ?></a>
								<a class="btn btn-outline-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Review settings', 'pluginname' ); ?></a>
							</div>
						</div>
					</div>
					<div class="modal-footer border-0 pt-0">
						<?php echo FormFieldHelper::button( __( 'Skip for now', 'pluginname' ), array( 'class' => 'btn-link text-secondary', 'type' => 'button', 'data-role' => 'skip' ) ); ?>
						<?php echo FormFieldHelper::button( __( 'Back', 'pluginname' ), array( 'class' => 'btn-outline-secondary', 'type' => 'button', 'data-role' => 'prev' ) ); ?>
						<?php echo FormFieldHelper::button( __( 'Next', 'pluginname' ), array( 'class' => 'btn-primary', 'type' => 'button', 'data-role' => 'next' ) ); ?>
						<?php echo FormFieldHelper::button( __( 'Finish setup', 'pluginname' ), array( 'class' => 'btn-success d-none', 'type' => 'button', 'data-role' => 'finish' ) ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Render the summary section of the dashboard.
	 *
	 * @since 1.0.0
	 */
	private function render_summary(): void {
		$summary = LicenceManager::summary();
		$cards   = array(
			array(
				'label'       => __( 'Active licences', 'pluginname' ),
				'value'       => $summary['active'] ?? 0,
				'url'         => admin_url( 'admin.php?page=pluginname-licences' ),
				'description' => __( 'Currently valid and live licences.', 'pluginname' ),
				'icon'        => 'dashicons-yes-alt',
			),
			array(
				'label'       => __( 'Expiring soon', 'pluginname' ),
				'value'       => $summary['expiring_soon'] ?? 0,
				'url'         => admin_url( 'admin.php?page=pluginname-tools&tool=export' ),
				'description' => __( 'Licences due for review in the next 30 days.', 'pluginname' ),
				'icon'        => 'dashicons-clock',
			),
			array(
				'label'       => __( 'Revoked', 'pluginname' ),
				'value'       => $summary['revoked'] ?? 0,
				'url'         => admin_url( 'admin.php?page=pluginname-tools&tool=debug' ),
				'description' => __( 'Licence records that have been disabled.', 'pluginname' ),
				'icon'        => 'dashicons-no-alt',
			),
			array(
				'label'       => __( 'Customers', 'pluginname' ),
				'value'       => $summary['customers'] ?? 0,
				'url'         => admin_url( 'admin.php?page=pluginname-settings&tab=access' ),
				'description' => __( 'Unique licence holders and managed customers.', 'pluginname' ),
				'icon'        => 'dashicons-groups',
			),
		);
		?>
		<section class="mb-4" aria-labelledby="pluginname-dashboard-summary">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h2 id="pluginname-dashboard-summary" class="h5 mb-0"><?php esc_html_e( 'Licence overview', 'pluginname' ); ?></h2>
				<span class="small text-secondary"><?php esc_html_e( 'Live revenue and access health', 'pluginname' ); ?></span>
			</div>
			<div class="row g-3">
				<?php foreach ( $cards as $card ) : ?>
					<div class="col-md-6 col-xl-3">
						<a class="pluginname-summary-card h-100 d-flex flex-column gap-1" href="<?php echo esc_url( $card['url'] ?? '' ); ?>">
							<span class="pluginname-summary-icon dashicons <?php echo esc_attr( $card['icon'] ?? 'dashicons-admin-generic' ); ?>" aria-hidden="true"></span>
							<span class="text-uppercase small fw-semibold text-secondary"><?php echo esc_html( $card['label'] ?? __( 'Metric', 'pluginname' ) ); ?></span>
							<strong class="h4 mb-0"><?php echo esc_html( (string) ( $card['value'] ?? 0 ) ); ?></strong>
							<span class="small text-secondary"><?php echo esc_html( $card['description'] ?? '' ); ?></span>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
	/**
	 * Render the dashboard cards section.
	 *
	 * @since 1.0.0
	 */
	private function render_cards(): void {
		$cards = apply_filters(
			'pluginname_dashboard_cards',
			array(
				array(
					'title'       => __( 'Issue a licence', 'pluginname' ),
					'description' => __( 'Create a product key for a customer, site, and feature set.', 'pluginname' ),
					'icon'        => 'dashicons-plus-alt',
					'url'         => admin_url( 'admin.php?page=pluginname-licences' ),
					'priority'    => 10,
				),
				array(
					'title'       => __( 'Security settings', 'pluginname' ),
					'description' => __( 'Review validation, key storage, and export protection.', 'pluginname' ),
					'icon'        => 'dashicons-shield',
					'url'         => admin_url( 'admin.php?page=pluginname-settings&tab=general' ),
					'priority'    => 20,
				),
				array(
					'title'       => __( 'Access control', 'pluginname' ),
					'description' => __( 'Set who can issue, revoke, export, and review licences.', 'pluginname' ),
					'icon'        => 'dashicons-admin-users',
					'url'         => admin_url( 'admin.php?page=pluginname-settings&tab=access' ),
					'priority'    => 30,
				),
			)
		);

		if ( is_array( $cards ) ) {
			$cards = array_filter( $cards, array( $this, 'can_render' ) );
			usort( $cards, static fn( $left, $right ) => (int) ( $left['priority'] ?? 100 ) <=> (int) ( $right['priority'] ?? 100 ) );
		}

		foreach ( Plugins::get_instance()->get_registered_plugins() as $plugin ) {
			if ( $plugin instanceof DashboardProviderInterface && Plugins::get_instance()->is_plugin_enabled( $plugin->get_slug() ) ) {
				foreach ( $plugin->get_dashboard_cards() as $card ) {
					if ( is_array( $card ) && $this->can_render( $card ) ) {
						$cards[] = $card;
					}
				}
			}
		}
		?>
		<section aria-labelledby="pluginname-dashboard-cards">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h2 id="pluginname-dashboard-cards" class="h5 mb-0"><?php esc_html_e( 'Quick actions', 'pluginname' ); ?></h2>
				<span class="small text-secondary"><?php esc_html_e( 'Licence operations and controls', 'pluginname' ); ?></span>
			</div>
			<div class="row g-3">
				<?php foreach ( $cards as $card ) : ?>
					<div class="col-md-6 col-xl-4">
						<a class="pluginname-summary-card h-100 d-flex flex-column gap-1" href="<?php echo esc_url( $card['url'] ?? '' ); ?>">
							<span class="pluginname-summary-icon dashicons <?php echo esc_attr( $card['icon'] ?? 'dashicons-admin-generic' ); ?>" aria-hidden="true"></span>
							<span class="fw-semibold text-body"><?php echo esc_html( $card['title'] ?? __( 'Action', 'pluginname' ) ); ?></span>
							<span class="small text-secondary"><?php echo esc_html( $card['description'] ?? '' ); ?></span>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
	/**
	 * Determine if a dashboard card can be rendered for the current user.
	 *
	 * @param array $item The dashboard card item.
	 * @return bool True if the card can be rendered, false otherwise.
	 * @since 1.0.0
	 */
	private function can_render( $item ): bool {
		return is_array( $item ) && ( empty( $item['capability'] ) || current_user_can( $item['capability'] ) );
	}
	/**
	 * Register the assets required for the dashboard page.
	 *
	 * @param Assets $assets The assets manager instance.
	 * @since 1.0.0
	 */
	public function register_assets( Assets $assets ): void {
		$this->register_page_assets( $assets, array( 'pluginname' ), 'dashboard' );
	}
}



