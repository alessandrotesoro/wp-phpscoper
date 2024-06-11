<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Adds support for WordPress.
 *
 * @package   Sematico\wp-phpscoper
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\Scoper\Modules\Support;

/**
 * Adds support for WordPress core.
 */
class WordPress extends AbstractSupported {
	/**
	 * Initializes the WordPress package.
	 */
	public function __construct() {
		parent::__construct(
			'wordpress', // phpcs:ignore
			'WordPress',
			[
				'vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
				'vendor/php-stubs/wordpress-globals/wordpress-globals.php',
			],
		);
	}
}
