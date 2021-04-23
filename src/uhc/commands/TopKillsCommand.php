<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\game\Game;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;

/**
 * Class TopKillsCommand
 * @package uhc\commands
 */
class TopKillsCommand extends Command
{

    /**
     * TopKillsCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('topkills', 'Use the command to see the top kills of the uhc');
        $this->setAliases(['kt']);
    }

    /**
     * @param Game $game
     * @return array
     */
    private function getPlayers(Game $game): array
    {
        $players = [];

        foreach ($game->getPlayers('scattering') as $player) {
            $players[$player->getName(true)] = $player->getEliminations();
        }
        return $players;
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

        if (in_array($sender->getGame()->getState(), [GameState::WAITING, GameState::SETUP])) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }
        $players = $this->getPlayers($sender->getGame());
        arsort($players);
        $sender->sendMessage(TextFormat::GRAY . 'TOP ' . TextFormat::LIGHT_PURPLE . '10 ' . TextFormat::GRAY . 'KILLS');

        for ($i = 0; $i < 10; $i++) {
            $kill = array_values($players);
            $player = array_keys($players);
            $pos = $i + 1;

            if (isset($player[$i]))
                $sender->sendMessage(TextFormat::GRAY . $pos . ' ' . TextFormat::LIGHT_PURPLE . $player[$i] . ' ' . TextFormat::GRAY . '- ' . TextFormat::WHITE . $kill[$i]);
            else
                $sender->sendMessage(TextFormat::GRAY . $pos . ' ' . TextFormat::GRAY . '- ');
        }
    }
}