<?php
declare(strict_types = 1);

namespace FaktGenerator\Symfony\Console\Output;

use Symfony\Component\Console\Output\ConsoleOutput;

final class TimeOutput extends ConsoleOutput {
	protected function doWrite(string $message, bool $newline): void {
		if (isset($_SERVER['argv'][1])) {
			$message = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message);
		}
		parent::doWrite($message, $newline);
	}
}
