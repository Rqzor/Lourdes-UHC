<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class PingCommand
 * @package uhc\commands
 */
class PingCommand extends Command
{

    /**
     * PingCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('ping', 'Use this command to see your ping or that of other players');
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

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::GRAY . 'Your ping is currently at ' . TextFormat::LIGHT_PURPLE . $sender->getPing() . 'ms');
            return;
        }
        $player = $sender->getServer()->getPlayer($args[0]);

        if (!$player instanceof GamePlayer) {
            $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
            return;
        }
        $sender->sendMessage(TextFormat::GRAY . $player->getName(true) . "'s ping is " . TextFormat::LIGHT_PURPLE . $player->getPing() . 'ms');
    }
}