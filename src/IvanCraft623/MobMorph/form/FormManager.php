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

namespace IvanCraft623\MobMorph\form;

use IvanCraft623\MobMorph\MobMorph;
use IvanCraft623\MobMorph\morph\Morph;
use IvanCraft623\MobMorph\morph\MorphManager;
use IvanCraft623\MobMorph\morph\MorphVariant;

use jojoe77777\FormAPI\SimpleForm;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

use function count;

/**
 * Compilation of all the forms in this plugin.
 */
final class FormManager {
	use SingletonTrait;

	/**
	 * @phpstan-param ?non-empty-list<class-string<Morph>> $morphs
	 */
	public function sendSelectMorph(Player $player, ?array $morphs = null) : void{
		$morphManager = MorphManager::getInstance();
		$morphs ??= $morphManager->getAll();

		$form = new SimpleForm(function (Player $player, ?string $morph = null) {
			/** @phpstan-var ?class-string<Morph> $morph */
			if ($morph === null) {
				return;
			}

			$variants = MorphManager::getInstance()->getVariants($morph);
			if (count($variants) === 1) {
				MobMorph::getInstance()->setMorph($player, $variants[0]);
			} else {
				self::sendSelectMorphVariant($player, $variants);
			}

		});
		$form->setTitle("Morph menu");
		$form->setContent("morph:morph_menu");

		$currentMorph = MobMorph::getInstance()->getMorph($player);
		foreach ($morphs as $morph) {
			if ($currentMorph->getTypeId() === $morph::getTypeId() && count($morphManager->getVariants($morph)) === 1) {
				continue;
			}

			$form->addButton(
				$morph::getName(),
				SimpleForm::IMAGE_TYPE_PATH, $morph::getIconPath(),
				$morph
			);
		}
		$player->sendForm($form);
	}

	/**
	 * @phpstan-param non-empty-list<MorphVariant<Morph>> $variants
	 */
	public function sendSelectMorphVariant(Player $player, array $variants) : void{
		$form = new SimpleForm(function (Player $player, ?MorphVariant $variant = null) {
			if ($variant === null) {
				return;
			}

			MobMorph::getInstance()->setMorph($player, $variant);

		});
		$form->setTitle("Morph menu");
		$form->setContent("morph:morph_menu");

		foreach ($variants as $variant) {
			//TODO: there is no current way to compare if variants are the same

			$form->addButton(
				"xds",
				SimpleForm::IMAGE_TYPE_PATH, $variant->getIconPath(),
				$variant
			);
		}
		$player->sendForm($form);
	}
}
