<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Exclusions generator handler.
 *
 * Code in this class has been ported from
 * https://github.com/snicco/php-scoper-excludes/
 *
 * All credit goes to the original author.
 * https://github.com/snicco/
 *
 * The code has been modified to fit the needs of this project.
 */

namespace Sematico\Scoper;

use Closure;
use RuntimeException;
use PhpParser\Parser;
use PhpParser\NodeTraverser;
use InvalidArgumentException;
use PhpParser\NodeVisitor\NameResolver;
use Sematico\Scoper\Modules\NodeVisitor\Filter;
use Sematico\Scoper\Modules\NodeVisitor\Categorize;
use Sematico\Scoper\Modules\Support\PackageInterface;

use function count;
use function is_dir;
use function pathinfo;
use function str_replace;
use function is_writable; // phpcs:ignore
use function is_readable;
use function json_encode; // phpcs:ignore
use function array_filter;
use function file_get_contents;
use function file_put_contents; // phpcs:ignore

use const JSON_PRETTY_PRINT;
use const PATHINFO_FILENAME;
use const PATHINFO_EXTENSION;
use const JSON_THROW_ON_ERROR;

/**
 * Generates exclusions from PHP files.
 */
class Generator {

	const STMT_FUNCTION  = 'function';
	const STMT_CLASS     = 'class';
	const STMT_CONST     = 'const';
	const STMT_TRAIT     = 'trait';
	const STMT_INTERFACE = 'interface';

	/**
	 * The parser to use.
	 *
	 * @var Parser
	 */
	private Parser $parser;

	/**
	 * The root directory to use to store the exclusions.
	 *
	 * @var string
	 */
	private string $root_dir;

	/**
	 * The package to use.
	 *
	 * @var PackageInterface
	 */
	private PackageInterface $package;

	/**
	 * Generator constructor.
	 *
	 * @param Parser           $parser The parser to use.
	 * @param string           $root_dir The root directory to use to store the exclusions.
	 * @param PackageInterface $package The package to use.
	 * @throws InvalidArgumentException If the root directory is not readable or writable.
	 */
	public function __construct( Parser $parser, string $root_dir, PackageInterface $package ) {
		if ( ! is_dir( $root_dir )) {
			throw new InvalidArgumentException( "Directory [$root_dir] does not exist." ); // phpcs:ignore
		}

		if ( ! is_writable( $root_dir )) { // phpcs:ignore
			throw new InvalidArgumentException( "Directory [$root_dir] is not writable." ); // phpcs:ignore
		}

		$this->parser   = $parser;
		$this->root_dir = $root_dir;
		$this->package  = $package;
	}

	/**
	 * Dumps the exclusions to a JSON file.
	 *
	 * @param string $file The file to dump the exclusions to.
	 * @param bool   $include_empty Whether to include empty files.
	 * @throws InvalidArgumentException If the file is not readable or writable.
	 */
	public function dumpAsJson( string $file, bool $include_empty = true ): void {
		$this->dump(
			$file,
			function ( array $excludes, $file_path ) {
				$json = json_encode( $excludes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT );
				return file_put_contents( $file_path, $json ); // phpcs:ignore
			},
			'.json',
			$include_empty
		);
	}

	/**
	 * Dumps the exclusions to files.
	 *
	 * @param string  $file The file to dump the exclusions to.
	 * @param Closure $save_do_disk The function to use to save the exclusions to disk.
	 * @param string  $file_extension The file extension to use.
	 * @param bool    $include_empty Whether to include empty files.
	 * @throws InvalidArgumentException If the file is not readable or writable.
	 */
	private function dump( string $file, Closure $save_do_disk, string $file_extension, bool $include_empty ): void {
		if ( ! is_readable( $file )) {
			throw new InvalidArgumentException( "File [$file] is not readable." ); // phpcs:ignore
		}

		if ('php' !== pathinfo( $file, PATHINFO_EXTENSION )) {
			throw new InvalidArgumentException(
				"Only PHP files can be processed.\nCant process file [$file]." // phpcs:ignore
			);
		}

		$content = file_get_contents( $file ); // phpcs:ignore

		if (false === $content) {
			throw new RuntimeException( "Cant read file contents of file [$file]." ); // phpcs:ignore
		}

		$exclude_list = $this->generateExcludeList( $content );

		$base_name = pathinfo( $file, PATHINFO_FILENAME );

		if ( ! $include_empty) {
			$exclude_list = array_filter( $exclude_list, fn( array $arr ) => count( $arr ) );
		}

		foreach ($exclude_list as $type => $excludes) {
			$path    = $this->getFileName( $type, $file_extension );
			$success = $save_do_disk( $excludes, $path );

			if (false === $success) {
				throw new RuntimeException( "Could not dump contents for file [$base_name]." ); // phpcs:ignore
			}
		}
	}

	/**
	 * @return array<string,string[]>
	 */
	private function generateExcludeList( string $file_contents ): array {
		$node_traverser = new NodeTraverser();
		$node_traverser->addVisitor( new Filter() );
		$node_traverser->addVisitor( new NameResolver() );
		// The order is important.
		$node_traverser->addVisitor( $categorizing_visitor = new Categorize() ); // phpcs:ignore

		$ast = $this->parser->parse( $file_contents );
		$node_traverser->traverse( $ast );

		return [
			self::STMT_CLASS     => $categorizing_visitor->classes(),
			self::STMT_INTERFACE => $categorizing_visitor->interfaces(),
			self::STMT_FUNCTION  => $categorizing_visitor->functions(),
			self::STMT_TRAIT     => $categorizing_visitor->traits(),
			self::STMT_CONST     => $categorizing_visitor->constants(),
		];
	}

	/**
	 * Gets the file name to use for the exclusions.
	 *
	 * @param string $key The type of exclusions to get the file name for.
	 * @param string $extension The file extension.
	 * @return string The file name to use.
	 * @throws RuntimeException If the file name is not valid.
	 */
	private function getFileName( string $key, string $extension ): string {
		$file_basename = $this->package->getSlug();

		switch ($key) {
			case self::STMT_FUNCTION:
				return $this->root_dir . "/exclude-$file_basename-functions$extension";
			case self::STMT_CLASS:
				return $this->root_dir . "/exclude-$file_basename-classes$extension";
			case self::STMT_INTERFACE:
				return $this->root_dir . "/exclude-$file_basename-interfaces$extension";
			case self::STMT_CONST:
				return $this->root_dir . "/exclude-$file_basename-constants$extension";
			case self::STMT_TRAIT:
				return $this->root_dir . "/exclude-$file_basename-traits$extension";
			default:
				throw new RuntimeException( "Unknown exclude identifier [$key]." ); // phpcs:ignore
		}
	}
}
