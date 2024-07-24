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

namespace IvanCraft623\MobMorph\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\world\sound\Sound;

class GenericSound implements Sound{

	public function __construct(
		private string $soundName,
		private float $volume = 1,
		private float $pitch = 1
	){}

	public function getSoundName() : string{
		return $this->soundName;
	}

	public function getVolume() : float{
		return $this->volume;
	}

	public function getPitch() : float{
		return $this->pitch;
	}

	public function encode(Vector3 $pos) : array{
		return [PlaySoundPacket::create($this->soundName, $pos->x, $pos->y, $pos->z, $this->volume, $this->pitch)];
	}
}
