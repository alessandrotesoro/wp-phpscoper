<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Reports the generate:exclusions command.
 *
 * @package   Sematico\wp-phpscoper
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\Scoper\Console\Command;

use Exception;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use Sematico\Scoper\Generator;
use Sematico\Scoper\Modules\Support\Manager;
use Sematico\Scoper\Modules\Support\PackageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generates a list of php-scoper exclusions for php-scoper.
 *
 * The exclusions are based off of the WordPress core codebase
 * and some of the most popular plugins.
 */
#[AsCommand(
	name: 'generate:exclusions',
	description: 'Generates a list of php-scoper exclusions.',
	hidden: false,
)]
class ExclusionsCommand extends Command {
	/**
	 * Defines the output directory for the exclusions.
	 *
	 * @var string
	 */
	protected $outputDirectory = '.phpscoper';

	/**
	 * Adds the command options.
	 *
	 * @return void
	 */
	protected function configure(): void {
		$this->addOption(
			'output',
			'o',
			InputOption::VALUE_REQUIRED,
			'The output directory for the exclusions.',
			'.phpscoper'
		);
	}

	/**
	 * Generates a list of exclusions for php-scoper.
	 *
	 * @param InputInterface  $input  Input interface.
	 * @param OutputInterface $output Output interface.
	 * @return int
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$io = new SymfonyStyle( $input, $output );
		$io->title( 'PHP-Scoper exclusions generator' );

		$this->outputDirectory = $input->getOption( 'output' );

		// Ask confirmation.
		$io->text( 'This will generate a list of exclusions for php-scoper.' );

		if ( $io->confirm( 'Do you want to continue?' ) ) {
			try {
				$this->generateExclusions( $input, $output );
			} catch ( Exception $e ) {
				$io->error( $e->getMessage() );
				return Command::FAILURE;
			}
		} else {
			$io->error( 'Exiting...' );
			return Command::FAILURE;
		}

		$io->writeln( '' );
		$io->success( 'Exclusions generated successfully.' );

		return Command::SUCCESS;
	}

	/**
	 * Returns the output directory.
	 *
	 * @return string
	 */
	private function getOutputDirectory(): string {
		return getcwd() . DIRECTORY_SEPARATOR . $this->outputDirectory;
	}

	/**
	 * Generates a list of exclusions for php-scoper.
	 *
	 * @param InputInterface  $input  Input interface.
	 * @param OutputInterface $output Output interface.
	 * @return void
	 */
	private function generateExclusions( InputInterface $input, OutputInterface $output ): void {
		$io = new SymfonyStyle( $input, $output );

		$chosenPackages = $io->choice(
			'Select the packages you want to exclude. You can select multiple packages by separating them with a comma',
			$this->getSupportedPackages(),
			null,
			true,
		);

		$chosenPackages = $this->getPackagesFromChoices( $chosenPackages );

		// Verify that files exist.
		$this->checkPackagesFiles( $chosenPackages, $io );

		$io->text(
			sprintf(
				'Generating exclusions from %d files...',
				count( $this->getAllFilesFromPackages( $chosenPackages ) )
			)
		);

		$this->startGenerator( $chosenPackages, $io );
	}

	/**
	 * Returns the list of supported packages
	 * for the choice prompt by using the
	 * package slugs and names.
	 *
	 * @return array
	 */
	private function getSupportedPackages(): array {
		$manager   = new Manager();
		$supported = $manager->getSupported();

		return array_map(
			function ( PackageInterface $package ) {
				return $package->getName();
			},
			$supported
		);
	}

	/**
	 * Returns the list of packages from the
	 * list of choices.
	 *
	 * @param array $choices The list of choices.
	 * @return array
	 */
	private function getPackagesFromChoices( array $choices ): array {
		$manager  = new Manager();
		$packages = [];

		foreach ( $choices as $choice ) {
			$package = $manager->getPackageByName( $choice );
			if ( $package ) {
				$packages[] = $package;
			}
		}

		return $packages;
	}

	/**
	 * Checks if the files from the chosen packages
	 * are valid.
	 *
	 * If files are not valid, the command will exit with
	 * a failure status and an error message.
	 *
	 * @param PackageInterface[] $packages The list of packages.
	 * @param SymfonyStyle       $io        The Symfony style interface.
	 * @throws Exception If the files are not valid. The command will exit with a failure status and an error message.
	 * @return void
	 */
	private function checkPackagesFiles( array $packages, SymfonyStyle $io ): void {
		foreach ( $packages as $package ) {
			$files = $package->getFiles();

			if ( ! $files ) {
				throw new Exception(
					sprintf(
						'The package "%s" has no files.',
						$package->getName() // phpcs:ignore
					)
				);
			}

			foreach ( $files as $file ) {
				if ( ! file_exists( $file ) ) {
					throw new Exception(
						sprintf(
							'The file "%s" does not exist.',
							$file // phpcs:ignore
						)
					);
				}
			}
		}

		$io->text( 'All files are valid.' );
	}

	/**
	 * Returns the list of all files from the
	 * list of packages.
	 *
	 * @param PackageInterface[] $packages The list of packages.
	 * @return array
	 */
	private function getAllFilesFromPackages( array $packages ): array {
		$files = array_reduce(
			$packages,
			function ( array $files, PackageInterface $package ) {
				return array_merge( $files, $package->getFiles() );
			},
			[]
		);

		$files = array_unique( $files );

		return $files;
	}

	/**
	 * Generates the exclusions for the given packages.
	 *
	 * @param PackageInterface[] $packages The list of packages.
	 * @param SymfonyStyle       $io        The Symfony style interface.
	 * @return void
	 */
	private function startGenerator( array $packages, SymfonyStyle $io ): void {
		$emulate_version = '8.1';
		$parser          = ( new ParserFactory() )->createForVersion( PhpVersion::fromString( $emulate_version ) );

		foreach ( $packages as $package ) {
			$files     = $package->getFiles();
			$generator = new Generator( $parser, $this->getOutputDirectory(), $package );

			foreach ( $files as $file ) {
				$io->text( sprintf( 'Generating exclusions for "%s"...', basename( $file ) ) );
				$generator->dumpAsJson( $file, false );
			}
		}
	}
}
