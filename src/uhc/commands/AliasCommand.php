<?php

declare(strict_types=1);

namespace uhc\commands;

use addon\AddonLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class AliasCommand
 * @package uhc\commands
 */
class AliasCommand extends Command
{

    /**
     * AliasCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('alias', 'You will be able to see all the accounts that a player has');
        $this->setPermission('alias.command.permission');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $config = new Config(AddonLoader::getInstance()->getDataFolder() . 'players.yml', Config::YAML);

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
            $player = $player->getName();
        } else {
            $player = $args[0];

            if (!$config->exists($player)) {
                $sender->sendMessage(TextFormat::RED . 'Player is not registered on the server.');
                return;
            }
        }
        $players = [];
        $data = (array) $config->get($player);

        foreach ($config->getAll() as $p => $d) {
            if (isset($d['address']) && isset($data['address']) && $d['address'] == $data['address'] || isset($d['cid']) && isset($data['cid']) && $d['cid'] == $data['cid'])
                $players[] = $p;
        }
        $accounts = array_map(function (string $name) use ($player): string {
            return $name == $player ? TextFormat::GREEN . $name : TextFormat::GRAY . $name;
        }, $players);
        $sender->sendMessage(TextFormat::GREEN . $player . TextFormat::GRAY . "' accounts: " . TextFormat::LIGHT_PURPLE . implode(TextFormat::GRAY . ', ', $accounts));
    }
}