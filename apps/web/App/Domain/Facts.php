<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

interface Facts {
	/**
	 * @return array<string, mixed>
	 */
	public function all(int $page, int $perPage): array;
}
