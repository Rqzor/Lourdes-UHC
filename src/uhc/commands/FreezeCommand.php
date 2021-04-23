<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class FreezeCommand
 * @package uhc\commands
 */
class FreezeCommand extends Command
{

    /**
     * FreezeCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('freeze', 'Use this command to freeze players');
        $this->setPermission('freeze.command.permission');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof GamePlayer)
            return;

        if (!$this->testPermission($sender))
            return;

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }
        $player = $sender->getServer()->getPlayer($args[0]);

        if (!$player instanceof GamePlayer) {
            $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
            return;
        }

        if ($player->getData()->isFreeze()) {
            $player->getData()->setFreeze(false);
            $player->sendMessage(TextFormat::GREEN . 'You have been unfreeze. Go back to play!');
            $sender->sendMessage(TextFormat::GREEN . 'You have unfrozen ' . $player->getName() . '. Now the player can play again!');
        } else {
            $player->getData()->setFreeze(true);
            $player->sendMessage(TextFormat::RED . 'You have been frozen by ' . $sender->getName() . '. Follow the instructions to avoid getting banned');
            $sender->sendMessage(TextFormat::RED . 'You have frozen ' . $player->getName() . '. Tell the player the instructions to follow');
        }
    }
}