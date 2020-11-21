<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages;

use FaktGenerator\Web;

interface Page {
	/**
	 * @param mixed[] $parameters
	 * @param mixed[] $queryParameters
	 */
	public function response(array $parameters = [], array $queryParameters = []): Web\Response;
}
