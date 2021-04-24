<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;
use uhc\scenarios\defaults\CatEyes;
use uhc\scenarios\defaults\SuperHeros;

/**
 * Class RespawnCommand
 * @package uhc\commands
 */
class RespawnCommand extends Command
{
    /**
     * RespawnCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('respawn', 'Use command to revive a player');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof GamePlayer || !$sender->isSpawned())
            return;

        if (!$sender->getData()->isHost())
            return;

        if (in_array($sender->getGame()->getState(), [GameState::WAITING, GameState::SETUP])) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }
        $player = $sender->getServer()->getPlayer($args[0]);

        if (!$player instanceof GamePlayer) {
            $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
            return;
        }

        if (!$player->getData()->isSpectator()) {
            $sender->sendMessage(TextFormat::RED . 'The player cannot be respawned');
            return;
        }

        if ($player->getData()->isHost()) {
            $sender->sendMessage(TextFormat::RED . 'The player cannot be respawned');
            return;
        }

        if (!$player->getData()->isScattering()) {
            $player->reset(3);
            $player->getData()->setSpectator(false);
            $sender->getGame()->respawn($player);

            if (($scenario = $sender->getGame()->getScenarios()->isActiveByName('Cat Eyes')) != null) {
                assert($scenario instanceof CatEyes);
                $scenario->addEffect($player);
            }
            if (($scenario = $sender->getGame()->getScenarios()->isActiveByName('Super Heros')) != null){
                assert($scenario instanceof SuperHeros);
                $scenario->addEffect($player);
            }
            $player->sendMessage(TextFormat::GREEN . 'You have been given the starting items.');
        } else {
            $player->getData()->setSpectator(false);
            $player->reset();
            $pos = $player->getData()->getLastPosition() != null ? $player->getData()->getLastPosition() : $player->getGame()->fixTeleport();
            $player->teleport($pos);
            $contents = $player->getData()->getContents();

            if (($scenario = $sender->getGame()->getScenarios()->isActiveByName('Cat Eyes')) != null) {
                assert($scenario instanceof CatEyes);
                $scenario->addEffect($player);
            }
            $player->getArmorInventory()->setContents(array_map(function (array $data): Item {
                return Item::jsonDeserialize($data);
            }, $contents['armorContents']));
            $player->getInventory()->setContents(array_map(function (array $data): Item {
                return Item::jsonDeserialize($data);
            }, $contents['contents']));
            $player->sendMessage(TextFormat::GREEN . 'You have received your last known items.');
        }
        $player->sendMessage(TextFormat::GREEN . 'You have been respawned. Good luck!');
        $sender->sendMessage(TextFormat::GREEN . 'You have revived player ' . $player->getName() . ' successfully!');
    }
}