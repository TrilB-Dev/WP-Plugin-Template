<?php
/**
 * Dashboard main UI for the PluginName plugin.
 *
 * @package PluginName\Admin\Manager\Dashboard
 */
namespace PluginName\Admin\Manager\Dashboard;

use PluginName\Includes\UserManagement\Groups\Groups;
use PluginName\Includes\UserManagement\Roles\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DashboardMain extends DashboardManager {
	/**
	 * Render the dashboard UI content.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		$this->header( __( 'PluginName Dashboard', 'pluginname' ) );
		$this->render_summary();
		$this->render_cards();
		$this->footer();
	}

	/**
	 * Render the key PluginName overview metrics for users, groups, and roles.
	 *
	 * @return void
	 */
	protected function render_summary(): void {
		$total_users = count_users();
		$user_count  = (int) ( $total_users['total_users'] ?? 0 );
		$group_count = count( Groups::get_group_definitions() );
		$role_count  = count( Roles::get_available_roles() );
		$max_roles   = Roles::get_max_roles();
		$cards       = array(
			array(
				'label'       => __( 'Users', 'pluginname' ),
				'value'       => $user_count,
				'url'         => admin_url( 'users.php' ),
				'description' => __( 'Total WordPress users managed by the site.', 'pluginname' ),
				'icon'        => 'dashicons-admin-users',
			),
			array(
				'label'       => __( 'Groups', 'pluginname' ),
				'value'       => $group_count,
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-groups' ),
				'description' => __( 'Configured PluginName membership groups.', 'pluginname' ),
				'icon'        => 'dashicons-groups',
			),
			array(
				'label'       => __( 'Roles', 'pluginname' ),
				'value'       => $role_count,
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-roles' ),
				'description' => __( 'Available WordPress roles in the PluginName role layer.', 'pluginname' ),
				'icon'        => 'dashicons-shield',
			),
			array(
				'label'       => __( 'Max per user', 'pluginname' ),
				'value'       => $max_roles,
				'url'         => admin_url( 'admin.php?page=pluginname&group=settings&tab=access' ),
				'description' => __( 'Maximum linked roles allowed on one account.', 'pluginname' ),
				'icon'        => 'dashicons-admin-network',
			),
		);
		?>
		<section class="mb-4" aria-labelledby="pluginname-dashboard-summary">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h2 id="pluginname-dashboard-summary" class="h5 mb-0"><?php esc_html_e( 'PluginName overview', 'pluginname' ); ?></h2>
				<span class="small text-secondary"><?php esc_html_e( 'Frontend user management health', 'pluginname' ); ?></span>
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
	 * Render quick actions for the PluginName portal configuration.
	 *
	 * @return void
	 */
	protected function render_cards(): void {
		$cards = array(
			array(
				'title'       => __( 'User groups', 'pluginname' ),
				'description' => __( 'Create and manage membership groups for access control and portal segmentation.', 'pluginname' ),
				'icon'        => 'dashicons-groups',
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-groups' ),
				'priority'    => 10,
			),
			array(
				'title'       => __( 'User roles', 'pluginname' ),
				'description' => __( 'Adjust role combinations and ensure users stay within the configured maximum role count.', 'pluginname' ),
				'icon'        => 'dashicons-shield',
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-roles' ),
				'priority'    => 20,
			),
			array(
				'title'       => __( 'Login', 'pluginname' ),
				'description' => __( 'Review and manage the frontend login experience and authentication flow.', 'pluginname' ),
				'icon'        => 'dashicons-admin-network',
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-login' ),
				'priority'    => 30,
			),
			array(
				'title'       => __( 'Registration', 'pluginname' ),
				'description' => __( 'Configure the custom PluginName registration and activation workflow.', 'pluginname' ),
				'icon'        => 'dashicons-plus-alt',
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-registration' ),
				'priority'    => 40,
			),
			array(
				'title'       => __( 'Profile', 'pluginname' ),
				'description' => __( 'Review the frontend account profile experience and user details management.', 'pluginname' ),
				'icon'        => 'dashicons-id',
				'url'         => admin_url( 'admin.php?page=pluginname&group=user-management&tab=user-profile' ),
				'priority'    => 50,
			),
			array(
				'title'       => __( 'Access settings', 'pluginname' ),
				'description' => __( 'Update portal-wide security, access restrictions, and default member controls.', 'pluginname' ),
				'icon'        => 'dashicons-lock',
				'url'         => admin_url( 'admin.php?page=pluginname&group=settings&tab=access' ),
				'priority'    => 60,
			),
		);

		usort(
			$cards,
			static fn( array $left, array $right ): int => (int) ( $left['priority'] ?? 100 ) <=> (int) ( $right['priority'] ?? 100 )
		);
		?>
		<section aria-labelledby="pluginname-dashboard-cards">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h2 id="pluginname-dashboard-cards" class="h5 mb-0"><?php esc_html_e( 'Quick actions', 'pluginname' ); ?></h2>
				<span class="small text-secondary"><?php esc_html_e( 'Manage the core access experience', 'pluginname' ); ?></span>
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
}

