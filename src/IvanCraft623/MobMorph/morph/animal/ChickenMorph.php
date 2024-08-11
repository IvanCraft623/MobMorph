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
use IvanCraft623\MobMorph\sound\EntityPlopSound;
use IvanCraft623\MobMorph\sound\GenericSound;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use function mt_rand;
use function number_format;

final class ChickenMorph extends AnimalMorph{

	public static function getName() : string{
		return "Chicken";
	}

	public static function getTypeId() : int{
		return MorphTypeIds::CHICKEN;
	}

	public static function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.8, 0.6, 0.7);
	}

	public static function getIconPath() : string{
		return "textures/icons/morph_menu/chicken/chicken";
	}

	/**
	 * @phpstan-return non-empty-list<MorphVariant<ChickenMorph>>
	 */
	public static function generateVariants() : array{
		return [
			new MorphVariant(self::class, fn(ChickenMorph $morph) : self => $morph, self::getIconPath()),
			new MorphVariant(self::class, fn(ChickenMorph $morph) : self => $morph->setBaby(true), "textures/icons/morph_menu/chicken/baby_chicken")
		];
	}

	public static function getRandomEggLayingDelay() : int{
		return mt_rand(6000, 12000);
	}

	protected int $eggLayingDelay; //in ticks

	protected bool $canLayEggs = true;

	public function getHurtSound(EntityDamageEvent $source) : GenericSound{
		return new GenericSound(soundName: "mob.chicken.hurt", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getDeathSound() : GenericSound{
		return new GenericSound(soundName: "mob.chicken.hurt", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getAmbientSound() : ?GenericSound{
		return new GenericSound(soundName: "mob.chicken.say", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getMaxHealth() : int{
		return 4;
	}

	public function setup() : void{
		parent::setup();

		$this->eggLayingDelay = self::getRandomEggLayingDelay();
	}

	public function onTick() : void{
		parent::onTick();

		$player = $this->getPlayer();
		//TODO!
		//$player->getEffects()->add(new EffectInstance(VanillaEffects::SLOW_FALLING(), 1));

		if ($this->canLayEggs && !$this->isBaby()) {
			$this->eggLayingDelay--;
			if ($this->eggLayingDelay <= 0) {
				//TODO: check that there no riders
				$player->broadcastSound(new EntityPlopSound());
				$player->getWorld()->dropItem($player->getLocation(), VanillaItems::EGG(), null, 30);

				$this->eggLayingDelay = self::getRandomEggLayingDelay();
			} elseif ($this->eggLayingDelay <= 200) {
				$player->sendTip("Laying egg in " . number_format($this->eggLayingDelay / 20, 1));
			}
		}
	}

	public function getEggLayingDelay() : int{
		return $this->eggLayingDelay;
	}

	public function setEggLayingDelay(int $delay) : self{
		$this->eggLayingDelay = $delay;

		return $this;
	}

	public function canLayEggs() : bool{
		return $this->canLayEggs;
	}

	/**
	 * @return $this
	 */
	public function setCanLayEggs(bool $value) : self{
		$this->canLayEggs = $value;

		return $this;
	}

	public function isFood(Item $item) : bool {
		return match ($item->getTypeId()) {
			ItemTypeIds::WHEAT_SEEDS,
			ItemTypeIds::BEETROOT_SEEDS,
			ItemTypeIds::MELON_SEEDS,
			ItemTypeIds::PITCHER_POD,
			ItemTypeIds::TORCHFLOWER_SEEDS,
			ItemTypeIds::PUMPKIN_SEEDS => true,
			default => false,
		};
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);

		if ($source->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$source->cancel();
		}
	}
}
