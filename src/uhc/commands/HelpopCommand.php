<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class HelpopCommand
 * @package uhc\commands
 */
class HelpopCommand extends Command
{

    /**
     * HelpopCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('helpop', 'Use this command to send a message to the game hosts');
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
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }
        $sender->sendMessage(TextFormat::GREEN . 'HELPOP > The message to the hosters was sent successfully!');

        foreach ($sender->getGame()->getPlayers('host') as $player)
            $player->sendMessage(TextFormat::LIGHT_PURPLE . '[Helpop] ' . TextFormat::GRAY . $sender->getName() . ': ' . TextFormat::WHITE . implode(' ', $args));
    }
}