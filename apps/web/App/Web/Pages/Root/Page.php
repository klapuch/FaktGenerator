<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages\Root;

use FaktGenerator\Domain\CookieHistory;
use FaktGenerator\Domain\DefaultFact;
use FaktGenerator\Domain\RandomFact;
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
		$actual = 0;
		header(
			sprintf(
				'Location: /fakt/%d',
				(new RandomFact(
					new DefaultFact($actual, $this->connection),
					(new CookieHistory($actual, $_COOKIE))->id(),
					$this->connection,
				))->id(),
			),
		);
		exit;
	}
}
