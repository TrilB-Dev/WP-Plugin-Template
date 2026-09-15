<?php
/**
 * Main Includes class for the PluginName plugin.
 *
 * Handles core initialization and extension management.
 */
namespace PluginName\Includes;

use PluginName\Includes\Core\Core;
use PluginName\Includes\Core\WP\WPLoader;
use PluginName\Includes\Functions\Helpers\LoggerHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Includes {
	/**
	 * The singleton instance of the Includes class.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;
	/**
	 * The core instance managing the main plugin functionality.
	 *
	 * @var Core
	 */
	private Core $core;
	/**
	 * List of registered extension initializers.
	 *
	 * @var array<int, callable>
	 */
	private array $extensions = array();
	/**
	 * Indicates whether the Includes instance has been initialized.
	 *
	 * @var bool
	 */
	private bool $initialized = false;
	/**
	 * Private constructor to enforce singleton pattern.
	 */
	private function __construct() {
		$this->core = new Core();
		LoggerHelper::write_log( 'PluginName core Includes initialized.' );
	}
	/**
	 * Get the singleton instance of the Includes class.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
	 * Initialize the Includes instance and its extensions.
	 *
	 * Subsequent calls have no effect.
	 */
	public function init(): void {
		if ( $this->initialized ) {
			return;
		}

		$this->core->register();
		foreach ( $this->extensions as $extension ) {
			call_user_func( $extension, $this );
		}
		$this->initialized = true;
	}
	/**
	 * Get the core instance managing the main plugin functionality.
	 *
	 * @return Core
	 */
	public function core(): Core {
		return $this->core;
	}

	/**
	 * Queue an extension initializer for the shared Includes lifecycle.
	 *
	 * Extensions registered after initialization are invoked immediately.
	 *
	 * @param callable $extension Callback receiving this Includes instance.
	 * @return self
	 */
	public function register_extension( callable $extension ): self {
		if ( $this->initialized ) {
			call_user_func( $extension, $this );
		} else {
			$this->extensions[] = $extension;
		}

		return $this;
	}

	/**
	 * Attach Core registration to an external PluginName loader.
	 *
	 * @param WPLoader $loader Loader owned by the main runtime or an extension.
	 * @param string   $hook WordPress action name.
	 * @param int      $priority Hook priority.
	 * @return self
	 */
	public function register_hooks( WPLoader $loader, string $hook = 'init', int $priority = 10 ): self {
		$this->core->register_hooks( $loader, $hook, $priority );
		return $this;
	}

	/**
	 * Check if the Includes instance has been initialized.
	 *
	 * @return bool
	 */
	public function is_initialized(): bool {
		return $this->initialized;
	}
}



