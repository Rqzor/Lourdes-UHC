<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;

/**
 * Class PracticeCommand
 * @package uhc\commands
 */
class PracticeCommand extends Command
{

    public function __construct()
    {
        parent::__construct('practice');
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

        if ($sender->getGame()->getState() != GameState::WAITING) {
            $sender->sendMessage(TextFormat::RED . 'The practice is no longer available because the game has already started.');
            return;
        }

        switch (strtolower($args[0])) {
            case 'spawn':
                if (!$sender->hasPermission('spawn.practice.command')) {
                    $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
                    return;
                }
                $level = $sender->getServer()->getLevelByName($args[1]);

                if (!$level instanceof Level) {
                    $sender->sendMessage(TextFormat::RED . 'This world does not exist or is not loaded.');
                    return;
                }
                $sender->getGame()->getPractice()->setArena($level);
                $sender->sendMessage(TextFormat::GREEN . 'You have configured the practice.');
                break;

            case 'join':
                if ($sender->isInPractice()) {
                    $sender->sendMessage(TextFormat::RED . 'You are already in the practice.');
                    return;
                }
                $sender->getGame()->getPractice()->joinArena($sender);
                break;

            case 'quit':
                if (!$sender->isInPractice()) {
                    $sender->sendMessage(TextFormat::RED . 'You are not in the practice.');
                    return;
                }
                $sender->getGame()->getPractice()->quitArena($sender);
                break;

            default:
                $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                break;
        }
    }
}