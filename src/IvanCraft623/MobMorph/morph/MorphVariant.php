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

use pocketmine\player\Player;

/**
 * @template-covariant TMorph of Morph
 */
final class MorphVariant{

	/**
	 * @phpstan-param class-string<TMorph> $morphClass
	 * @phpstan-param \Closure(TMorph) : TMorph $creationFunc
	 */
	public function __construct(
		private string $morphClass,
		private \Closure $creationFunc,
		private string $iconPath
	) {}

	/**
	 * @phpstan-return class-string<TMorph>
	 */
	public function getMorphClass() : string{
		return $this->morphClass;
	}

	/**
	 * @phpstan-return TMorph
	 */
	public function create(Player $player) : Morph{
		return ($this->creationFunc)(new ($this->morphClass)($player));
	}

	public function getIconPath() : string{
		return $this->iconPath;
	}
}
