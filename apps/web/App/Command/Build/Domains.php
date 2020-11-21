<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Build;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Domains extends Console\Command\Command {
	private const OUTPUT = __DIR__ . '/../../Web/Pages/@domains.xml';

	public function execute(InputInterface $input, OutputInterface $output): int {
		$env = $input->getArgument('env');
		assert(is_string($env));
		$document = new \DOMDocument('1.0', 'UTF-8');
		$domainsElement = $document->createElement('domains');
		foreach (self::list($env) as $name => $domain) {
			$domainsElement->appendChild($document->createElement($name, $domain));
		}
		$document->appendChild($domainsElement);
		return $document->save(self::OUTPUT) === false ? 1 : 0;
	}

	public static function getDefaultName(): string {
		return 'build:domains';
	}

	protected function configure(): void {
		$this->setDescription('Build domains')
			->addArgument('env', InputArgument::REQUIRED, 'Env - one of [localhost]');
	}

	/**
	 * @return array<string, string>
	 */
	private static function list(string $env): array {
		switch ($env) {
			case 'localhost':
				return ['static' => 'http://static.faktgenerator.localhost'];
			default:
				throw new \RuntimeException('Invalid env');
		}
	}
}
