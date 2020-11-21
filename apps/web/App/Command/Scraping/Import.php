<?php
declare(strict_types = 1);

namespace FaktGenerator\Command\Scraping;

use Klapuch\Storage;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Import extends Console\Command\Command {
	private Storage\Connection $connection;

	public function __construct(Storage\Connection $connection) {
		parent::__construct();
		$this->connection = $connection;
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$facts = [];
		foreach (self::files() as $file) {
			$resource = @fopen($file->getPathname(), 'r');
			if ($resource === false) {
				throw new \RuntimeException(sprintf('Can not open file "%s".', $file->getRealPath()));
			}
			fgetcsv($resource); // header
			while (($row = fgetcsv($resource)) !== false) {
				[$fact] = $row;
				assert(is_string($fact));
				$fact = self::format($fact);
				if ($fact !== '') {
					$facts[md5($fact)] = $fact;
				}
			}
		}
		$facts = self::withoutSimilarities($facts);
		(new Storage\NativeQuery(
			$this->connection,
			sprintf('INSERT INTO facts (text) VALUES %s', implode(', ', array_fill(0, count($facts), '(?)'))),
			array_values($facts),
		))->execute();
		return 0;
	}

	public static function getDefaultName(): string {
		return 'scraping:import';
	}

	protected function configure(): void {
		$this->setDescription('Import scraped data to DB');
	}

	/**
	 * @param string[] $facts
	 * @return string[]
	 */
	private static function withoutSimilarities(array $facts): array {
		$acceptable = [];
		$remove = [];
		foreach ($facts as $outerKey => $outerText) {
			foreach ($facts as $innerKey => $innerText) {
				if (
					$outerKey !== $innerKey
					&& !isset($remove[$outerKey], $remove[$innerKey])
					&& mb_strlen($outerText) < 255
					&& mb_strlen($innerText) < 255
					&& levenshtein($outerText, $innerText) <= 5
				) {
					$acceptable[$outerKey] = $outerText;
					$remove[$innerKey] = $innerKey;
					$remove[$outerKey] = $outerKey;
				}
			}
		}
		return $acceptable + array_diff_key($facts, $remove);
	}

	/**
	 * @return \Generator<\SplFileInfo>
	 */
	private static function files(): \Generator {
		yield new \SplFileInfo(__DIR__ . '/../../../../scraper/results/mental-floss.csv');
		yield new \SplFileInfo(__DIR__ . '/../../../../scraper/results/pohlmanpavilion.csv');
		yield new \SplFileInfo(__DIR__ . '/../../../../scraper/results/random_fact_generator.csv');
		yield new \SplFileInfo(__DIR__ . '/../../../../scraper/results/random_word_generator.csv');
		yield new \SplFileInfo(__DIR__ . '/../../../../scraper/results/wtf_fun_fact.csv');
	}

	private static function format(string $fact): string {
		$fact = Strings::trim($fact);
		$fact = Html::htmlToText($fact);
		$fact = str_replace('”', '"', $fact);
		$fact = str_replace('“', '"', $fact);
		$fact = str_replace('’', '\'', $fact);
		$fact = str_replace('‘', '\'', $fact);
		$fact = str_replace('""', '"', $fact);
		$fact = str_replace('\\"', '"', $fact);
		$fact = (string) preg_replace('~\r?\n~', ' ', $fact);
		$fact = (string) preg_replace('~\s{2,}~', ' ', $fact);
		$fact = (string) preg_replace('~[\.\?\!]$~', '', $fact) . '.';
		return $fact;
	}
}
