<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;
use uhc\UHCLoader;

/**
 * Class RankCommand
 * @package uhc\commands
 */
class RankCommand extends Command
{

    /**
     * RankCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('rank', 'Use this command to add/remove rank to a player');
        $this->setPermission('rank.command.permission');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $config = UHCLoader::getInstance()->getConfig()->getAll()['ranks'];

        if (!$this->testPermission($sender))
            return;

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }

        switch (strtolower($args[0])) {
            case 'add':
                if (!isset($args[1]) || !isset($args[2])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments ' . TextFormat::GRAY . '(/rank add [player] [rank])');
                    return;
                }
                $player = $sender->getServer()->getPlayer($args[1]);
                $rank = $args[2];

                if (!$player instanceof GamePlayer) {
                    $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
                    return;
                }

                if (!isset($config[$rank])) {
                    $sender->sendMessage(TextFormat::RED . 'Rank does not exist. Try again.');
                    return;
                }
                $player->setRank($rank);
                $player->clearPermissions();
                $player->addPermissions();
                $sender->sendMessage(TextFormat::GRAY . 'You added the ' . TextFormat::GREEN . strtolower($rank) . TextFormat::GRAY . ' rank to player ' . TextFormat::GREEN . $player->getName());
                $player->sendMessage(TextFormat::GRAY . 'You received rank ' . TextFormat::GREEN . strtolower($rank));
                break;

            case 'remove':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments ' . TextFormat::GRAY . '(/rank remove [player])');
                    return;
                }
                $player = $sender->getServer()->getPlayer($args[1]);

                if (!$player instanceof GamePlayer) {
                    $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
                    return;
                }

                if ($player->getRank() == null) {
                    $sender->sendMessage(TextFormat::RED . 'Player does not contain any rank.');
                }
                $player->removeRank();
                $player->clearPermissions();
                $sender->sendMessage(TextFormat::GRAY . 'You eliminated the rank that had the player ' . TextFormat::RED . $player->getName());
                $player->sendMessage(TextFormat::RED . 'The rank you had was eliminated by a staff');
                break;

            default:
                $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                break;
        }
    }
}