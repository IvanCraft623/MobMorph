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
use IvanCraft623\MobMorph\morph\MorphVariant;
use IvanCraft623\MobMorph\sound\GenericSound;

use pocketmine\entity\Ageable;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;

class ZombieMorph extends MonsterMorph implements Ageable{

	private bool $isBaby = false;

	public static function getName() : string{
		return "Zombie";
	}

	public static function getTypeId() : int{
		return MorphTypeIds::ZOMBIE;
	}

	public static function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.8, 0.6, 1.62);
	}

	public static function getIconPath() : string{
		return "textures/icons/morph_menu/zombie/zombie";
	}

	/**
	 * @phpstan-return non-empty-list<MorphVariant<ZombieMorph>>
	 */
	public static function generateVariants() : array{
		return [
			new MorphVariant(self::class, fn(ZombieMorph $morph) : self => $morph, self::getIconPath()),
			new MorphVariant(self::class, fn(ZombieMorph $morph) : self => $morph->setBaby(true), "textures/icons/morph_menu/zombie/baby_zombie")
		];
	}

	public function isSunSensitive() : bool{
		return true;
	}

	public function getHurtSound(EntityDamageEvent $source) : GenericSound{
		return new GenericSound(soundName: "mob.zombie.hurt", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getDeathSound() : GenericSound{
		return new GenericSound(soundName: "mob.zombie.death", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getAmbientSound() : ?GenericSound{
		return new GenericSound(soundName: "mob.zombie.say", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getSoundPitch() : float{
		return parent::getSoundPitch() + ($this->isBaby ? $this->getBabyScale() : 0);
	}

	public function unset() : void{
		//this will force an unset of baby properties
		$this->setBaby(false);
	}

	public function isBaby() : bool{
		return $this->isBaby;
	}

	/** @return $this */
	public function setBaby(bool $value = true) : self{
		if ($this->isBaby === $value) {
			return $this;
		}

		$this->isBaby = $value;

		$player = $this->getPlayer();
		$babyScale = $this->getBabyScale();
		$player->setScale($player->getScale() * ($value ? $babyScale : (1 / $babyScale)));

		$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::BABY, $value);

		return $this;
	}
}
