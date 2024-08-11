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

use IvanCraft623\MobMorph\command\MobMorphCommand;
use IvanCraft623\MobMorph\form\FormManager;
use IvanCraft623\MobMorph\morph\Morph;
use IvanCraft623\MobMorph\morph\MorphManager;
use IvanCraft623\MobMorph\morph\MorphTypeIds;
use IvanCraft623\MobMorph\morph\MorphVariant;
use IvanCraft623\MobMorph\morph\PlayerMorph;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;

use ReflectionMethod;

class MobMorph extends PluginBase{
	use SingletonTrait;

	/**
	 * WeakMap ensures that the morph is destroyed when the player is destroyed, without causing any memory leaks
	 *
	 * @phpstan-var \WeakMap<Player, Morph>
	 */
	private \WeakMap $playersMorph;

	/** @phpstan-var CacheableNbt<CompoundTag> */
	private CacheableNbt $playerActorProperties;

	public function onEnable() : void {
		self::setInstance($this);

		$this->getServer()->getCommandMap()->register('MobMorph', new MobMorphCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void {
			foreach ($this->getServer()->getOnlinePlayers() as $player) {
				$morph = $this->getMorph($player);
				if ($morph->getTypeId() === MorphTypeIds::PLAYER) {
					continue;
				}

				//force the usage of the morph's size
				$scale = $player->getScale();
				$morphSize = $morph->getInitialSizeInfo()->scale($scale);
				if (!Utils::entitySizeInfoEquals($player->getSize(), $morphSize)) {
					$reflection = new ReflectionMethod($player, "setSize");
					$reflection->setAccessible(true);
					$reflection->invoke($player, $morphSize);
					$reflection->setAccessible(false);
				}

				$morph->onTick();
			}
		}), 1);

		$this->generatePlayerActorProperties();
	}

	public function onDisable() : void {
		if (isset($this->playersMorph)) {
			foreach ($this->playersMorph as $player => $morph) {
				if ($morph->getTypeId() === MorphTypeIds::PLAYER) {
					continue;
				}

				//force to restore player's properties
				$this->setMorph($player, PlayerMorph::class);
			}
			unset($this->playersMorph);
		}
	}

	public function generatePlayerActorProperties() : void{
		//TODO: get rid of this hardcoded shit >:C
		$this->playerActorProperties = new CacheableNbt(CompoundTag::create()
			->setTag("properties", (new ListTag([
				CompoundTag::create()
					->setTag("enum", new ListTag([new StringTag("unrolled"), new StringTag("rolled_up")], NBT::TAG_String))
					->setString("name", "morph:armadillo_state")
					->setInt("type", 3) //3 = enum
				,
				CompoundTag::create()
					->setString("name", "morph:bee_has_nectar")
					->setInt("type", 2) //2 = bool
				,
				CompoundTag::create()
					->setInt("max", 15)
					->setInt("min", 0)
					->setString("name", "morph:color")
					->setInt("type", 0) //0 = int
				,
				CompoundTag::create()
					->setInt("max", 15)
					->setInt("min", 0)
					->setString("name", "morph:color2")
					->setInt("type", 0) //0 = int
				,
				CompoundTag::create()
					->setInt("max", 80)
					->setInt("min", -1)
					->setString("name", "morph:entity")
					->setInt("type", 0) //0 = int
			], NBT::TAG_Compound)))
			->setString("type", EntityIds::PLAYER)
		);
	}

	/**
	 * @phpstan-return CacheableNbt<CompoundTag>
	 */
	public function getPlayerActorProperties() : CacheableNbt{
		return $this->playerActorProperties;
	}

	private function initMorphMapIfneeded() : void{
		if(!isset($this->playersMorph)){
			/** @phpstan-var \WeakMap<Player, Morph> $map */
			$map = new \WeakMap();
			$this->playersMorph = $map;
		}
	}

	public function getMorph(Player $player) : Morph{
		$this->initMorphMapIfneeded();

		return $this->playersMorph[$player] ??
			$this->setMorph($player, $this->getMorphManager()->getDefaultMorph()) ??
			throw new \Error("Error Processing Request")
		;
	}

	/**
	 * @template T of Morph
	 *
	 * @phpstan-param MorphVariant<T>|class-string<T>|null $morphSource
	 *
	 * @phpstan-return ?T
	 */
	public function setMorph(Player $player, MorphVariant|string|null $morphSource) : ?Morph{
		$this->initMorphMapIfneeded();

		if (isset($this->playersMorph[$player])) {
			$this->playersMorph[$player]->unset();
		}

		$morph = null;
		if ($morphSource === null) {
			unset($this->playersMorph[$player]);
		}elseif ($morphSource instanceof MorphVariant) {
			$morph = $morphSource->create($player);
		} else {
			$morph = new $morphSource($player);
		}

		if ($morph !== null) {
			$this->playersMorph[$player] = $morph;
			$morph->setup();
		}

		$player->sendData(null);

		return $morph;
	}

	public function getMorphManager() : MorphManager{
		return MorphManager::getInstance();
	}

	public function getFormManager() : FormManager{
		return FormManager::getInstance();
	}
}
