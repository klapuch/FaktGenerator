<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages\Admin\Facts;

use FaktGenerator\Domain\AdminFacts;
use FaktGenerator\Web;
use Klapuch\Storage;

final class Page implements Web\Pages\Page {
	private const PER_PAGE = 24;
	private \DOMDocument $document;
	private Storage\Connection $connection;

	public function __construct(\DOMDocument $document, Storage\Connection $connection) {
		$this->document = $document;
		$this->connection = $connection;
	}

	/**
	 * @param mixed[] $parameters
	 * @param mixed[] $queryParameters
	 */
	public function response(array $parameters = [], array $queryParameters = []): Web\Response {
		$page = isset($queryParameters['page']) ? (int) $queryParameters['page'] : 1;
		$facts = (new AdminFacts($this->connection))->all($page, self::PER_PAGE);
		return new Web\Response($this->template($facts, $page), __DIR__ . '/template.xsl');
	}

	/**
	 * @param mixed[] $facts
	 */
	private function template(array $facts, int $page): \DOMDocument {
		$pageElement = $this->document->getElementById('page');
		assert($pageElement instanceof \DOMElement);
		$pageElement->setAttribute('title', 'Facts');

		$paginatorElement = $this->document->createElement('paginator');
		$paginatorElement->setAttribute('page', (string) $page);
		$paginatorElement->setAttribute('perPage', (string) self::PER_PAGE);

		$factsElement = $this->document->createElement('facts');
		foreach ($facts as $fact) {
			$factElement = $this->document->createElement('fact');
			$factElement->appendChild($this->document->createElement('id', (string) $fact['id']));
			$factElement->appendChild($this->document->createElement('text', htmlspecialchars($fact['text'])));
			$factElement->appendChild(
				$this->document->createElement('created_at', (string) strtotime($fact['created_at'])),
			);
			$factElement->appendChild($this->document->createElement('visited_count', (string) $fact['visited_count']));

			$tagsElement = $this->document->createElement('tags');
			foreach ($fact['tags'] as $tag) {
				$tagsElement->appendChild($this->document->createElement('tag', $tag));
			}

			$sourcesElement = $this->document->createElement('sources');
			foreach ($fact['sources'] as $source) {
				if ($source['name'] !== null) {
					$sourceElement = $this->document->createElement('source');
					$sourceElement->appendChild($this->document->createElement('url', $source['url']));
					$sourceElement->appendChild($this->document->createElement('icon', $source['icon']));
					$sourceElement->appendChild($this->document->createElement('name', $source['name']));
					$sourcesElement->appendChild($sourceElement);
				}
			}

			$factElement->appendChild($sourcesElement);
			$factElement->appendChild($tagsElement);
			$factsElement->appendChild($factElement);
		}

		$pageElement->appendChild($factsElement);
		$pageElement->appendChild($paginatorElement);

		return $this->document;
	}
}
