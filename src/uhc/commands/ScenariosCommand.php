<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class ScenariosCommand
 * @package uhc\commands
 */
class ScenariosCommand extends Command
{

    /**
     * ScenariosCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('scenarios', 'Use this command to see the scenarios of the game');
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

        if (count($sender->getGame()->getScenarios()->getActives()) == 0) {
            $sender->sendMessage(TextFormat::RED . 'No scenario was activated.');
            return;
        }
        $sender->sendMessage(TextFormat::GOLD . 'Scenarios activated');

        foreach ($sender->getGame()->getScenarios()->getActives() as $scenario) {
            $sender->sendMessage(TextFormat::GOLD . $scenario->getName() . ' ' . TextFormat::GRAY . '- ' . TextFormat::WHITE . $scenario->getDescription());
        }
    }
}