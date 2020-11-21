<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

interface Fact {
	public function id(): int;

	/**
	 * @return array<string, mixed>
	 */
	public function properties(): array;

	public function delete(): void;
}
