<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

use Klapuch\Storage;

final class DefaultFact implements Fact {
	private int $id;
	private Storage\Connection $connection;

	public function __construct(int $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	public function id(): int {
		return $this->id;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function properties(): array {
		return (new Storage\TypedQuery(
			$this->connection,
			<<<'SQL'
			SELECT
				f.id,
				f.text,
				array_to_json(array_remove(array_agg(DISTINCT ft.name), NULL)) AS tags,
				jsonb_agg(DISTINCT jsonb_build_object('name', s.name, 'icon', s.fa_icon, 'url', fs.url)) AS sources
			FROM facts AS f
			LEFT JOIN fact_tags AS ft ON ft.fact_id = f.id
			LEFT JOIN fact_sources AS fs ON fs.fact_id = f.id
			LEFT JOIN sources AS s ON s.id = fs.source_id
			WHERE f.id = ?
			GROUP BY f.id
			SQL,
			[$this->id],
		))->row();
	}

	public function delete(): void {
		(new Storage\NativeQuery(
			$this->connection,
			'DELETE FROM facts WHERE id = ?',
			[$this->id],
		))->execute();
	}
}
