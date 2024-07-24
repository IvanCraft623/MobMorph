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

use IvanCraft623\MobMorph\morph\monster\SkeletonMorph;
use IvanCraft623\MobMorph\morph\MorphTypeIds;
use IvanCraft623\MobMorph\morph\PlayerMorph;

use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\enchantment\VanillaEnchantments;

use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\SyncActorPropertyPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;

final class EventListener implements Listener {

	public function __construct(
		private MobMorph $plugin
	) {}

	public function onDataPacketSend(DataPacketSendEvent $event) : void {
		$packets = $event->getPackets();

		$isStartGamePacketSend = false;
		foreach($packets as $packet) {
			if ($packet instanceof StartGamePacket) {
				$packet->playerActorProperties = $this->plugin->getPlayerActorProperties();

				$isStartGamePacketSend = true;
			} elseif ($packet instanceof SetActorDataPacket) {
				$entity = $this->plugin->getServer()->getWorldManager()->findEntity($packet->actorRuntimeId);
				if (!$entity instanceof Player) {
					continue;
				}

				$morph = $this->plugin->getMorph($entity);

				//TODO: get rid of this hardcoded shit >:C
				$packet->syncedProperties = new PropertySyncData(
					intProperties: [
						0 => 0, //morph:armadillo_state
						1 => 0, //morph:bee_has_nectar
						2 => 0, //morph:color
						3 => 0, //morph:color2
						4 => $morph->getTypeId() //morph:entity
					],
					floatProperties: []
				);
			}
		}

		if ($isStartGamePacketSend) {
			//for some reason this needs to be sent twice
			$packets[] = SyncActorPropertyPacket::create($this->plugin->getPlayerActorProperties());
			$event->setPackets($packets);
		}
	}

	public function onEntityDamageEvent(EntityDamageEvent $event) : void{
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			$this->plugin->getMorph($entity)->attack($event);
		}
	}

	public function onPlayerQuitEvent(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		$currentMorph = $this->plugin->getMorph($player);
		if ($currentMorph->getTypeId() !== MorphTypeIds::PLAYER) {
			$this->plugin->setMorph($player, PlayerMorph::class); //force to restore player's properties
		}
	}

	public function onPlayerInteractEntity(PlayerEntityInteractEvent $event) : void{
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			$this->plugin->getMorph($entity)->onInteract($event->getPlayer(), $event->getClickPosition());
		}
	}

	public function onPlayerItemUseEvent(PlayerItemUseEvent $event) : void{
		$this->plugin->getMorph($event->getPlayer())->onItemUse($event->getItem(), $event->getDirectionVector());
	}

	public function onEntityShootBowEvent(EntityShootBowEvent $event) : void{
		$entity = $event->getEntity();
		if ($event->getBow()->hasEnchantment(VanillaEnchantments::INFINITY()) ||
			!$entity instanceof Player ||
			!$entity->hasFiniteResources()
		) {
			return;
		}

		$arrow = SkeletonMorph::getArrow();

		$inventory = match(true){
			$entity->getOffHandInventory()->contains($arrow) => $entity->getOffHandInventory(),
			$entity->getInventory()->contains($arrow) => $entity->getInventory(),
			default => null
		};

		if ($inventory === null) {
			return;
		}

		$inventory->addItem($arrow);
		if (($projectile = $event->getProjectile()) instanceof Arrow) {
			$projectile->setPickupMode(Arrow::PICKUP_NONE);
		}
	}

	/**
	 * @priority MONITOR
	 */
	public function onEntityHurt(EntityDamageEvent $event) : void{
		$entity = $event->getEntity();
		if ($entity instanceof Player &&
			$event->getFinalDamage() < $entity->getHealth()
		) {
			$entity->broadcastSound($this->plugin->getMorph($entity)->getHurtSound($event));
		}
	}

	/**
	 * @priority MONITOR
	 */
	public function onEntityDeath(EntityDeathEvent $event) : void{
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			$entity->broadcastSound($this->plugin->getMorph($entity)->getDeathSound());
		}
	}
}
