<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class GlobalMuteCommand
 * @package uhc\commands
 */
class GlobalMuteCommand extends Command
{

    /**
     * GlobalMuteCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('globalmute', 'Use this command so that players cannot speak');
        $this->setPermission('globalmute.command.permission');
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

        if ($sender->getGame()->getSettings()->isGlobalMute()) {
            $sender->getGame()->getSettings()->setGlobalMute(false);
            $sender->getServer()->broadcastMessage(TextFormat::GREEN . '[GM] The GlobalMute was deactivated');
        } else {
            $sender->getGame()->getSettings()->setGlobalMute(true);
            $sender->getServer()->broadcastMessage(TextFormat::RED . '[GM] The GlobalMute was activated');
        }
    }
}