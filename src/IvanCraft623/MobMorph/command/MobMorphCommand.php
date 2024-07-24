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

namespace IvanCraft623\MobMorph\command;

use IvanCraft623\MobMorph\MobMorph;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;

final class MobMorphCommand extends Command implements PluginOwned {

	private MobMorph $plugin;

	public function __construct(MobMorph $plugin) {
		parent::__construct('mobmorph', 'Morph into a mob');

		$this->plugin = $plugin;
		$this->setPermission("mobmorph.command");
	}

	public function getOwningPlugin() : MobMorph {
		return $this->plugin;
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (!$this->checkPermission($sender)) {
			return;
		}
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cThis command can only be used in-game!");
			return true;
		}

		$this->plugin->getFormManager()->sendSelectMorph($sender);
	}

	public function checkPermission(CommandSender $sender) : bool {
		if (!$this->testPermission($sender)) {
			$sender->sendMessage("§cYou do not have permission to use this command!");
			return false;
		}
		return true;
	}
}
