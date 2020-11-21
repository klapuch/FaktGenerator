<?php
declare(strict_types = 1);

namespace FaktGenerator\Configuration;

use Klapuch\Configuration;

final class ApplicationConfiguration implements Configuration\Source {
	private const CONFIGURATION = __DIR__ . '/config.ini';
	private const CONFIGURATION_ENV = __DIR__ . '/config.env.ini';

	/**
	 * @return mixed[]
	 */
	public function read(): array {
		$origin = new Configuration\CombinedSource(
			new Configuration\ValidIni(new \SplFileInfo(self::CONFIGURATION)),
			new Configuration\ValidIni(new \SplFileInfo(self::CONFIGURATION_ENV)),
		);
		if (getenv('FAKTGENERATOR_ENV') !== 'local') {
			$origin = new Configuration\CachedSource($origin, new \SplFileInfo(__DIR__ . '/../../temp'));
		}
		return $origin->read();
	}
}
