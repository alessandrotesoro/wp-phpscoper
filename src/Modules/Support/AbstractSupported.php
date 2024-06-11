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
abstract class AbstractSupported implements PackageInterface {
	/**
	 * The slug of the package.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The name of the package.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The stub files that are supported.
	 *
	 * @var array
	 */
	protected $files;

	/**
	 * Creates a new supported package.
	 *
	 * @param string $slug   The slug of the package.
	 * @param string $name   The name of the package.
	 * @param array  $files  The stub files that are supported.
	 */
	public function __construct( string $slug, string $name, array $files ) {
		$this->slug  = $slug;
		$this->name  = $name;
		$this->files = $files;
	}

	/**
	 * Returns the slug of the package.
	 *
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * Returns the name of the package.
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Returns the stub files that are supported
	 * relative to the current working directory.
	 *
	 * @return array
	 */
	public function getFiles(): array {
		$files = array_map(
			function ( string $file ) {
				return getcwd() . DIRECTORY_SEPARATOR . $file;
			},
			$this->files
		);

		// Use array_map to replace slashes with DIRECTORY_SEPARATOR for each path
		$files = array_map(
			function ( $path ) {
				return str_replace( '/', DIRECTORY_SEPARATOR, $path );
			},
			$files
		);

		return $files;
	}
}
