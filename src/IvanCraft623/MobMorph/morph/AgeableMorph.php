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

use pocketmine\entity\Ageable;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use function min;

abstract class AgeableMorph extends Morph implements Ageable{

	public const STARTING_BABY_AGE = -24000;
	public const ADULT_AGE = 0;

	public static function getAgeUpWhenFeeding(int $currentAge) : int {
		if ($currentAge < self::ADULT_AGE) {
			return (int) min(-(self::STARTING_BABY_AGE / 10), -$currentAge);
		}
		return 0;
	}

	protected int $age = self::ADULT_AGE;

	public function getSoundPitch() : float{
		return parent::getSoundPitch() + ($this->isBaby() ? $this->getBabyScale() : 0);
	}

	public function unset() : void{
		//this will force an unset of baby properties
		$this->setBaby(false);
	}

	//TODO!
	/*public abstract function getBreedOffspring(AgeableMob $partner) : AgeableMob;*/

	public function canBreed() : bool {
		return false;
	}

	public function getAge() : int {
		return $this->age;
	}

	public function setAge(int $age) : void{
		$currentAge = $this->getAge();
		$this->age = $age;

		$nowIsBaby = $currentAge >= self::ADULT_AGE && $age < self::ADULT_AGE;
		if ($nowIsBaby || ($currentAge < self::ADULT_AGE && $age >= self::ADULT_AGE)) {
			$this->reachedAgeBoundary();

			$player = $this->getPlayer();
			$babyScale = $this->getBabyScale();
			$player->setScale($player->getScale() * ($nowIsBaby ? $babyScale : (1 / $babyScale)));

			$player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::BABY, $nowIsBaby);
		}
	}

	public function ageUp(int $ageAmount) : void{
		$currentAge = $this->getAge();
		$currentAge += $ageAmount;

		if ($currentAge > self::ADULT_AGE) {
			$currentAge = self::ADULT_AGE;
		}
		$this->setAge($currentAge);
	}

	public function onTick() : void{
		parent::onTick();

		if ($this->getPlayer()->isAlive()) {
			$currentAge = $this->getAge();
			if ($currentAge < self::ADULT_AGE) {
				$this->setAge(++$currentAge);
			} elseif ($currentAge > self::ADULT_AGE) {
				$this->setAge(--$currentAge);
			}
		}
	}

	public function reachedAgeBoundary() : void{
		//TODO: check if it is mounting something and leave the vehicle if it can no longer ride it.
	}

	public function isBaby() : bool{
		return $this->getAge() < self::ADULT_AGE;
	}

	/** @return $this */
	public function setBaby(bool $value = true) : self{
		$this->setAge($value ? self::STARTING_BABY_AGE : self::ADULT_AGE);

		return $this;
	}
}
