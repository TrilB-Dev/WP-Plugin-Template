<?php
/**
 * AnalyticsManager class for PluginName plugin.
 *
 * @package PluginName
 * @subpackage Admin\Manager\Tools
 * @since 1.0.0
 */
namespace PluginName\Admin\Manager\Tools;

use PluginName\Admin\Manager\Manager;
use PluginName\Assets\Assets;
use PluginName\Includes\Analytics\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class AnalyticsManager extends Manager {
    /**
     * The Page variable.
     *
     * @since 1.0.0
     * @access protected
     * @var string $page The page variable.
     */
    protected $page;
    /**
     * `Constructor` method for the `DashboardManager` class. 
     *
     * @since 1.0.0
     * @return void
     */

    public function __construct() {
        /**
         * Set the page variable to 'dashboard'.
         *
         * @since 1.0.0
         */
        $this->page = 'analytics';

    }
    /**
     * Renders the analytics page.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_content(): void {
        echo '<div class="pluginname-analytics-summary">';
        $this->card( __( 'Total Wiki Page Views', 'pluginname' ), Analytics::total_views(), 'pluginname-manage' );
        echo '</div><h2 class="h4 mt-4">' . esc_html__( 'Most Viewed Wiki Pages', 'pluginname' ) . '</h2><div class="table-responsive"><table class="table pluginname-analytics-table table-striped table-hover align-middle"><thead><tr><th>' . esc_html__( 'Page', 'pluginname' ) . '</th><th>' . esc_html__( 'Views', 'pluginname' ) . '</th></tr></thead><tbody>';
        foreach ( Analytics::top_pages() as $page ) {
            printf( '<tr><td><a href="%s">%s</a></td><td>%d</td></tr>', esc_url( $page['link'] ), esc_html( $page['title'] ), absint( $page['views'] ) );
        }
        echo '</tbody></table></div>';
    }
}
