<?php

declare(strict_types=1);

namespace uhc\sessions;

use uhc\player\GamePlayer;
use uhc\sessions\types\PlayerSession;
use uhc\sessions\types\TeamSession;

/**
 * Class SessionManager
 * @package uhc\sessions
 */
class SessionManager
{

    /** @var PlayerSession[] */
    private $players = [];
    /** @var TeamSession[] */
    private $teams = [];

    /**
     * @return PlayerSession[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * @return TeamSession[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @param string|GamePlayer $player
     * @return PlayerSession|null
     */
    public function getPlayer($player): ?PlayerSession
    {
        $name = $player instanceof GamePlayer ? $player->getName(false) : $player;
        return isset($this->players[$name]) ? $this->players[$name] : null;
    }

    public function removeNullifiedPlayers(): void
    {
        foreach ($this->getPlayers() as $player) {
            if (!$player->isOnline()) {
                $player->setSpectator(true);
            }
        }
    }

    /**
     * @param int $teamInt
     * @return TeamSession|null
     */
    public function getTeam(int $teamInt): ?TeamSession
    {
        return isset($this->teams[$teamInt]) ? $this->teams[$teamInt] : null;
    }

    /**
     * @param GamePlayer $player
     */
    public function addPlayer(GamePlayer $player): void
    {
        $name = $player->getName(false);
        $session = new PlayerSession($name);
        $this->players[$name] = $session;
    }

    /**
     * @param GamePlayer $player
     */
    public function addTeam(GamePlayer $player): void
    {
        $name = $player->getName(false);
        $teamInt = count($this->getTeams()) + 1;
        $session = new TeamSession($teamInt, $name);
        $this->teams[$teamInt] = $session;
        $player->getData()->setTeam($teamInt);
    }
}