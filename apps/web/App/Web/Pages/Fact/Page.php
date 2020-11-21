<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages\Fact;

use FaktGenerator\Domain\CookieHistory;
use FaktGenerator\Domain\CountedFact;
use FaktGenerator\Domain\DefaultFact;
use FaktGenerator\Domain\ExistingFact;
use FaktGenerator\Domain\MemoizedHistory;
use FaktGenerator\Domain\RandomFact;
use FaktGenerator\Web;
use Klapuch\Storage;

final class Page implements Web\Pages\Page {
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
		$parameters['id'] = (int) $parameters['id'];
		$history = new MemoizedHistory(new CookieHistory($parameters['id'], $_COOKIE));
		$fact = new ExistingFact(
			new CountedFact(
				new DefaultFact($parameters['id'], $this->connection),
				$history->id(),
				$this->connection,
			),
			$this->connection,
		);
		try {
			$properties = $fact->properties();
		} catch (\UnexpectedValueException $e) {
			return (new Web\Pages\Error\NotFound\Page($this->document))->response();
		}
		$history->append();
		$nextId = (new RandomFact($fact, $history->id(), $this->connection))->id();
		return new Web\Response($this->template($properties, $nextId), __DIR__ . '/template.xsl');
	}

	/**
	 * @param mixed[] $fact
	 */
	private function template(array $fact, int $nextId): \DOMDocument {
		$pageElement = $this->document->getElementById('page');
		assert($pageElement instanceof \DOMElement);
		$pageElement->setAttribute('title', sprintf('Fakt #%d', $fact['id']));
		$pageElement->setAttribute('description', htmlspecialchars($fact['text']));

		$factElement = $this->document->createElement('fact');
		$factElement->appendChild($this->document->createElement('nextId', (string) $nextId));
		$factElement->appendChild($this->document->createElement('text', htmlspecialchars($fact['text'])));

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
		$pageElement->appendChild($factElement);

		return $this->document;
	}
}
