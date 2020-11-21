<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

final class MemoizedHistory implements History {
	/** @var int[]|null */
	private ?array $id = null;
	private History $origin;

	public function __construct(History $origin) {
		$this->origin = $origin;
	}

	/**
	 * @return int[]
	 */
	public function id(): array {
		return $this->id ??= $this->origin->id();
	}

	public function append(): void {
		$this->id = null;
		$this->origin->append();
	}
}
