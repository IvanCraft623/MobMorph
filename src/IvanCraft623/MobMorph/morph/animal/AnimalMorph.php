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

use IvanCraft623\MobMorph\animation\BabyAnimalFeedAnimation;
use IvanCraft623\MobMorph\animation\BreedingAnimation;
use IvanCraft623\MobMorph\morph\AgeableMorph;
use IvanCraft623\MobMorph\Utils;

use pocketmine\entity\animation\ConsumingItemAnimation;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;

use function mt_rand;

abstract class AnimalMorph extends AgeableMorph {

	protected const PARENT_AGE_AFTER_BREEDING = 6000;

	private int $inLoveTicks = 0;

	public function getAmbientSoundInterval() : int{
		return 12;
	}

	public function onTick() : void{
		parent::onTick();

		if ($this->getAge() !== AgeableMorph::ADULT_AGE) {
			$this->inLoveTicks = 0;
		}

		if ($this->inLoveTicks > 0) {
			$this->inLoveTicks--;

			if ($this->inLoveTicks % 16 === 0) {
				$player = $this->getPlayer();
				$player->broadcastAnimation(new BreedingAnimation($player));
			}
		}
	}

	public function isFood(Item $item) : bool {
		return $item->getTypeId() === ItemTypeIds::WHEAT;
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		$item = $player->getInventory()->getItemInHand();
		if ($this->isFood($item)) {
			$age = $this->getAge();
			if ($age === AgeableMorph::ADULT_AGE && $this->canFallInLove()) {
				Utils::popItemInHand($player);
				$this->setInLove();

				$this->getPlayer()->broadcastAnimation(new ConsumingItemAnimation($this->getPlayer(), $item));

				return true;
			}

			if ($this->isBaby()) {
				Utils::popItemInHand($player);
				$this->ageUp(static::getAgeUpWhenFeeding($age));

				$this->getPlayer()->broadcastAnimation(new BabyAnimalFeedAnimation($this->getPlayer()));

				return true;
			}
		}

		return parent::onInteract($player, $clickPos);
	}

	public function canFallInLove() : bool {
		return $this->inLoveTicks <= 0;
	}

	public function setInLove() : void {
		$this->setInLoveTicks(600);
	}

	public function isInLove() : bool{
		return $this->inLoveTicks > 0;
	}

	public function setInLoveTicks(int $ticks) : void{
		$inLove = $this->isInLove();
		if ($inLove && $ticks <= 0 || !$inLove && $ticks > 0) {
			$this->getPlayer()->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::INLOVE, $ticks > 0);
		}

		$this->inLoveTicks = $ticks;
	}

	public function getInLoveTicks() : int {
		return $this->inLoveTicks;
	}

	/*public function canMate(Animal $other) : bool{
		if ($other === $this) {
			return false;
		}
		if ($other::class !== $this::class) {
			return false;
		}

		return $this->isInLove() && $other->isInLove();
	}

	public function spawnChildFromBreeding(Animal $partner) : void{
		$offspring = $this->getBreedOffspring($partner);
		if ($offspring !== null) {
			$offspring->setBaby();
			$offspring->setPersistent();
			$offspring->spawnToAll();

			$this->finalizeSpawnChildFromBreeding($partner, $offspring);
		}
	}

	public function finalizeSpawnChildFromBreeding(Animal $partner, AgeableMorph $offspring) : void{
		foreach ([$this, $partner] as $parent) {
			$parent->setAge(self::PARENT_AGE_AFTER_BREEDING);
			$parent->setInLoveTicks(0);
		}

		$this->getWorld()->dropExperience($this->location, mt_rand(1, 8));
	}*/
}
