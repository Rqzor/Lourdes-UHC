<?php

declare(strict_types=1);

namespace uhc\commands\defaults;

use addon\AddonLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use uhc\form\FormUtils;
use uhc\player\GamePlayer;

/**
 * Class BanCommand
 * @package uhc\commands\defaults
 */
class BanCommand extends Command
{

    /**
     * BanCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('ban', 'Use the command to ban a player');
        $this->setPermission('pocketmine.command.ban.player');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $players = (new Config(AddonLoader::getInstance()->getDataFolder() . 'players.yml', Config::YAML))->getAll();

        if (!$sender instanceof GamePlayer)
            return;

        if (!$this->testPermission($sender))
            return;

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }
        $player = $sender->getServer()->getPlayer($args[0]);

        if ($player instanceof GamePlayer) {
            FormUtils::createBanForm($sender, $player->getName());
        } else {
            $player = $args[0];

            if (!isset($players[$player])) {
                $sender->sendMessage(TextFormat::RED . 'Player is not registered on the server.');
                return;
            }
            FormUtils::createBanForm($sender, $player);
        }
    }
}