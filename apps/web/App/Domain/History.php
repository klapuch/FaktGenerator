<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

interface History {
	/**
	 * @return int[]
	 */
	public function id(): array;

	public function append(): void;
}
