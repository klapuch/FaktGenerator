<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Jobs\Logs\Command;

use FaktGenerator\Command\Jobs\Job;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Delete extends Console\Command\Command implements Job {
	private const OLDER_THAN = '-20 days';
	private const EMPTY_OLDER_THAN = '-2 days';

	public function scheduled(): bool {
		return date('N H:i') === '1 01:10'; // monday at 01:10
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$files = new \CallbackFilterIterator(
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(
					__DIR__ . '/../logs',
					\RecursiveDirectoryIterator::SKIP_DOTS,
				),
			),
			static fn (\SplFileInfo $file): bool => (
				$file->getMTime() <= strtotime(self::OLDER_THAN)
				|| ($file->getSize() === 0 && $file->getMTime() <= strtotime(self::EMPTY_OLDER_THAN))
			),
		);
		$total = 0;
		foreach ($files as $file) {
			++$total;
			assert($file instanceof \SplFileInfo);
			$output->writeln(sprintf('Removing "%s"', $file->getRealPath()), OutputInterface::VERBOSITY_VERBOSE);
			@unlink($file->getPathname());
		}
		$output->writeln(sprintf('Removed "%d" logs', $total), OutputInterface::VERBOSITY_VERBOSE);
		return 0;
	}

	public static function getDefaultName(): string {
		return 'jobs:logs:command:delete';
	}

	protected function configure(): void {
		$this->setDescription(sprintf('Delete command logs from filesystem older than %s', self::OLDER_THAN));
	}
}
