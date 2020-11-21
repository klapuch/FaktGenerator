<?php
declare(strict_types = 1);

namespace FaktGenerator\Tracy;

use Elasticsearch;
use Tracy;

/**
 * Before using, create index:
 * curl -XPUT localhost:9200/logs -H 'Content-Type: application/json' -d '{
 *   "settings" : {
 *     "index" : {
 *       "number_of_shards" : 4,
 *       "number_of_replicas" : 0
 *     }
 *   },
 *   "mappings" : {
 *     "properties" : {
 *       "timestamp" : { "type" : "date" }
 *     }
 *   }
 * }'
 */
final class ElasticLogger implements Tracy\ILogger {
	public const INDEX = 'logs';
	private const TYPE = 'php';
	private const TIMESTAMP = DATE_ATOM;
	private Tracy\ILogger $origin;
	private Elasticsearch\Client $elasticSearch;

	public function __construct(Tracy\ILogger $origin, Elasticsearch\Client $elasticSearch) {
		$this->origin = $origin;
		$this->elasticSearch = $elasticSearch;
	}

	/**
	 * @param mixed $message
	 * @param string|mixed $level
	 */
	public function log($message, $level = self::INFO): ?string {
		$exceptionFile = $this->origin->log($message, sprintf('%s-%s', $level, date('Y-m-d')));
		$this->elasticSearch->index([
			'index' => self::INDEX,
			'body' => [
				'server' => gethostname(),
				'timestamp' => date(self::TIMESTAMP),
				'message' => sprintf('%s @ %s', Tracy\Logger::formatMessage($message), Tracy\Helpers::getSource()),
				'filename' => $exceptionFile === null ? null : basename($exceptionFile),
				'level' => $level,
				'type' => self::TYPE,
			],
		]);
		return $exceptionFile;
	}
}
