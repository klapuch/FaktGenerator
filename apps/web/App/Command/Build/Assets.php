<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Build;

use Nette\Utils\FileSystem;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Assets extends Console\Command\Command {
	private const OUTPUT = __DIR__ . '/../../Web/Pages/@assets.xml';

	public function execute(InputInterface $input, OutputInterface $output): int {
		$env = $input->getArgument('env');
		assert(is_string($env));
		$document = new \DOMDocument('1.0', 'UTF-8');
		$assetsElement = $document->createElement('assets');
		$buildHash = md5(((string) time()) . mt_rand());
		foreach (self::files($document, $buildHash, $env) as $asset) {
			$assetsElement->appendChild($asset);
		}
		self::move($buildHash);
		$document->appendChild($assetsElement);
		return $document->save(self::OUTPUT) === false ? 1 : 0;
	}

	public static function getDefaultName(): string {
		return 'build:assets';
	}

	protected function configure(): void {
		$this->setDescription('Build assets')
			->addArgument('env', InputArgument::REQUIRED, 'Env - one of [localhost]');
	}

	private function move(string $hash): void {
		$buildDir = self::buildDir($hash);
		FileSystem::copy(__DIR__ . '/../../../static/assets/webfonts', sprintf('%s/webfonts', $buildDir));
		FileSystem::copy(__DIR__ . '/../../../static/assets/js', sprintf('%s/js', $buildDir));
		FileSystem::copy(__DIR__ . '/../../../static/assets/css', sprintf('%s/css', $buildDir));
	}

	private function files(\DOMDocument $document, string $hash, string $env): \AppendIterator {
		$assets = new \AppendIterator();
		$assets->append(self::css($document, self::domain($env), $hash));
		$assets->append(self::js($document, self::domain($env), $hash));
		return $assets;
	}

	private static function buildDir(string $hash): string {
		$dir = __DIR__ . sprintf('/../../../static/assets/public/assets/%s', $hash);
		FileSystem::createDir($dir);
		return $dir;
	}

	/**
	 * @return \Generator<\DOMElement>
	 */
	private static function css(\DOMDocument $document, string $domain, string $build): \Generator {
		$files = [
			sprintf('%s/assets/%s/css/font-awesome.min.css', $domain, $build),
			sprintf('%s/assets/%s/css/bulma.min.css', $domain, $build),
			sprintf('%s/assets/%s/css/tabs.css', $domain, $build),
			sprintf('%s/assets/%s/css/custom.css', $domain, $build),
		];
		foreach ($files as $url) {
			yield $document->createElement('css', $url);
		}
	}

	/**
	 * @return \Generator<\DOMElement>
	 */
	private static function js(\DOMDocument $document, string $domain, string $build): \Generator {
		$files = [
			sprintf('%s/assets/%s/js/custom.js', $domain, $build),
		];
		foreach ($files as $url) {
			yield $document->createElement('js', $url);
		}
	}

	private static function domain(string $env): string {
		switch ($env) {
			case 'localhost':
				return 'http://static.faktgenerator.localhost';
			default:
				throw new \RuntimeException('Invalid env');
		}
	}
}
