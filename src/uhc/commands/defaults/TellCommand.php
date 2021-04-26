<?php

declare(strict_types=1);

namespace uhc\commands\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;
use uhc\UHCLoader;

/**
 * Class TellCommand
 * @package uhc\commands\defaults
 */
class TellCommand extends Command
{

    /**
     * TellCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('tell', 'Sends a private message to the given player', '/tell <player> <private message ...>', ['w', 'msg']);
        $this->setPermission('pocketmine.command.tell');
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

        if (count($args) < 2)
            throw new InvalidCommandSyntaxException();
        $player = $sender->getServer()->getPlayer(array_shift($args));

        if ($player === $sender) {
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . '%commands.message.sameTarget'));
            return;
        }

        if (!$player instanceof GamePlayer) {
            $sender->sendMessage(new TranslationContainer('commands.generic.player.notFound'));
            return;
        }
        $sender->sendMessage('[' . $sender->getName() . ' -> ' . $player->getDisplayName() . '] ' . implode(' ', $args));
        $name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
        $player->sendMessage('[' . $name . ' -> ' . $player->getName() . '] ' . implode(' ', $args));

        $game = UHCLoader::getInstance()->getGame();

        foreach ($game->getPlayers('host') as $host)
            if ($host->getName() != $sender->getName())
                $host->sendMessage('[' . $sender->getName() . ' -> ' . $player->getName() . '] ' . implode(' ', $args));
    }
}