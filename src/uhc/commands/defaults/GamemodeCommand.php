<?php

declare(strict_types=1);

namespace uhc\commands\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class GamemodeCommand
 * @package uhc\commands\defaults
 */
class GamemodeCommand extends Command
{

    /**
     * GamemodeCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('gamemode', 'Change gamemode of a player', '/gamemode', ['gm']);
        $this->setPermission('pocketmine.command.gamemode');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$this->testPermission($sender))
            return;

        if (count($args) === 0)
            throw new InvalidCommandSyntaxException();
        $gameMode = Server::getGamemodeFromString($args[0]);

        if ($gameMode === -1) {
            $sender->sendMessage('Unknown game mode');
            return;
        }

        if (isset($args[1])) {
            $target = $sender->getServer()->getPlayer($args[1]);

            if ($target === null) {
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . '%commands.generic.player.notFound'));
                return;
            }
        } elseif ($sender instanceof GamePlayer) {
            $target = $sender;
        } else {
            throw new InvalidCommandSyntaxException();
        }
        $target->setGamemode($gameMode);

        if ($gameMode !== $target->getGamemode()) {
            $sender->sendMessage('Game mode change for ' . $target->getName() . ' failed!');
        } else {
            if ($target === $sender) {
                Command::broadcastCommandMessage($sender, new TranslationContainer('commands.gamemode.success.self', [Server::getGamemodeString($gameMode)]));
            } else {
                $target->sendMessage(new TranslationContainer('gameMode.changed', [Server::getGamemodeString($gameMode)]));
                Command::broadcastCommandMessage($sender, new TranslationContainer('commands.gamemode.success.other', [Server::getGamemodeString($gameMode), $target->getName()]));
            }
        }
    }
}