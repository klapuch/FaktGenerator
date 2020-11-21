<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Jobs;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface Job {
	public function scheduled(): bool;

	public function getName(); // intentionally without type

	public function execute(InputInterface $input, OutputInterface $output): int;
}
