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

namespace IvanCraft623\MobMorph\morph\monster;

use IvanCraft623\MobMorph\morph\Morph;

use pocketmine\item\Durable;
use pocketmine\item\VanillaItems;

use function floor;
use function mt_rand;

abstract class MonsterMorph extends Morph{

	public function isSunSensitive() : bool{
		return false;
	}

	public function onTick() : void{
		parent::onTick();

		$player = $this->getPlayer();
		if ($player->isSurvival() && !$player->isOnFire() && $this->isSunSensitive()) {
			$world = $player->getWorld();
			$pos = $player->getEyePos();
			if ($world->getSkyLightReduction() <= 3 &&
				$world->getPotentialBlockSkyLightAt((int) floor($pos->x), (int) floor($pos->y), (int) floor($pos->z)) === 15 &&
				!$this->isTouchingWater() //TODO: Powder snow also prevents this
			) {
				$helmet = $player->getArmorInventory()->getHelmet();
				if ($helmet->isNull()) {
					$player->setOnFire(8);
				}/* elseif ($helmet instanceof Durable) {
					$helmet->applyDamage(mt_rand(0, 2));
					if ($helmet->isBroken()) {
						$helmet = VanillaItems::AIR();
					}
					$player->getArmorInventory()->setHelmet($helmet);
				}*/
			}
		}
	}
}
