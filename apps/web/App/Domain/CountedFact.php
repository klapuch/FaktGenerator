<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

use Klapuch\Storage;

final class CountedFact implements Fact {
	private Fact $origin;
	/** @var int[] */
	private array $visited;
	private Storage\Connection $connection;

	/**
	 * @param int[] $visited
	 */
	public function __construct(Fact $origin, array $visited, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->visited = $visited;
		$this->connection = $connection;
	}

	public function id(): int {
		return $this->origin->id();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function properties(): array {
		if (!in_array($this->id(), $this->visited, true)) {
			(new Storage\TypedQuery(
				$this->connection,
				'UPDATE facts SET visited_count = visited_count + 1 WHERE id = ?',
				[$this->id()],
			))->execute();
		}
		return $this->origin->properties();
	}

	public function delete(): void {
		$this->origin->delete();
	}
}
