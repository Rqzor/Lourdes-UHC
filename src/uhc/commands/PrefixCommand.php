<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;
use uhc\UHCLoader;

/**
 * Class PrefixCommand
 * @package uhc\commands
 */
class PrefixCommand extends Command
{

    /**
     * PrefixCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('prefix', 'Use the command to add/remove a prefix to the player');
        $this->setPermission('prefix.command.permission');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $config = UHCLoader::getInstance()->getConfig()->getAll()['prefix'];

        if (!$this->testPermission($sender))
            return;

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }

        switch (strtolower($args[0])) {
            case 'add':
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments ' . TextFormat::GRAY . '(/prefix add [player] [rank])');
                    return;
                }
                $player = $sender->getServer()->getPlayer($args[1]);
                $prefix = $args[2];

                if (!$player instanceof GamePlayer) {
                    $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
                    return;
                }

                if (!isset($config[$prefix])) {
                    $sender->sendMessage(TextFormat::RED . 'Prefix does not exist. Try again.');
                    return;
                }
                $player->setPrefix($prefix);
                $sender->sendMessage(TextFormat::GRAY . 'You added the ' . TextFormat::GREEN . strtolower($prefix) . TextFormat::GRAY . ' prefix to player ' . TextFormat::GREEN . $player->getName());
                $player->sendMessage(TextFormat::GRAY . 'You received prefix ' . TextFormat::GREEN . strtolower($prefix));
                break;

            case 'remove':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments ' . TextFormat::GRAY . '(/prefix remove [player])');
                    return;
                }
                $player = $sender->getServer()->getPlayer($args[1]);

                if (!$player instanceof GamePlayer) {
                    $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
                    return;
                }

                if ($player->getPrefix() == null) {
                    $sender->sendMessage(TextFormat::RED . 'Player does not contain any prefix.');
                }
                $player->removePrefix();
                $sender->sendMessage(TextFormat::GRAY . 'You eliminated the prefix that had the player ' . TextFormat::RED . $player->getName());
                $player->sendMessage(TextFormat::RED . 'The prefix you had was eliminated by a staff');
                break;

            default:
                $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                break;
        }
    }
}