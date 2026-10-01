<?php
/**
 * CronJobs class for registering plugin cron jobs.
 *
 * @package PluginName
 * @subpackage Includes\Pages
 * @since 1.0.0
 */
namespace PluginName\Includes\Pages;

use PluginName\Includes\Core\CronJobs as CronJobCore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class CronJobs {
	/**
	 * Register all page-layer cron jobs for the plugin.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function register(): void {
		self::pluginname_cleanup();
	}

	/**
	 * Register the built-in PluginName cleanup cron job.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function pluginname_cleanup(): bool {
		return CronJobCore::create(
			'core',
			'pluginname_cleanup',
			'daily',
			array(),
			array( self::class, 'cleanup' ),
			__( 'Daily PluginName cleanup task.', 'pluginname' )
		);
	}

	/**
	 * Example built-in cron callback.
	 *
	 * @return void
	 */
	public static function cleanup(): void {
		do_action( 'pluginname_daily_cleanup' );
	}
}

