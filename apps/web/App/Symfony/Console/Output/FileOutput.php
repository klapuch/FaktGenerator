<?php
declare(strict_types = 1);

namespace FaktGenerator\Symfony\Console\Output;

use Nette\Utils\FileSystem;
use Symfony\Component\Console\Output\StreamOutput;

final class FileOutput extends StreamOutput {
	public function __construct(string $directory) {
		if (isset($_SERVER['argv'][1])) {
			$directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . str_replace(':', '_', $_SERVER['argv'][1]);
			FileSystem::createDir($directory);
			$filename = sprintf('%s/%s.log', $directory, date('Y-m-d--H-i-s'));
		} else {
			$filename = '/dev/null';
		}
		if (($resource = fopen($filename, 'a')) === false) {
			throw new \RuntimeException(sprintf('Can not open "%s"', $filename));
		}
		parent::__construct($resource);
	}

	protected function doWrite(string $message, bool $newline): void {
		parent::doWrite(sprintf('[%s] %s', date('Y-m-d H:i:s'), $message), $newline);
	}
}
