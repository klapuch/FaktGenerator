<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages\Error\Internal;

use FaktGenerator\Web;

final class Page implements Web\Pages\Page {
	private \DOMDocument $document;

	public function __construct(\DOMDocument $document) {
		$this->document = $document;
	}

	/**
	 * @param mixed[] $parameters
	 * @param mixed[] $queryParameters
	 */
	public function response(array $parameters = [], array $queryParameters = []): Web\Response {
		$pageElement = $this->document->getElementById('page');
		assert($pageElement instanceof \DOMElement);
		$pageElement->setAttribute('title', '500 - Chyba');
		return new Web\Response($this->document, __DIR__ . '/template.xsl', 500);
	}
}
