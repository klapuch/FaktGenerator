<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

use Klapuch\Storage;

final class RandomFact implements Fact {
	private const LIMIT = 300;
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
		$id = (new Storage\TypedQuery(
			$this->connection,
			sprintf(
				'SELECT fact_id(?, ARRAY[%s]::integer[])',
				rtrim(str_repeat('?,', min(count($this->visited), self::LIMIT)), ','),
			),
			array_merge([$this->origin->id()], array_slice($this->visited, 0, self::LIMIT)),
		))->field();
		assert(is_int($id));
		return $id;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function properties(): array {
		return $this->origin->properties();
	}

	public function delete(): void {
		$this->origin->delete();
	}
}
