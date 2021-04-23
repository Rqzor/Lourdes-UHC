<?php

namespace uhc\sessions\types;

use pocketmine\level\Position;
use uhc\game\Game;
use uhc\player\GamePlayer;
use uhc\sessions\utils\SessionUtils;
use uhc\UHCLoader;

/**
 * Class TeamSession
 * @package uhc\sessions\types
 */
class TeamSession
{

    /** @var int */
    private $teamInt;
    /** @var string */
    private $format;
    /** @var string */
    private $owner;
    /** @var bool */
    private $scattering = false;
    /** @var Position|null */
    private $scatterPosition = null;

    /**
     * TeamSession constructor.
     * @param int $teamInt
     * @param string $owner
     */
    public function __construct(int $teamInt, string $owner)
    {
        $this->teamInt = $teamInt;
        $this->owner = $owner;
        $this->format = SessionUtils::getRandomColor() . '[Team ' . $teamInt . ']';
    }

    /**
     * @return Game
     */
    private function getGame(): Game
    {
        return UHCLoader::getInstance()->getGame();
    }

    /**
     * @return int
     */
    public function getTeamInt(): int
    {
        return $this->teamInt;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return string
     */
    public function getOwner(): string
    {
        return $this->owner;
    }

    /**
     * @return bool
     */
    public function isScattering(): bool
    {
        return $this->scattering;
    }

    /**
     * @return Position|null
     */
    public function getScatterPosition(): ?Position
    {
        return $this->scatterPosition;
    }

    /**
     * @param string $owner
     */
    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @param bool $scattering
     */
    public function setScattering(bool $scattering): void
    {
        $this->scattering = $scattering;
    }

    /**
     * @param Position|null $scatterPosition
     */
    public function setScatterPosition(?Position $scatterPosition): void
    {
        $this->scatterPosition = $scatterPosition;
    }

    public function getEliminations(): int
    {
        $eliminations = 0;

        foreach ($this->getPlayers() as $player) {
            assert($player instanceof PlayerSession);
            $eliminations += $player->getEliminations();
        }
        return $eliminations;
    }

    /**
     * @param string $type
     * @return PlayerSession[]|GamePlayer[]|null
     */
    public function getPlayers($type = 'all'): ?array
    {
        switch ($type) {
            case 'all':
                return array_filter($this->getGame()->getSessions()->getPlayers(), function (PlayerSession $player): bool {
                    return $player->getTeam() == $this->getTeamInt();
                });

            case 'alive':
                return array_filter($this->getGame()->getPlayers('alive'), function (GamePlayer $player): bool {
                    return $player->getData()->getTeam() == $this->getTeamInt();
                });

            case 'alives':
                return array_filter($this->getGame()->getPlayers('alives'), function (PlayerSession $player): bool {
                    return $player->getTeam() == $this->getTeamInt();
                });

            case 'keyboard':
                return array_filter($this->getPlayers(), function (PlayerSession $player): bool {
                    return $player->getInput() == 'Keyboard';
                });
        }
        return null;
    }

    /**
     * @param string $message
     */
    public function broadcast(string $message): void
    {
        foreach ($this->getPlayers('alive') as $player)
            $player->sendMessage($message);
    }
}