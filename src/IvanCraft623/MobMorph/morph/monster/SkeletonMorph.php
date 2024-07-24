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

use IvanCraft623\MobMorph\morph\MorphTypeIds;
use IvanCraft623\MobMorph\sound\GenericSound;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Bow;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;

class SkeletonMorph extends MonsterMorph{

	public static function getName() : string{
		return "Skeleton";
	}

	public static function getTypeId() : int{
		return MorphTypeIds::SKELETON;
	}

	public static function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.9, 0.6, 1.71);
	}

	public static function getIconPath() : string{
		return "textures/icons/morph_menu/skeleton/skeleton";
	}

	public static function getBow() : Bow{
		$bow = VanillaItems::BOW();
		$bow->setKeepOnDeath(true);
		return $bow->setNamedTag($bow->getNamedTag()
			->setByte("minecraft:item_lock", 2) // 2 = only inventory
			->setByte("mobmorph:skeleton_bow", 1)
		)->setUnbreakable()->setLore(["Mob morph"]);
	}

	public static function getArrow() : Item{
		$arrow = VanillaItems::ARROW();
		$arrow->setKeepOnDeath(true);
		return $arrow->setNamedTag($arrow->getNamedTag()
			->setByte("minecraft:item_lock", 2) // 2 = only inventory
			->setByte("mobmorph:infinite_arrow", 1)
		)->setLore(["Mob morph"]);
	}

	public function isSunSensitive() : bool{
		return true;
	}

	public function getHurtSound(EntityDamageEvent $source) : GenericSound{
		return new GenericSound(soundName: "mob.skeleton.hurt", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getDeathSound() : GenericSound{
		return new GenericSound(soundName: "mob.skeleton.death", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getAmbientSound() : ?GenericSound{
		return new GenericSound(soundName: "mob.skeleton.say", volume: 1, pitch: $this->getSoundPitch());
	}

	public function setup() : void{
		parent::setup();

		$this->getPlayer()->getInventory()->addItem(self::getBow(), self::getArrow());
	}

	public function unset() : void{
		$player = $this->getPlayer();
		$player->getInventory()->removeItem(self::getBow());

		$arrow = self::getArrow();
		$inventory = (match(true){
			$player->getOffHandInventory()->contains($arrow) => $player->getOffHandInventory(),
			$player->getInventory()->contains($arrow) => $player->getInventory(),
			$player->getCursorInventory()->contains($arrow) => $player->getCursorInventory(),
			default => null
		})?->removeItem($arrow);
	}
}
