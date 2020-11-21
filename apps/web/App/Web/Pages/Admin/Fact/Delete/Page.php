<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages\Admin\Fact\Delete;

use FaktGenerator\Domain\DefaultFact;
use FaktGenerator\Web;
use Klapuch\Storage;

final class Page implements Web\Pages\Page {
	private Storage\Connection $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param mixed[] $parameters
	 * @param mixed[] $queryParameters
	 */
	public function response(array $parameters = [], array $queryParameters = []): Web\Response {
		(new DefaultFact((int) $_POST['id'], $this->connection))->delete();
		header(sprintf('Location: %s', '/admin/facts'));
		exit;
	}
}
