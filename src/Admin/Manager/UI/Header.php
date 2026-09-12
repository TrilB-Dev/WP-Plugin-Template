<?php
/**
 * Header UI component for PluginName admin pages.
 *
 * @package PluginName
 * @subpackage Admin\Manager\UI
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\UI;

use PluginName\Assets\Assets;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Header {
	/**
	 * Renders the header for PluginName admin pages.
	 *
	 * @return void
	 */
	public static function render(): void {
		$links = array(
			array(
				'label' => __( 'Documentation', 'pluginname' ),
				'url'   => 'https://github.com/TrilB-Dev/PluginName',
			),
			array(
				'label' => __( 'Community', 'pluginname' ),
				'url'   => 'https://github.com/TrilB-Dev/PluginName/discussions',
			),
			array(
				'label' => __( 'Extensions', 'pluginname' ),
				'url'   => 'https://github.com/TrilB-Dev/PluginName',
			),
			array(
				'label' => __( 'Support', 'pluginname' ),
				'url'   => 'https://github.com/TrilB-Dev/PluginName/issues',
			),
			array(
				'label' => __( 'Roadmap', 'pluginname' ),
				'url'   => 'https://github.com/TrilB-Dev/PluginName/issues',
			),
			array(
				'label' => __( 'Account', 'pluginname' ),
				'url'   => 'https://github.com/TrilB-Dev/PluginName',
			),
		);
		?>
		<header class="pluginname-header border-bottom">
			<nav class="navbar navbar-expand-lg" aria-label="<?php esc_attr_e( 'PluginName header navigation', 'pluginname' ); ?>"> 
				<div class="container-fluid pluginname-shell px-3 px-lg-4">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname' ) ); ?>">
						<img class="navbar-brand d-flex align-items-center gap-2" src="<?php echo esc_url( Assets::get_image( 'logo/PluginName-Logo.svg' ) ); ?>" alt="" />
					</a>
					<?php echo FormFieldHelper::button(
						'<span class="navbar-toggler-icon" aria-hidden="true"></span>',
						array(
							'class'          => 'navbar-toggler',
							'type'           => 'button',
							'data-bs-toggle' => 'collapse',
							'data-bs-target' => '#pluginname-header-menu',
							'aria-controls'  => 'pluginname-header-menu',
							'aria-expanded'  => 'false',
							'aria-label'     => __( 'Toggle header navigation', 'pluginname' ),
							'raw'            => true,
						)
					); ?>
					<div class="collapse navbar-collapse" id="pluginname-header-menu">
						<ul class="navbar-nav ms-auto align-items-lg-start gap-lg-1">
							<?php foreach ( $links as $link ) : ?>
								<li class="nav-item"><a class="nav-link" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</nav>
		</header>
		<?php
	}
}



