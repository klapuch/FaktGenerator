<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

use Klapuch\Storage;

final class AdminFacts implements Facts {
	private Storage\Connection $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function all(int $page, int $perPage): array {
		return (new Storage\TypedQuery(
			$this->connection,
			<<<'SQL'
			SELECT
				f.id,
				f.text,
				f.created_at,
				f.visited_count,
				array_to_json(array_remove((array_agg(DISTINCT ft.name))[1:2], NULL)) AS tags,
				jsonb_agg(DISTINCT jsonb_build_object('name', s.name, 'icon', s.fa_icon, 'url', fs.url)) AS sources
			FROM facts AS f
			LEFT JOIN fact_tags AS ft ON ft.fact_id = f.id
			LEFT JOIN fact_sources AS fs ON fs.fact_id = f.id
			LEFT JOIN sources AS s ON s.id = fs.source_id
			GROUP BY f.id
			ORDER BY date_trunc('second', created_at) DESC, f.visited_count DESC, length(f.text), f.id DESC
			LIMIT :per_page::integer + 1
			OFFSET :page::integer * :per_page::integer + 1
			SQL,
			['page' => $page, 'per_page' => $perPage],
		))->rows();
	}
}
