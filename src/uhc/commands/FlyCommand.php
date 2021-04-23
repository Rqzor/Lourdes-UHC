<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;

/**
 * Class FlyCommand
 * @package uhc\commands
 */
class FlyCommand extends Command
{
	
	/**
     * FlyCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('fly', 'Use this command to activate/deactivate the fly');
        $this->setPermission('fly.command.permission');
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

        if ($sender->getGame()->getState() != GameState::WAITING) {
        	$sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
		}
            
		if ($sender->isInPractice()) {
			$sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
			return;
		}

        if ($sender->getAllowFlight()) {
            $sender->setAllowFlight(false);
            $sender->setFlying(false);
            $sender->sendMessage(TextFormat::RED . 'You have deactivated the fly');
        } else {
            $sender->setAllowFlight(true);
            $sender->setFlying(true);
            $sender->sendMessage(TextFormat::GREEN . 'You have activated the fly');
        }
    }
}