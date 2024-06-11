<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Adds the setup command.
 *
 * @package   Sematico\wp-phpscoper
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */

namespace Sematico\Scoper\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Runs specific composer commands to install php-scoper.
 */
#[AsCommand(
	name: 'setup',
	description: 'Runs specific composer commands to install php-scoper.',
	hidden: false,
)]
class SetupCommand extends Command {
	/**
	 * Runs the setup command.
	 *
	 * @param InputInterface  $input  Input interface.
	 * @param OutputInterface $output Output interface.
	 * @return int
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$io = new SymfonyStyle( $input, $output );
		$io->title( 'PHP-Scoper Setup' );

		// Ask confirmation.
		$io->text( 'This will run specific composer commands to install php-scoper.' );

		if ( $io->confirm( 'Do you want to continue?' ) ) {
			$this->runCommand( 'composer config allow-plugins.bamarni/composer-bin-plugin true', $input, $output );
			$this->runCommand( 'composer require bamarni/composer-bin-plugin --dev', $input, $output );
			$this->runCommand( 'composer bin php-scoper config minimum-stability dev', $input, $output );
			$this->runCommand( 'composer bin php-scoper config prefer-stable true', $input, $output );
			$this->runCommand( 'composer bin php-scoper require --dev humbug/php-scoper', $input, $output );
		} else {
			$io->error( 'Exiting...' );
			return Command::FAILURE;
		}

		$io->writeln( '' );
		$io->success( 'Setup completed successfully.' );

		return Command::SUCCESS;
	}

	/**
	 * Runs a command.
	 *
	 * @param string          $command The composer command.
	 * @param InputInterface  $input  Input interface.
	 * @param OutputInterface $output Output interface.
	 * @return void
	 */
	private function runCommand( string $command, InputInterface $input, OutputInterface $output ): void {
		$process = new Process( explode( ' ', $command ) );
		$process->setWorkingDirectory( getcwd() );

		$process->run(
			function ( string $type, string $buffer ) use ( $output ) {
				$output->write( $buffer );
			}
		);
	}
}
