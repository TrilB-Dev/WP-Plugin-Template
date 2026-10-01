<?php
/**
 * Dashboard screen for the PluginName mod manager.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Mods
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Mods;

use PluginName\Includes\ModManagement\ModManagement;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ModDashboard extends ModManager {
	/**
	 * Render the mod dashboard overview.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		$mod_service = new ModManagement();
		$summary     = $mod_service->get_dashboard_summary();
		?>
		<div class="row g-4">
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm border-0 h-100">
					<div class="card-body">
						<p class="text-muted text-uppercase small fw-semibold mb-2"><?php esc_html_e( 'Total Mods', 'pluginname' ); ?></p>
						<h2 class="display-6 mb-0"><?php echo esc_html( number_format_i18n( $summary['total'] ) ); ?></h2>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm border-0 h-100">
					<div class="card-body">
						<p class="text-muted text-uppercase small fw-semibold mb-2"><?php esc_html_e( 'Published', 'pluginname' ); ?></p>
						<h2 class="display-6 mb-0"><?php echo esc_html( number_format_i18n( $summary['published'] ) ); ?></h2>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm border-0 h-100">
					<div class="card-body">
						<p class="text-muted text-uppercase small fw-semibold mb-2"><?php esc_html_e( 'Drafts', 'pluginname' ); ?></p>
						<h2 class="display-6 mb-0"><?php echo esc_html( number_format_i18n( $summary['draft'] ) ); ?></h2>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-xl-3">
				<div class="card shadow-sm border-0 h-100">
					<div class="card-body">
						<p class="text-muted text-uppercase small fw-semibold mb-2"><?php esc_html_e( 'Actions', 'pluginname' ); ?></p>
						<a class="btn btn-primary btn-sm" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname&group=manage-mod&tab=mods' ) ); ?>"><?php esc_html_e( 'Manage mods', 'pluginname' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}


