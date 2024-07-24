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

use IvanCraft623\MobMorph\MobMorph;
use IvanCraft623\MobMorph\sound\GenericSound;
use IvanCraft623\MobMorph\Utils;

use pocketmine\block\Block;
use pocketmine\block\Water;
use pocketmine\entity\Attribute;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;

use function floor;
use function mt_rand;

abstract class Morph {

	abstract public static function getName() : string;
	abstract public static function getTypeId() : int;
	abstract public static function getInitialSizeInfo() : EntitySizeInfo;
	abstract public static function getIconPath() : string;

	/**
	 * @phpstan-return non-empty-list<MorphVariant<Morph>>
	 */
	public static function generateVariants() : array{
		return [new MorphVariant(static::class, fn(Morph $morph) : self => $morph, static::getIconPath())];
	}

	private string $playerRawUUID;

	private int $nextAmbientSound = 0; // in ticks

	final public function __construct(Player $player) {
		$this->playerRawUUID = $player->getUniqueId()->getBytes();
	}

	public function getPlayer() : Player{
		return Server::getInstance()->getPlayerByRawUUID($this->playerRawUUID) ?? throw new AssumptionFailedError("Player no longer conected?!");
	}

	abstract public function getHurtSound(EntityDamageEvent $source) : GenericSound;
	abstract public function getDeathSound() : GenericSound;
	abstract public function getAmbientSound() : ?GenericSound;

	public function getSoundPitch() : float{
		return Utils::random_float(0.8, 1.2);
	}

	public function getAmbientSoundInterval() : int{
		return 8;
	}

	public function getAmbientSoundIntervalRange() : int{
		return 16;
	}

	public function setup() : void{
		$morphMaxHealth = $this->getMaxHealth();

		$player = $this->getPlayer();
		$playerHealth = $player->getHealth();
		$playerMaxHealth = $player->getMaxHealth();

		$player->setMaxHealth($morphMaxHealth);
		$player->setHealth(($playerHealth / $playerMaxHealth) * $morphMaxHealth);

		//TODO: Hack! For some reason this attribute gets out of sync with the client,
		// so we sending it again after a few ticks.
		MobMorph::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($player) : void {
			/** @var Attribute $healthAttr */
			$healthAttr = $player->getAttributeMap()->get(Attribute::HEALTH);
			$healthAttr->markSynchronized(false);
		}), 2);
	}
	public function unset() : void{}

	public function onTick() : void{
		if (--$this->nextAmbientSound <= 0 && ($sound = $this->getAmbientSound()) !== null) {
			$this->nextAmbientSound = mt_rand($this->getAmbientSoundInterval(), $this->getAmbientSoundIntervalRange()) * 20;
			$this->getPlayer()->broadcastSound($sound);
		}
	}
	public function attack(EntityDamageEvent $source) : void{}

	/**
	 * Called when interacted or tapped by other Player. Returns whether something happened as a result of the interaction.
	 */
	public function onInteract(Player $interactor, Vector3 $clickPos) : bool{
		return false;
	}

	/**
	 * Called when the player uses its held item.
	 */
	public function onItemUse(Item $item, Vector3 $directionVector) : bool{
		return false;
	}

	protected function getBabyScale() : float{
		return 0.5;
	}

	public function getMaxHealth() : int{
		return 20;
	}

	/**
	 * Yields all the blocks whose full-cube areas are intersected by the player's AABB.
	 *
	 * @phpstan-return \Generator<int, Block, void, void>
	 */
	protected function getBlocksIntersected(float $inset) : \Generator{
		$player = $this->getPlayer();

		$bb = $player->getBoundingBox();

		$minX = (int) floor($bb->minX + $inset);
		$minY = (int) floor($bb->minY + $inset);
		$minZ = (int) floor($bb->minZ + $inset);
		$maxX = (int) floor($bb->maxX - $inset);
		$maxY = (int) floor($bb->maxY - $inset);
		$maxZ = (int) floor($bb->maxZ - $inset);

		$world = $player->getWorld();
		for($z = $minZ; $z <= $maxZ; ++$z){
			for($x = $minX; $x <= $maxX; ++$x){
				for($y = $minY; $y <= $maxY; ++$y){
					yield $world->getBlockAt($x, $y, $z);
				}
			}
		}
	}

	public function isTouchingWater() : bool{
		foreach ($this->getBlocksIntersected(0.001) as $block) {
			if ($block instanceof Water) {
				return true;
			}
		}

		return false;
	}
}
