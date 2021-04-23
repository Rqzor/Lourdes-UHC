<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class MuteCommand
 * @package uhc\commands
 */
class MuteCommand extends Command
{

    /**
     * MuteCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('mute', 'Use this command to mute a player');
        $this->setPermission('mute.command.permission');
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

        if ($player->getData()->isMute()) {
            $player->getData()->setMute(false);
            $sender->getServer()->broadcastMessage(TextFormat::GREEN . $player->getName(true) . TextFormat::GRAY . ' has been unmuted');
        } else {
            $player->getData()->setMute(true);
            $player->sendMessage(TextFormat::RED . 'You were mutated by the staff ' . $sender->getName());
            $sender->getServer()->broadcastMessage(TextFormat::RED . $player->getName(true) . TextFormat::GRAY . ' has been temporarily muted by ' . TextFormat::RED . $sender->getName());
        }
    }
}