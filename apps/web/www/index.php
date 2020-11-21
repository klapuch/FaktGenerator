<?php
declare(strict_types = 1);

use FaktGenerator\Configuration;
use Klapuch\Storage;

require __DIR__ . '/../vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line): void {
	if (error_reporting() & $severity) {
		throw new ErrorException($message, 0, $severity, $file, $line);
	}
});

$configuration = new Configuration\ApplicationConfiguration();
$rawConfiguration = $configuration->read();

$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($rawConfiguration['elasticsearch']['hosts'])
	->build();
$logger = new FaktGenerator\Tracy\ElasticLogger(new Tracy\Logger(__DIR__ . '/../logs'), $elasticsearch);

try {
	$connection = new Storage\CachedConnection(
		new Storage\PDOConnection(
			new Storage\SafePDO(
				$rawConfiguration['database']['dsn'],
				$rawConfiguration['database']['user'],
				$rawConfiguration['database']['password'],
				[
					\PDO::ATTR_TIMEOUT => 2, // 2 seconds
				],
			),
		),
		new \SplFileInfo(__DIR__ . '/../temp/db'),
	);

	$endpoint = (new FaktGenerator\Web\Routing\ApplicationRoutes($connection))->match(sprintf('%s', $_SERVER['ROUTE_NAME']));
	parse_str($_SERVER['ROUTE_PARAM_QUERY'] ?? '', $parameters);
	$endpoint->response($parameters, $_GET)->render();
} catch (\Throwable $e) {
	$logger->log($e);
	if (getenv('FAKTGENERATOR_ENV') !== 'local') {
		(new FaktGenerator\Web\Routing\ApplicationRoutes($connection))->match('500')->response()->render();
	}
	throw $e;
}
