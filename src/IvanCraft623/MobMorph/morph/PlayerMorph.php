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

use IvanCraft623\MobMorph\sound\GenericSound;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;

final class PlayerMorph extends Morph{

	public static function getName() : string{
		return "Player";
	}

	public static function getTypeId() : int{
		return MorphTypeIds::PLAYER;
	}

	/**
	 * Actually we don't use this for players.
	 */
	public static function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.8, 0.6, 1.62);
	}

	public static function getIconPath() : string{
		return "textures/icons/morph_menu/player/player";
	}

	public function getHurtSound(EntityDamageEvent $source) : GenericSound{
		return match ($source->getCause()) {
			EntityDamageEvent::CAUSE_DROWNING => new GenericSound(soundName: "mob.player.hurt_drown"),
			EntityDamageEvent::CAUSE_FIRE_TICK => new GenericSound(soundName: "mob.player.hurt_on_fire"),
			//TODO: freezeing (powder snow damage)
			default => new GenericSound(soundName: "game.player.hurt", volume: 1, pitch: $this->getSoundPitch())
		};
	}

	public function getDeathSound() : GenericSound{
		return new GenericSound(soundName: "game.player.die", volume: 1, pitch: $this->getSoundPitch());
	}

	public function getAmbientSound() : ?GenericSound{
		return null;
	}
}
