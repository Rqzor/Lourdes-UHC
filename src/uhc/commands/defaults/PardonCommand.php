<?php

declare(strict_types=1);

namespace uhc\commands\defaults;

use addon\AddonLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

/**
 * Class PardonCommand
 * @package uhc\commands\defaults
 */
class PardonCommand extends Command
{

    /**
     * PardonCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('pardon', 'Use the command to unban a player');
        $this->setPermission('pocketmine.command.unban.player');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $config = new Config(AddonLoader::getInstance()->getDataFolder() . 'bans.yml', Config::YAML);
        $bans = $config->getAll();

        if (!$this->testPermission($sender))
            return;

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }
        $player = $args[0];

        if (!isset($bans[$player])) {
            $sender->sendMessage(TextFormat::RED . 'Player is not banned from the server.');
            return;
        }
        $config->remove($player);
        $config->save();
        $sender->getServer()->broadcastMessage(TextFormat::RED . $player . TextFormat::GRAY . ' has been unbanned from the server');
    }
}