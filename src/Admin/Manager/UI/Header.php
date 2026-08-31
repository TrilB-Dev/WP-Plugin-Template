<?php
/**
 * Header UI component for PluginName admin pages.
 *
 * @package PluginName
 * @subpackage Admin\Manager\UI
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\UI;

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
		$links = [
					[ 
					'label' => __( 'Documentation', 'pluginname' ), 
					'url' => 'https://trilb.dev/collection/web-extension/wordpress/pluginname' 
					],
					[ 
					'label' => __( 'Community', 'pluginname' ), 
					'url' => 'https://trilb.dev/community' 
					],
					[ 
					'label' => __( 'Extensions', 'pluginname' ), 
					'url' => 'https://trilb.dev/extensions' 
					],
					[ 
					'label' => __( 'Support', 'pluginname' ), 
					'url' => 'https://trilb.dev/support' 
					],
					[ 
					'label' => __( 'Roadmap', 'pluginname' ), 
					'url' => 'https://trilb.dev/roadmap' 
					],
					[ 
					'label' => __( 'Account', 'pluginname' ), 
					'url' => 'https://trilb.dev/account' 
					],
			];
		?>
		<header class="pluginname-header border-bottom">
			<nav class="navbar navbar-expand-lg" aria-label="<?php esc_attr_e( 'PluginName header navigation', 'pluginname' ); ?>">
				<div class="container-fluid pluginname-shell px-3 px-lg-4">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname' ) ); ?>">
						<img class="navbar-brand d-flex align-items-center gap-2" src="<?php echo esc_url( WIKIPRESS_ASSETS_URL . '/Images/Logo/PluginNameLogo.svg' ); ?>" alt="" />
					</a>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pluginname-header-menu" aria-controls="pluginname-header-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle header navigation', 'pluginname' ); ?>">
						<span class="navbar-toggler-icon"></span>
					</button>
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
