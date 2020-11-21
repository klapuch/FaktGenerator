<?php
declare(strict_types = 1);

namespace FaktGenerator\Web;

final class Response {
	private \DOMDocument $document;
	private string $template;
	private int $status;
	/** @var string[] */
	private array $headers;

	/**
	 * @param string[] $headers
	 */
	public function __construct(\DOMDocument $document, string $template, int $status = 200, array $headers = []) {
		$this->document = $document;
		$this->template = $template;
		$this->status = $status;
		$this->headers = $headers;
	}

	public function render(): void {
		$xsl = new \DOMDocument('1.0', 'UTF-8');
		$xsl->load($this->template);
		$xslt = new \XSLTProcessor();
		$xslt->registerPHPFunctions();
		$xslt->importStylesheet($xsl);
		if ($this->status !== 200) {
			http_response_code($this->status);
		}
		foreach ($this->headers as $header) {
			header($header);
		}
		echo $xslt->transformToXml($this->document);
	}
}
