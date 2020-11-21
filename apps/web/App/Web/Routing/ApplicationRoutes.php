<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Routing;

use FaktGenerator\Web\Pages;
use Klapuch\Storage;

final class ApplicationRoutes {
	private Storage\Connection $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function match(string $name): Pages\Page {
		$document = (new Pages\Layout())->document();
		switch ($name) {
			case '/fact/{id}':
				return new Pages\Fact\Page($document, $this->connection);
			case '/':
				return new Pages\Root\Page($this->connection);
			case '/admin/facts':
				return new Pages\Admin\Facts\Page($document, $this->connection);
			case '/admin/fact/delete':
				return new Pages\Admin\Fact\Delete\Page($this->connection);
			case '500':
				return new Pages\Error\Internal\Page($document);
			case '404':
			default:
				return new Pages\Error\NotFound\Page($document);
		}
	}
}
