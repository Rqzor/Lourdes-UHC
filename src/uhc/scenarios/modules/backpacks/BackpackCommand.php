<?php

declare(strict_types=1);

namespace uhc\scenarios\modules\backpacks;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;
use uhc\scenarios\defaults\Backpacks;

/**
 * Class BackpackCommand
 * @package uhc\scenarios\modules\backpacks
 */
class BackpackCommand extends Command
{

    /**
     * BackpackCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('backpack', "You open your team's backpack");
        $this->setAliases(['bp']);
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

        if ($sender->getGame()->getState() != GameState::RUNNING) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        if (!$sender->getData()->isAlive()) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        if ($sender->getTeam() == null){
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        $scenario = $sender->getGame()->getScenarios()->isActiveByName('Backpacks');
        assert($scenario instanceof Backpacks);
        $backpack = $scenario->getBackpack($sender->getTeam());
        $backpack->send($sender);
    }
}