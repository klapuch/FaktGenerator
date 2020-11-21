<?php
declare(strict_types = 1);

namespace FaktGenerator\Domain;

final class CookieHistory implements History {
	private const NAME = 'history';
	private const HOUR = 60 * 60;

	private int $id;
	/** @var array<string, string> */
	private array $cookie;

	/**
	 * @param array<string, string> $cookie
	 */
	public function __construct(int $id, array $cookie) {
		$this->id = $id;
		$this->cookie = $cookie;
	}

	/**
	 * @return int[]
	 */
	public function id(): array {
		return array_filter(
			array_unique(
				array_map(
					'intval',
					explode(',', $this->cookie[self::NAME] ?? ''),
				),
			),
			static fn (int $id): bool => $id > 0,
		);
	}

	public function append(): void {
		setcookie(
			self::NAME,
			implode(',', array_unique(array_merge($this->id(), [$this->id]))),
			[
				'expires' => time() + (self::HOUR * 2),
				'httponly' => true,
				'path' => '/',
				'samesite' => 'Strict',
			],
		);
	}
}
