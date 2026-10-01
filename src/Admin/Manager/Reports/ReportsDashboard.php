<?php
/**
 * Reports dashboard UI for the PluginName plugin.
 *
 * @package PluginName\Admin\Manager\Reports
 */
namespace PluginName\Admin\Manager\Reports;

use PluginName\Includes\Analytics\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ReportsDashboard extends ReportsManager {
	/**
	 * Render the reports page content.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		$this->header( __( 'Reports', 'pluginname' ) );
		$login_summary = Analytics::get_summary( array( 'event' => 'user_login' ) );
		$signup_summary = Analytics::get_summary( array( 'event' => 'user_register' ) );
		$view_summary = Analytics::get_summary( array( 'event' => 'page_view' ) );
		?>
		<div class="row g-4">
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<h2 class="h6 text-muted"><?php esc_html_e( 'Logins', 'pluginname' ); ?></h2>
						<p class="display-6 mb-0"><?php echo esc_html( (string) ( $login_summary['count'] ?? 0 ) ); ?></p>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<h2 class="h6 text-muted"><?php esc_html_e( 'Registrations', 'pluginname' ); ?></h2>
						<p class="display-6 mb-0"><?php echo esc_html( (string) ( $signup_summary['count'] ?? 0 ) ); ?></p>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<h2 class="h6 text-muted"><?php esc_html_e( 'Page views', 'pluginname' ); ?></h2>
						<p class="display-6 mb-0"><?php echo esc_html( (string) ( $view_summary['count'] ?? 0 ) ); ?></p>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm h-100">
					<div class="card-body">
						<h2 class="h6 text-muted"><?php esc_html_e( 'Last activity', 'pluginname' ); ?></h2>
						<p class="display-6 mb-0"><?php echo esc_html( $login_summary['last_seen'] ?? $signup_summary['last_seen'] ?? $view_summary['last_seen'] ?? __( 'No activity yet', 'pluginname' ) ); ?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
		$this->footer();
	}
}

