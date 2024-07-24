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

namespace IvanCraft623\MobMorph;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

use function lcg_value;
use function round;

final class Utils {

	/**
	 * Compares two EntitySizeInfo objects, both should be at the same scale.
	 */
	public static function entitySizeInfoEquals(EntitySizeInfo $first, EntitySizeInfo $second) : bool{
		return round($first->getHeight(), 5) == round($second->getHeight(), 5) &&
			round($first->getWidth(), 5) == round($second->getWidth(), 5) &&
			round($first->getEyeHeight(), 5) == round($second->getEyeHeight(), 5);
	}

	public static function random_float(float $min, float $max) : float{
	   return ($max - $min) * lcg_value() + $min;
	}

	public static function popItemInHand(Player $player, int $amount = 1) : void{
		if ($player->hasFiniteResources()) {
			$item = $player->getInventory()->getItemInHand();
			$item->pop($amount);

			if ($item->isNull()) {
				$item = VanillaItems::AIR();
			}

			$player->getInventory()->setItemInHand($item);
		}
	}

	public static function transformItemInHand(Player $player, Item $result) : void{
		if ($player->hasFiniteResources()) {
			$item = $player->getInventory()->getItemInHand();
			$item->pop($result->getCount());

			if ($item->isNull()) {
				$player->getInventory()->setItemInHand($result);
				return;
			}

			$player->getInventory()->setItemInHand($item);
		}

		$player->getInventory()->addItem($result);
	}
}
