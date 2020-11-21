<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Jobs;

use Klapuch\Lock;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Tracy;

final class Run extends Console\Command\Command {
	private Tracy\ILogger $logger;
	/** @var array<string, Job> */
	private array $jobs;

	public function __construct(Tracy\ILogger $logger, Job ...$jobs) {
		parent::__construct();
		$this->logger = $logger;
		$jobs = array_combine(
			array_map(static fn (Job $job): string => $job->getName(), $jobs),
			$jobs,
		);
		assert(is_array($jobs));
		$this->jobs = $jobs;
	}

	public static function getDefaultName(): string {
		return 'jobs:run';
	}

	protected function configure(): void {
		$this->setDescription('Run all CRON jobs.')
			->addOption('all', null, Input\InputOption::VALUE_NONE, 'Start all jobs as child processes.')
			->addArgument('name', Input\InputArgument::OPTIONAL, '(INTERNAL) One job as child process.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$all = $input->getOption('all') === true;
		$name = $input->getArgument('name');
		assert(is_string($name));
		if (($all && $name !== '') || (!$all && $name === '')) {
			$output->writeln('<fg=red>Bad combination - use "name" and "--all" separately.</>');
			return 1;
		} elseif ($all) {
			foreach ($this->jobs as $job) {
				Process::fromShellCommandline(
					sprintf(
						'/usr/local/bin/php %s %s %s &', // full-path - security + CRON
						escapeshellarg($_SERVER['argv'][0]),
						escapeshellarg($_SERVER['argv'][1]),
						escapeshellarg($job->getName()),
					),
				)
					->mustRun();
			}
		} else {
			$job = $this->jobs[$name];
			$semaphore = new Lock\Semaphore($name, 1);
			if ($job->scheduled() && $semaphore->tryAcquire()) {
				try {
					return $job->execute($input, $output);
				} catch (\Throwable $e) {
					$this->logger->log($e, Tracy\ILogger::EXCEPTION);
					throw $e;
				} finally {
					$semaphore->release();
				}
			}
		}
		return 0;
	}
}
