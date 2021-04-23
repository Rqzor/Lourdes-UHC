<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\game\utils\GameUtils;
use uhc\player\GamePlayer;

/**
 * Class StaffCommand
 * @package uhc\commands
 */
class StaffCommand extends Command
{

    /**
     * StaffCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('staff');
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

        if ($sender->getGame()->getState() == GameState::WAITING || !$sender->getData()->isHost()) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

       if ($sender->getData()->isModeStaff()) {
           $sender->reset(1);
           $sender->getData()->setModeStaff(false);
           GameUtils::setVanish($sender, true);
       } else {
           $sender->reset(1);
           $sender->getInventory()->setContents(GameUtils::getKit('staff'));
           $sender->setAllowFlight(true);
           $sender->getData()->setModeStaff(true);
           GameUtils::setVanish($sender);
       }
    }
}