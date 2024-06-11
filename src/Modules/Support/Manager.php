<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Handles the support module.
 *
 * @package   Sematico\wp-phpscoper
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\Scoper\Modules\Support;

/**
 * Manages the list of supported packages
 * that exclusions are generated for.
 */
class Manager {
	/**
	 * The list of supported packages.
	 *
	 * @var array
	 */
	protected $supported = [];

	/**
	 * Initializes the support module.
	 */
	public function __construct() {
		$this->addSupported( new WordPress() );
	}

	/**
	 * Adds a supported package.
	 *
	 * @param AbstractSupported $supported The supported package.
	 */
	public function addSupported( AbstractSupported $supported ): void {
		$this->supported[] = $supported;
	}

	/**
	 * Returns the list of supported packages.
	 *
	 * @return array
	 */
	public function getSupported(): array {
		return $this->supported;
	}

	/**
	 * Returns a supported package by name.
	 *
	 * @param string $name The name of the package.
	 * @return AbstractSupported|null
	 */
	public function getPackageByName( string $name ): ?AbstractSupported {
		foreach ( $this->supported as $supported ) {
			if ( $supported->getName() === $name ) {
				return $supported;
			}
		}
		return null;
	}
}
