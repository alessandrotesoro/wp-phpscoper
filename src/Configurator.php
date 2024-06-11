<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Handles the configurator class.
 *
 * @package   Sematico\wp-phpscoper
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\Scoper;

use Sematico\Scoper\Modules\Support\Manager;
use Sematico\Scoper\Modules\Support\PackageInterface;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Class Configurator
 */
class Configurator {
	/**
	 * The namespace prefix to use.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * The finders to use.
	 *
	 * @var Finder[]
	 */
	protected $finders;

	/**
	 * The patchers to use.
	 *
	 * @var callable[]
	 */
	protected $patchers = [];

	/**
	 * Initializes the configurator class.
	 *
	 * @param string $prefix The namespace prefix to use.
	 */
	public function __construct( string $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Returns the namespace prefix to use.
	 *
	 * @return string
	 */
	public function getPrefix(): string {
		return $this->prefix;
	}

	/**
	 * Returns the list of supported packages.
	 *
	 * @return PackageInterface[]
	 */
	public function getSupportedPackages(): array {
		$manager = new Manager();

		return $manager->getSupported();
	}

	/**
	 * Returns the list of possible file names.
	 *
	 * @param PackageInterface $package The package to get the possible file names for.
	 * @return string[]
	 */
	private function getPossibleFileNames( PackageInterface $package ): array {
		return [
			'exclude-' . $package->getName() . '-classes.json',
			'exclude-' . $package->getName() . '-constants.json',
			'exclude-' . $package->getName() . '-functions.json',
			'exclude-' . $package->getName() . '-interfaces.json',
			'exclude-' . $package->getName() . '-traits.json',
		];
	}

	/**
	 * Check if files exists in the `.phpscoper` folder
	 * of the current working directory and return
	 * them into an array.
	 *
	 * @param PackageInterface $package The package to get the generated files for.
	 * @return string[]
	 */
	public function getGeneratedFiles( PackageInterface $package ): array {
		$files = [];

		foreach ( $this->getPossibleFileNames( $package ) as $file ) {
			$path = $this->getPath( $file );

			if ( file_exists( $path ) ) {
				$files[] = $path;
			}
		}

		// Use array_map to replace slashes with DIRECTORY_SEPARATOR for each path
		$files = array_map(
			function ( $path ) {
				return str_replace( '/', DIRECTORY_SEPARATOR, $path );
			},
			$files
		);

		return $files;
	}

	/**
	 * Returns the path to the `.phpscoper` folder
	 * of the current working directory relative to
	 * the given file.
	 *
	 * @param string $file The file to get the path for.
	 * @return string
	 */
	private function getPath( string $file ): string {
		return getcwd() . '/.phpscoper/' . $file;
	}

	/**
	 * Returns the list of default patchers.
	 *
	 * @return callable[]
	 */
	public function getDefaultPatchers(): array {
		return [
			function ( string $file_path, string $prefix, string $contents ): string {
				// Change the contents here.
				return str_replace(
					'Symfony\\\\',
					sprintf( '%s\\\\Symfony\\\\', addslashes( $prefix ) ),
					$contents
				);
			},
			// Search for the string "\__" and replace it with "__".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					'\\__',
					'__',
					$contents
				);
			},
			// Search for the string "\_e" and replace it with "_e".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					'\\_e',
					'_e',
					$contents
				);
			},
			// Search for the string "\_n" and replace it with "_n".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					'\\_n',
					'_n',
					$contents
				);
			},
			// Search for the string "\_x" and replace it with "_x".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					'\\_x',
					'_x',
					$contents
				);
			},
			// Search for the string "\_nx" and replace it with "_nx".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					'\\_nx',
					'_nx',
					$contents
				);
			},
			// Search for the string "\esc_attr__" and replace it with "esc_attr__".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_attr__",
					'esc_attr__',
					$contents
				);
			},
			// Search for the string "\esc_attr_e" and replace it with "esc_attr_e".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_attr_e",
					'esc_attr_e',
					$contents
				);
			},
			// Search for the string "\esc_attr_x" and replace it with "esc_attr_x".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_attr_x",
					'esc_attr_x',
					$contents
				);
			},
			// Search for the string "\esc_html__" and replace it with "esc_html__".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_html__",
					'esc_html__',
					$contents
				);
			},
			// Search for the string "\esc_html_e" and replace it with "esc_html_e".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_html_e",
					'esc_html_e',
					$contents
				);
			},
			// Search for the string "\esc_html_x" and replace it with "esc_html_x".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_html_x",
					'esc_html_x',
					$contents
				);
			},
			// Search for the string "\esc_html_e" and replace it with "esc_html_e".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_html_e",
					'esc_html_e',
					$contents
				);
			},
			// Search for the string "\esc_html_x" and replace it with "esc_html_x".
			function ( string $file_path, string $prefix, string $contents ): string {
				return str_replace(
					"\\esc_html_x",
					'esc_html_x',
					$contents
				);
			},
		];
	}

	/**
	 * Returns a Finder instance.
	 *
	 * @param string[] $folders The folders to search in.
	 * @param string[] $assets The assets to search in.
	 * @param string   $exclude_assets The assets to exclude.
	 * @return Finder
	 */
	public static function makeFinder( $folders = [], $assets = [], $exclude_assets = '' ): Finder {
		if ( ! empty( $assets ) ) {
			$assets_finder = Finder::create();
			$assets_finder
			->in( $assets )
			->filter(
				static function ( SplFileInfo $file ) {
					return in_array( $file->getExtension(), [ 'css', 'js', 'woff', 'woff2', 'txt', 'php', 'json' ], true );
				}
			);

			if ( ! empty( $exclude_assets ) ) {
				$assets_finder->exclude( $exclude_assets );
			}

			$assets = array_keys( \iterator_to_array( $assets_finder ) );
		}

		$finder = Finder::create()->
			files()->
			ignoreVCS( true )->
			ignoreDotFiles( true )->
			notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.(json|lock)/' )->
			exclude(
				[
					'doc',
					'test',
					'build',
					'test_old',
					'tests',
					'Tests',
					'vendor-bin',
					'wp-coding-standards',
					'squizlabs',
					'phpcompatibility',
					'dealerdirect',
					'bin',
					'vendor',
					'deprecated',
					'mu-plugins',
					'plugin-boilerplate',
					'templates',
				]
			)->
			in( $folders )->
			name( [ '*.php' ] );

		if ( ! empty( $assets ) ) {
			$finder->append( $assets );
		}

		return $finder;
	}

	/**
	 * Returns the finders to use.
	 *
	 * @return Finder[]
	 */
	public function getFinders(): array {
		return $this->finders;
	}

	/**
	 * Adds a finder to the list of finders.
	 *
	 * @param Finder $finder The finder to add.
	 * @return self
	 */
	public function addFinder( Finder $finder ): self {
		$this->finders[] = $finder;

		return $this;
	}

	/**
	 * Returns the patchers to use.
	 *
	 * @return callable[]
	 */
	public function getPatchers(): array {
		return $this->patchers;
	}

	/**
	 * Adds a patcher to the list of patchers.
	 *
	 * @param callable $patcher The patcher to add.
	 * @return self
	 */
	public function addPatcher( callable $patcher ): self {
		$this->patchers[] = $patcher;

		return $this;
	}

	/**
	 * Returns the list of all generated classes exclusions.
	 *
	 * @return array
	 */
	public function getAllGeneratedClassesExclusions(): array {
		$exclusions = [];
		$packages   = $this->getSupportedPackages();
		$files      = [];

		foreach ( $packages as $package ) {
			$files = $this->getGeneratedFiles( $package );
		}

		// Out of the files, get the ones that end with `-classes.json`.
		$files = array_filter(
			$files,
			static function ( string $file ) {
				return str_ends_with( $file, '-classes.json' );
			}
		);

		// Out of the files, decode the JSON and get the classes.
		foreach ( $files as $file ) {
			$classes = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( ! empty( $classes ) ) {
				$exclusions = array_merge( $exclusions, $classes );
			}
		}

		return $exclusions;
	}

	/**
	 * Returns the list of all generated functions exclusions.
	 *
	 * @return array
	 */
	public function getAllGeneratedFunctionsExclusions(): array {
		$exclusions = [];
		$packages   = $this->getSupportedPackages();
		$files      = [];

		foreach ( $packages as $package ) {
			$files = $this->getGeneratedFiles( $package );
		}

		// Out of the files, get the ones that end with `-functions.json`.
		$files = array_filter(
			$files,
			static function ( string $file ) {
				return str_ends_with( $file, '-functions.json' );
			}
		);

		// Out of the files, decode the JSON and get the functions.
		foreach ( $files as $file ) {
			$functions = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( ! empty( $functions ) ) {
				$exclusions = array_merge( $exclusions, $functions );
			}
		}

		return $exclusions;
	}

	/**
	 * Returns the list of all generated constants exclusions.
	 *
	 * @return array
	 */
	public function getAllGeneratedConstantsExclusions(): array {
		$exclusions = [];
		$packages   = $this->getSupportedPackages();
		$files      = [];

		foreach ( $packages as $package ) {
			$files = $this->getGeneratedFiles( $package );
		}

		// Out of the files, get the ones that end with `-constants.json`.
		$files = array_filter(
			$files,
			static function ( string $file ) {
				return str_ends_with( $file, '-constants.json' );
			}
		);

		// Out of the files, decode the JSON and get the constants.
		foreach ( $files as $file ) {
			$constants = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( ! empty( $constants ) ) {
				$exclusions = array_merge( $exclusions, $constants );
			}
		}

		return $exclusions;
	}

	/**
	 * Returns the list of all generated interfaces exclusions.
	 *
	 * @return array
	 */
	public function getAllGeneratedInterfacesExclusions(): array {
		$exclusions = [];
		$packages   = $this->getSupportedPackages();
		$files      = [];

		foreach ( $packages as $package ) {
			$files = $this->getGeneratedFiles( $package );
		}

		// Out of the files, get the ones that end with `-interfaces.json`.
		$files = array_filter(
			$files,
			static function ( string $file ) {
				return str_ends_with( $file, '-interfaces.json' );
			}
		);

		// Out of the files, decode the JSON and get the interfaces.
		foreach ( $files as $file ) {
			$interfaces = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( ! empty( $interfaces ) ) {
				$exclusions = array_merge( $exclusions, $interfaces );
			}
		}

		return $exclusions;
	}

	/**
	 * Returns the list of all generated traits exclusions.
	 *
	 * @return array
	 */
	public function getAllGeneratedTraitsExclusions(): array {
		$exclusions = [];
		$packages   = $this->getSupportedPackages();
		$files      = [];

		foreach ( $packages as $package ) {
			$files = $this->getGeneratedFiles( $package );
		}

		// Out of the files, get the ones that end with `-traits.json`.
		$files = array_filter(
			$files,
			static function ( string $file ) {
				return str_ends_with( $file, '-traits.json' );
			}
		);

		// Out of the files, decode the JSON and get the traits.
		foreach ( $files as $file ) {
			$traits = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( ! empty( $traits ) ) {
				$exclusions = array_merge( $exclusions, $traits );
			}
		}

		return $exclusions;
	}

	/**
	 * Returns the configuration for the scoper.
	 *
	 * @return array
	 */
	public function getScoperConfiguration(): array {
		return [
			'prefix'                  => $this->getPrefix(),
			'expose-global-constants' => false,
			'expose-global-classes'   => false,
			'expose-global-functions' => false,

			/**
			 * By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
			 * directory. You can however define which files should be scoped by defining a collection of Finders in the
			 * following configuration key.
			 *
			 * For more see: https://github.com/humbug/php-scoper#finders-and-paths.
			 */
			'finders'                 => $this->getFinders(),

			'exclude-classes'         => $this->getAllGeneratedClassesExclusions(),
			'exclude-functions'       => $this->getAllGeneratedFunctionsExclusions(),
			'exclude-constants'       => $this->getAllGeneratedConstantsExclusions(),
			'exclude-namespaces'      => $this->getAllGeneratedInterfacesExclusions(),

			'patchers'                => array_merge( $this->getDefaultPatchers(), $this->getPatchers() ),
		];
	}
}
