<?php

declare(strict_types=1);

namespace uhc\commands\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class KickCommand
 * @package uhc\commands\defaults
 */
class KickCommand extends Command
{

    /**
     * KickCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('kick', '%pocketmine.command.kick.description', '%commands.kick.usage');
        $this->setPermission('pocketmine.command.kick');
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$this->testPermission($sender))
            return true;

        if (count($args) === 0)
            throw new InvalidCommandSyntaxException();
        $name = array_shift($args);
        $reason = trim(implode(' ', $args));

        if (($player = $sender->getServer()->getPlayer($name)) instanceof GamePlayer) {
            $player->kick($reason);
            
            if ($reason !== '')
                Command::broadcastCommandMessage($sender, new TranslationContainer('commands.kick.success.reason', [$player->getName(), $reason]));
            else
                Command::broadcastCommandMessage($sender, new TranslationContainer('commands.kick.success', [$player->getName()]));
        } else
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . '%commands.generic.player.notFound'));
        return true;
    }
}