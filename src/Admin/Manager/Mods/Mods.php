<?php
/**
 * Mod list screen for the PluginName mod manager.
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

final class Mods extends ModManager {
	/**
	 * Render a list of available mod records.
	 *
	 * @return void
	 */
	public function render_page_content(): void {
		$mod_service = new ModManagement();
		$mods        = $mod_service->get_mods();
		?>
		<div class="card shadow-sm border-0">
			<div class="card-body p-4">
				<div class="d-flex align-items-center justify-content-between gap-3 mb-4">
					<h2 class="h5 mb-0"><?php esc_html_e( 'Available Mods', 'pluginname' ); ?></h2>
					<a class="btn btn-primary btn-sm" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname&group=manage-mod&tab=mods' ) ); ?>"><?php esc_html_e( 'Create mod', 'pluginname' ); ?></a>
				</div>
				<?php if ( empty( $mods ) ) : ?>
					<p class="text-muted mb-0"><?php esc_html_e( 'No mods have been created yet.', 'pluginname' ); ?></p>
				<?php else : ?>
					<div class="table-responsive">
						<table class="table align-middle mb-0">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Name', 'pluginname' ); ?></th>
									<th><?php esc_html_e( 'Status', 'pluginname' ); ?></th>
									<th><?php esc_html_e( 'Version', 'pluginname' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $mods as $mod ) : ?>
									<?php $summary = $mod_service->get_mod_summary( (int) $mod->ID ); ?>
									<tr>
										<td><?php echo esc_html( $summary['title'] ); ?></td>
										<td><?php echo esc_html( ucfirst( $summary['status'] ) ); ?></td>
										<td><?php echo esc_html( $summary['version'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}


