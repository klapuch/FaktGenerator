<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Jobs\Logs\Error;

use Elasticsearch;
use FaktGenerator\Command\Jobs\Job;
use FaktGenerator\Tracy\ElasticLogger;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Delete extends Console\Command\Command implements Job {
	private const OLDER_THAN = '-1 month';
	private Elasticsearch\Client $elasticsearch;

	public function __construct(Elasticsearch\Client $elasticsearch) {
		parent::__construct();
		$this->elasticsearch = $elasticsearch;
	}

	public function scheduled(): bool {
		return date('N H:i') === '1 01:00'; // monday at 01:00
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$result = $this->elasticsearch->deleteByQuery([
			'index' => ElasticLogger::INDEX,
			'body' => [
				'query' => [
					'range' => [
						'timestamp' => [
							'lte' => (new \DateTime(self::OLDER_THAN))->format(DATE_ATOM),
						],
					],
				],
			],
		]);
		$output->writeln(sprintf('Total logs removed: %d', $result['deleted']), $output::VERBOSITY_VERBOSE);
		return 0;
	}

	public static function getDefaultName(): string {
		return 'jobs:logs:error:delete';
	}

	protected function configure(): void {
		$this->setDescription(sprintf('Delete error logs from ElasticSearch older than %s', self::OLDER_THAN));
	}
}
