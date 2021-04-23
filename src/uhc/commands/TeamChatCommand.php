<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class TeamChatCommand
 * @package uhc\commands
 */
class TeamChatCommand extends Command
{

    /**
     * TeamChatCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('teamchat', 'Use this command for your team private chat');
        $this->setAliases(['tc']);
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
        
        if (!$sender->getGame()->isTeams()) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        if (!$sender->getData()->isAlive() || $sender->getTeam() == null) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }

        foreach ($sender->getTeam()->getPlayers('alive') as $player)
            $player->sendMessage(TextFormat::YELLOW . '[Team] ' . TextFormat::GRAY . $sender->getName(true) . ': ' . TextFormat::WHITE . implode(' ', $args));
    }
}