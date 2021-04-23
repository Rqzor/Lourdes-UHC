<?php

declare(strict_types=1);

namespace uhc\commands;

use addon\AddonLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;

/**
 * Class NickCommand
 * @package uhc\commands
 */
class NickCommand extends Command
{

    /** @var Config */
    private $players, $nicks;

    /**
     * NickCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('nick');
        $this->setPermission('nick.command.permission');

        # Config
        $this->players = new Config(AddonLoader::getInstance()->getDataFolder() . 'players.yml', Config::YAML);
        $this->nicks = new Config(AddonLoader::getInstance()->getDataFolder() . 'nicks.yml', Config::YAML);
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

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }

        switch (strtolower($args[0])) {
            case 'set':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Use /nick set [nickName]');
                    return;
                }

                if ($this->players->exists($args[1])) {
                    $sender->sendMessage(TextFormat::RED . "You cannot use another player's name");
                    return;
                }

                if ($this->existsNick($sender->getName(), $args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'This nick already has another owner');
                    return;
                }

                # Config
                $this->nicks->set($sender->getName(), $args[1]);
                $this->nicks->save();

                $sender->sendMessage(TextFormat::GREEN . 'Your nickname has been successfully changed to ' . $args[1]);
                break;

            case 'remove':
                if (!$this->nicks->exists($sender->getName())) {
                    $sender->sendMessage(TextFormat::RED . 'Your name is not changed');
                    return;
                }

                # Config
                $this->nicks->remove($sender->getName());
                $this->nicks->save();

                $sender->sendMessage(TextFormat::GREEN . 'You have successfully removed your modified nick');
                break;
        }
    }

    /**
     * @param string $player
     * @param string $nick
     * @return bool
     */
    private function existsNick(string $player, string $nick): bool
    {
        foreach ($this->nicks->getAll() as $owner => $n) {
            if ($owner != $player && $n == $nick)
                return true;
        }
        return false;
    }
}