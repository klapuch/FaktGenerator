<?php
declare(strict_types = 1);

namespace FaktGenerator\Web\Pages;

final class Layout {
	public function document(): \DOMDocument {
		$document = new \DOMDocument('1.0', 'UTF-8');
		$webElement = $document->createElement('web');
		$layoutElement = $document->createElement('layout');
		$pageElement = $document->createElement('page');
		$pageElement->setAttribute('xml:id', 'page');
		$pageElement->setAttribute('route', $_SERVER['ROUTE_NAME']);
		$layoutElement->appendChild($document->importNode(self::assets(), true));
		$webElement->appendChild($layoutElement);
		$webElement->appendChild($pageElement);
		$webElement->appendChild($document->importNode(self::domains(), true));
		$document->appendChild($webElement);
		return $document;
	}

	private static function assets(): \DOMElement {
		$assets = new \DOMDocument('1.0', 'UTF-8');
		if ($assets->load(__DIR__ . '/@assets.xml') === false) {
			throw new \RuntimeException('Can not load @assets.xml');
		}
		assert($assets->documentElement instanceof \DOMElement);
		return $assets->documentElement;
	}

	private static function domains(): \DOMElement {
		$assets = new \DOMDocument('1.0', 'UTF-8');
		if ($assets->load(__DIR__ . '/@domains.xml') === false) {
			throw new \RuntimeException('Can not load @domains.xml');
		}
		assert($assets->documentElement instanceof \DOMElement);
		return $assets->documentElement;
	}
}
