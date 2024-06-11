<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Base class for supported packages.
 *
 * @package   Sematico\wp-phpscoper
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\Scoper\Modules\Support;

/**
 * Base class for supported packages.
 */
interface PackageInterface {
	/**
	 * Returns the slug of the package.
	 *
	 * @return string
	 */
	public function getSlug(): string;

	/**
	 * Returns the name of the package.
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Returns the files that should be excluded.
	 *
	 * @return array
	 */
	public function getFiles(): array;
}
