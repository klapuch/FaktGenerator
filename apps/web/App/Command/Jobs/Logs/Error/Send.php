<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Jobs\Logs\Error;

use Elasticsearch;
use FaktGenerator\Command\Jobs\Job;
use FaktGenerator\Tracy\ElasticLogger;
use Klapuch\Configuration;
use Nette\Mail;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Send extends Console\Command\Command implements Job {
	private Elasticsearch\Client $elasticsearch;
	private Configuration\Source $configuration;

	public function __construct(Elasticsearch\Client $elasticsearch, Configuration\Source $configuration) {
		parent::__construct();
		$this->elasticsearch = $elasticsearch;
		$this->configuration = $configuration;
	}

	public function scheduled(): bool {
		return true;
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$last = self::last();
		$results = $this->elasticsearch->search([
			'index' => ElasticLogger::INDEX,
			'body' => [
				'size' => 1000,
				'query' => [
					'range' => [
						'timestamp' => [
							'gte' => date(DATE_ATOM, $last),
							'lte' => 'now',
						],
					],
				],
				'sort' => [
					'timestamp' => ['order' => 'desc'],
				],
			],
		]);
		$total = $results['hits']['total']['value'];
		if ($total !== 0) {
			$logs = $results['hits']['hits'];
			['logs' => ['email' => $email]] = $this->configuration->read();
			(new Mail\SendmailMailer())->send(
				(new Mail\Message())
					->setSubject(sprintf('Logs (%d)', $total))
					->setFrom($email, 'Mr. Log')
					->addTo($email, 'FaktGenerator')
					->setHtmlBody(self::html($logs)),
			);
		}
		self::refresh();
		return 0;
	}

	public static function getDefaultName(): string {
		return 'jobs:logs:error:send';
	}

	protected function configure(): void {
		$this->setDescription('Send error logs to email.');
	}

	/**
	 * @param mixed[] $logs
	 * @return string
	 */
	private static function html(array $logs): string {
		$document = new \DOMDocument('1.0', 'UTF-8');
		$logsElement = $document->createElement('logs');
		foreach ($logs as $log) {
			$logElement = $document->createElement('log', $log['_source']['message']);
			$logElement->setAttribute('timestamp', date('Y-m-d H:i:s', strtotime($log['_source']['timestamp'])));
			$logElement->setAttribute('type', $log['_source']['type']);
			if ($log['_source']['level'] !== null) {
				$logElement->setAttribute('level', $log['_source']['level']);
			}
			if ($log['_source']['filename'] !== null) {
				$logElement->setAttribute('filename', $log['_source']['filename']);
			}
			$logsElement->appendChild($logElement);
		}

		$document->appendChild($logsElement);

		$xsl = new \DOMDocument('1.0', 'UTF-8');
		$xsl->load(__DIR__ . '/../../templates/logsSend.xsl');
		$xslt = new \XSLTProcessor();
		$xslt->importStylesheet($xsl);
		$content = $xslt->transformToXml($document);
		if ($content === false) {
			throw new \RuntimeException('Failed to transform.');
		}
		return $content;
	}

	private static function filename(): string {
		return __DIR__ . sprintf('/../../../../../data/%s.time', str_replace(':', '_', self::getDefaultName()));
	}

	private static function last(): int {
		$filename = self::filename();
		if (is_file($filename)) {
			$content = @file_get_contents($filename);
			if ($content === false) {
				throw new \RuntimeException(sprintf('Can not read from file "%s"', $filename));
			}
		} else {
			$content = time();
		}
		return (int) $content;
	}

	private static function refresh(): void {
		if (@file_put_contents(self::filename(), (string) time()) === false) {
			throw new \RuntimeException(sprintf('Can not write to file "%s"', self::filename()));
		}
	}
}
