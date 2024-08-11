<?php

/*
 *   __  __       _     __  __
 *  |  \/  |     | |   |  \/  |                | |
 *  | \  / | ___ | |__ | \  / | ___  _ __ _ __ | |__
 *  | |\/| |/ _ \| '_ \| |\/| |/ _ \| '__| '_ \| '_ \
 *  | |  | | (_) | |_) | |  | | (_) | |  | |_) | | | |
 *  |_|  |_|\___/|_.__/|_|  |_|\___/|_|  | .__/|_| |_|
 *                                       | |
 *                                       |_|
 *
 * A PocketMine-MP virion that allows players to morph into mobs.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author IvanCraft623
 */

declare(strict_types=1);

namespace IvanCraft623\MobMorph\morph;

use IvanCraft623\MobMorph\morph\animal\ChickenMorph;
use IvanCraft623\MobMorph\morph\animal\CowMorph;
use IvanCraft623\MobMorph\morph\monster\SkeletonMorph;
use IvanCraft623\MobMorph\morph\monster\ZombieMorph;

use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;

/**
 * Class to be able to group morphs.
 */
final class MorphManager {
	use SingletonTrait;

	/** @phpstan-var class-string<Morph> */
	private string $defaultMorphClass = PlayerMorph::class;

	/**
	 * @var string[]
	 * @phpstan-var array<string, class-string<Morph>>
	 */
	private array $morphs = [];

	/** @phpstan-var array<int, non-empty-list<MorphVariant<Morph>>> */
	protected array $variants = [];

	public function __construct() {
		$this->register(PlayerMorph::class);
		$this->register(ZombieMorph::class);
		$this->register(SkeletonMorph::class);
		$this->register(CowMorph::class);
		$this->register(ChickenMorph::class);
	}

	/**
	 * Registers a morph type into the index.
	 *
	 * @param string $className Class that extends Morph
	 * @phpstan-param class-string<Morph> $className
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(string $className) : void{
		Utils::testValidInstance($className, Morph::class);

		if (isset($this->morph[$className])) {
			throw new \InvalidArgumentException("Morph $className is already registered");
		}

		$this->morphs[$className] = $className;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, class-string<Morph>>
	 */
	public function getAll() : array{
		return $this->morphs;
	}

	/**
	 * @phpstan-return class-string<Morph>
	 */
	public function getDefaultMorph() : string{
		return $this->defaultMorphClass;
	}

	/**
	 * @phpstan-param class-string<Morph> $morphClass
	 *
	 * @phpstan-return non-empty-list<MorphVariant<Morph>>
	 */
	public function getVariants(string $morphClass) : array{
		return $this->variants[$morphClass::getTypeId()] ??= $morphClass::generateVariants();
	}
}
