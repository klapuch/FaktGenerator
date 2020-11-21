<?php
declare(strict_types = 1);

use FaktGenerator\Command;
use FaktGenerator\Command\Jobs;
use FaktGenerator\Configuration;
use Klapuch\Storage;
use Symfony\Component\Console;

require __DIR__ . '/../../vendor/autoload.php';

$configuration = new Configuration\ApplicationConfiguration();
$rawConfiguration = $configuration->read();

$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($rawConfiguration['elasticsearch']['hosts'])
	->build();
$logger = new FaktGenerator\Tracy\ElasticLogger(new Tracy\Logger(__DIR__ . '/../../logs'), $elasticsearch);

$connection = new Storage\CachedConnection(
	new Storage\PDOConnection(
		new Storage\SafePDO(
			$rawConfiguration['database']['dsn'],
			$rawConfiguration['database']['user'],
			$rawConfiguration['database']['password'],
			[
				PDO::ATTR_TIMEOUT => 2, // 2 seconds
			],
		),
	),
	new SplFileInfo(__DIR__ . '/../temp/db'),
);

$application = new Console\Application('Command');
$application->setCatchExceptions(false);
$jobs = [
	new Jobs\Logs\Error\Send($elasticsearch, $configuration),
	new Jobs\Logs\Error\Delete($elasticsearch),
	new Jobs\Logs\Command\Delete(),
];
$application->addCommands([
	new Command\Jobs\Run($logger, ...$jobs),
	new Command\Build\Assets(),
	new Command\Build\Domains(),
	new Command\Scraping\Import($connection),
	...$jobs,
]);

$output = new FaktGenerator\Symfony\Console\Output\MultiOutput(
	new FaktGenerator\Symfony\Console\Output\TimeOutput(),
	new FaktGenerator\Symfony\Console\Output\FileOutput(__DIR__ . '/logs'),
);

try {
	$application->run(null, $output);
} catch (\Throwable $e) {
	$application->renderThrowable($e, $output);
	$logger->log($e, Tracy\Logger::EXCEPTION);
}
