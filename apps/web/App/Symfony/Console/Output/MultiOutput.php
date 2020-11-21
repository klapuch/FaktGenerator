<?php
declare(strict_types = 1);

namespace FaktGenerator\Symfony\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MultiOutput implements OutputInterface {
	/** @var OutputInterface[] */
	private array $outputs;

	public function __construct(OutputInterface ...$outputs) {
		$this->outputs = $outputs;
	}

	/**
	 * @param iterable<mixed>|string $messages
	 */
	public function write($messages, bool $newline = false, int $options = 0): void {
		foreach ($this->outputs as $output) {
			$output->write($messages, $newline, $options);
		}
	}

	/**
	 * @param iterable<mixed>|string $messages
	 */
	public function writeln($messages, int $options = 0): void {
		foreach ($this->outputs as $output) {
			$output->writeln($messages, $options);
		}
	}

	public function setVerbosity(int $level): void {
		foreach ($this->outputs as $output) {
			$output->setVerbosity($level);
		}
	}

	public function getVerbosity(): int {
		return $this->outputs[0]->getVerbosity();
	}

	public function isQuiet(): bool {
		return $this->outputs[0]->isQuiet();
	}

	public function isVerbose(): bool {
		return $this->outputs[0]->isVerbose();
	}

	public function isVeryVerbose(): bool {
		return $this->outputs[0]->isVeryVerbose();
	}

	public function isDebug(): bool {
		return $this->outputs[0]->isDebug();
	}

	public function setDecorated(bool $decorated): void {
		foreach ($this->outputs as $output) {
			$output->setDecorated($decorated);
		}
	}

	public function isDecorated(): bool {
		return $this->outputs[0]->isDecorated();
	}

	public function setFormatter(OutputFormatterInterface $formatter): void {
		foreach ($this->outputs as $output) {
			$output->setFormatter($formatter);
		}
	}

	public function getFormatter(): OutputFormatterInterface {
		return $this->outputs[0]->getFormatter();
	}
}
