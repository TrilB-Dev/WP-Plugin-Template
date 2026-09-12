<?php
/**
 * Sidebar UI component for PluginName admin pages.
 *
 * @package PluginName
 * @subpackage Admin\Manager\UI
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\UI;

use PluginName\Includes\Functions\Admin\FunctionsSidebar;
use PluginName\Includes\Functions\Helpers\FormFieldHelper;
use PluginName\Includes\Functions\Helpers\RequestHelper;
use PluginName\Includes\Functions\Helpers\SanitizationHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the sidebar from the centralized FunctionsSidebar menu model.
 * 
 * @since 1.0.0
 */
final class Sidebar {
	/**
	 * Render the admin sidebar.
	 *
	 * @return void
	 */
	public static function render(): void {
		$current = RequestHelper::get_key( 'page', 'pluginname' );
		$groups  = FunctionsSidebar::get_sidebar_groups();
		?>
		<aside class="col-12 col-lg-auto pluginname-sidebar-column">
			<div class="pluginname-sidebar position-sticky" style="top: 32px;">
				<div class="d-flex align-items-center justify-content-between mb-3 px-2">
					<span class="small text-uppercase fw-semibold text-secondary"><?php esc_html_e( 'Navigate', 'pluginname' ); ?></span>
					<span class="badge rounded-pill text-bg-light">WP</span>
				</div>
				<nav aria-label="<?php esc_attr_e( 'PluginName admin navigation', 'pluginname' ); ?>">
					<a class="pluginname-sidebar-link <?php echo 'pluginname' === $current ? 'active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=pluginname' ) ); ?>">
						<?php echo self::render_icon_markup( 'fa-solid fa-house' ); ?><?php esc_html_e( 'Dashboard', 'pluginname' ); ?>
					</a>
					<div id="pluginname-sidebar-groups">
						<?php foreach ( $groups as $key => $group ) : ?>
							<?php $expanded = self::group_is_expanded( $key, $group, $current ); ?>
							<div class="pluginname-sidebar-group">
								<h3 class="pluginname-sidebar-group-heading">
										<?php echo FormFieldHelper::button(
											self::render_icon_markup( (string) ( $group['icon'] ?? '' ) ) . esc_html( $group['label'] ) . '<span class="ms-auto text-secondary">' . count( $group['items'] ) . '</span>',
											array(
												'class'          => 'pluginname-sidebar-link pluginname-sidebar-group-link border-0 bg-transparent w-100 text-start ' . ( $expanded ? '' : 'collapsed' ),
												'type'           => 'button',
												'data-bs-toggle' => 'collapse',
												'data-bs-target' => '#pluginname-group-' . esc_attr( $key ),
												'aria-expanded'  => $expanded ? 'true' : 'false',
												'aria-controls'  => 'pluginname-group-' . esc_attr( $key ),
												'raw'            => true,
											)
										); ?>
								</h3>
								<div id="pluginname-group-<?php echo esc_attr( $key ); ?>" class="collapse <?php echo $expanded ? 'show' : ''; ?>">
									<div class="nav flex-column pluginname-sidebar-group-items">
										<?php foreach ( $group['items'] as $slug => $item ) : ?>
											<?php
											$page   = self::item_page( $slug );
											$query  = self::item_query( $slug );
											$active = self::item_is_active( $page, $query, $current );
											?>
											<a class="nav-link <?php echo $active ? 'active' : ''; ?>" <?php echo $active ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( self::item_url( $page, $query ) ); ?>"><?php echo self::render_icon_markup( (string) ( $item['icon'] ?? '' ), true ); ?><?php echo esc_html( $item['label'] ); ?></a>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</nav>
			</div>
		</aside>
		<?php
	}
	/**
	 * Renders the sidebar for the admin interface.
	 *
	 * @since 1.0.0
	 */
	private static function render_icon_markup( string $icon, bool $with_spacing = false ): string {
		$icon = trim( $icon );
		if ( '' === $icon ) {
			return '';
		}

		if ( preg_match( '/^(https?:)?\/\//i', $icon ) || preg_match( '/\.(svg|png|jpg|jpeg|webp)(\?.*)?$/i', $icon ) ) {
			return sprintf(
				'<span class="pluginname-sidebar-icon pluginname-sidebar-icon-image" aria-hidden="true"><img src="%1$s" alt="" loading="lazy"%2$s /></span>',
				esc_url( $icon ),
				$with_spacing ? ' class="me-2"' : ''
			);
		}

		return sprintf(
			'<span class="pluginname-sidebar-icon" aria-hidden="true"><i class="%1$s%2$s"></i></span>',
			esc_attr( $icon ),
			$with_spacing ? ' me-2' : ''
		);
	}

	/**
	 * Determines if a sidebar group should be expanded based on the current page.
	 *
	 * @param string $key The key of the sidebar group.
	 * @param array<string, mixed> $group The sidebar group configuration.
	 * @param string $current The current admin page.
	 *
	 * @return bool True if the group should be expanded, false otherwise.
	 *
	 * @since 1.0.0
	 */
	private static function group_is_expanded( string $key, array $group, string $current ): bool {
		if ( 'settings' === $key ) {
			return 'pluginname-settings' === $current;
		}
		if ( 'tools' === $key ) {
			return 'pluginname-tools' === $current;
		}

		foreach ( $group['items'] as $slug => $item ) {
			if ( self::item_is_active( self::item_page( $slug ), self::item_query( $slug ), $current ) ) {
				return true;
			}
		}

		return false;
	}
	/**
	 * Extracts the page part from a sidebar item slug.
	 *
	 * @param string $slug The sidebar item slug.
	 *
	 * @return string The page part of the slug.
	 *
	 * @since 1.0.0
	 */
	private static function item_page( string $slug ): string {
		return strtok( $slug, '&' );
	}

	/**
	 * Item Query
	 *
	 * Extracts the query part from a sidebar item slug.
	 *
	 * @param string $slug The sidebar item slug.
	 * @return array The query parameters as an associative array.
	 * @since 1.0.0
	 */
	private static function item_query( string $slug ): array {
		$query = array();
		parse_str( (string) strstr( $slug, '&' ), $query );
		return $query;
	}

	/**
	 * Constructs the URL for a sidebar item based on its page and query parameters.
	 *
	 * @param string $page The page part of the sidebar item.
	 * @param array<string, string> $query The query parameters as an associative array.
	 *
	 * @return string The constructed URL.
	 *
	 * @since 1.0.0
	 */
	private static function item_url( string $page, array $query ): string {
		$query_string = empty( $query ) ? '' : '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
		if ( in_array( $page, array( 'edit.php', 'post-new.php' ), true ) ) {
			return admin_url( $page . $query_string );
		}

		return admin_url( 'admin.php?page=' . $page . ( empty( $query ) ? '' : '&' . ltrim( $query_string, '?' ) ) );
	}

	/**
	 * Checks if a sidebar item is active based on its page, query parameters, and the current admin page.
	 *
	 * @param string $page The page part of the sidebar item.
	 * @param array<string, string> $query The query parameters as an associative array.
	 * @param string $current The current admin page.
	 *
	 * @return bool True if the item is active, false otherwise.
	 *
	 * @since 1.0.0
	 */
	private static function item_is_active( string $page, array $query, string $current ): bool {
		if ( $page !== $current ) {
			return false;
		}

		foreach ( $query as $key => $value ) {
			if ( (string) RequestHelper::value( $_GET, $key, '' ) !== (string) $value ) {
				return false;
			}
		}

		return true;
	}
}



