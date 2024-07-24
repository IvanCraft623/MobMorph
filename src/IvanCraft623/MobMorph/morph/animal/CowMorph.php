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

namespace IvanCraft623\MobMorph\morph\animal;

use IvanCraft623\MobMorph\morph\MorphTypeIds;
use IvanCraft623\MobMorph\morph\MorphVariant;
use IvanCraft623\MobMorph\sound\CowMilkSound;
use IvanCraft623\MobMorph\sound\GenericSound;
use IvanCraft623\MobMorph\Utils;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Bucket;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

final class CowMorph extends AnimalMorph{

	public static function getName() : string{
		return "Cow";
	}

	public static function getTypeId() : int{
		return MorphTypeIds::COW;
	}

	public static function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.3, 0.9, 1.3);
	}

	public static function getIconPath() : string{
		return "textures/icons/morph_menu/cow/cow";
	}

	/**
	 * @phpstan-return non-empty-list<MorphVariant<CowMorph>>
	 */
	public static function generateVariants() : array{
		return [
			new MorphVariant(self::class, fn(CowMorph $morph) : self => $morph, self::getIconPath()),
			new MorphVariant(self::class, fn(CowMorph $morph) : self => $morph->setBaby(true), "textures/icons/morph_menu/cow/baby_cow")
		];
	}

	public function getHurtSound(EntityDamageEvent $source) : GenericSound{
		return new GenericSound(soundName: "mob.cow.hurt", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getDeathSound() : GenericSound{
		return new GenericSound(soundName: "mob.cow.hurt", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getAmbientSound() : ?GenericSound{
		return new GenericSound(soundName: "mob.cow.say", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getMaxHealth() : int{
		return 10;
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		$item = $player->getInventory()->getItemInHand();
		if (!$this->isBaby() && $item instanceof Bucket) {
			Utils::transformItemInHand($player, VanillaItems::MILK_BUCKET());
			$this->getPlayer()->broadcastSound(new CowMilkSound());
			return true;
		}

		return parent::onInteract($player, $clickPos);
	}
}
