<?php

declare(strict_types=1);

namespace uhc\game\task;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use uhc\game\Game;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;
use uhc\sessions\types\TeamSession;

/**
 * Class RandomTeleportTask
 * @package uhc\game\task
 */
class RandomTeleportTask extends Task
{

    /** @var Game */
    private $game;

    /**
     * RandomTeleportTask constructor.
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @return Game
     */
    private function getGame(): Game
    {
        return $this->game;
    }

    /**
     * @return GamePlayer[]|TeamSession[]
     */
    private function getQueue(): array
    {
        if (!$this->getGame()->isTeams()) {
            return array_filter($this->getGame()->getPlayers('alive'), function (GamePlayer $player): bool {
                return !$player->getData()->isScattering();
            });
        } else {
            return array_filter($this->getGame()->getTeams('alive'), function (TeamSession $team): bool {
                return !$team->isScattering();
            });
        }
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        $queue = $this->getQueue();

        if (count($queue) > 0) {
            $key = array_rand($queue);
            $object = $queue[$key];

            if ($object instanceof GamePlayer)
                $this->getGame()->scatterPlayer($object);
            elseif ($object instanceof TeamSession)
                $this->getGame()->scatterTeam($object);
        } else {
            $this->getGame()->setState(GameState::STARTING);
            $this->getGame()->getPlugin()->getServer()->broadcastMessage(TextFormat::DARK_PURPLE . 'Player scattering has been completed');
            $this->getGame()->getSessions()->removeNullifiedPlayers();
            $this->cancel();
            $this->destroy();
        }
    }

    private function cancel(): void
    {
        $this->getGame()->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
    }

    private function destroy(): void
    {
        foreach ($this as $key => $value)
            unset($this->$key);
    }
}