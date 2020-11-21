<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

use Klapuch\Storage;

final class ExistingFact implements Fact {
	private Fact $origin;
	private Storage\Connection $connection;

	public function __construct(Fact $origin, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->connection = $connection;
	}

	public function id(): int {
		return $this->origin->id();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function properties(): array {
		$exists = (new Storage\TypedQuery(
			$this->connection,
			'SELECT EXISTS(SELECT 1 FROM facts WHERE id = ?)',
			[$this->id()],
		))->field();
		if ($exists === false) {
			throw new \UnexpectedValueException('Not found.');
		}
		return $this->origin->properties();
	}

	public function delete(): void {
		$this->origin->delete();
	}
}
