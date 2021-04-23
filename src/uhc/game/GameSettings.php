<?php

declare(strict_types=1);

namespace uhc\game;

/**
 * Class GameSettings
 */
class GameSettings
{

    # Global settings
    /** @var int */
    private $appleRate = 10;
    /** @var bool */
    private $globalMute = false;

    # Teams settings
    /** @var int */
    private $maxPlayers = 2;
    /** @var int */
    private $keyboardPlayers = 1;
    /** @var bool */
    private $teamDamage = false;

    # Global settings functions

    /**
     * @return int
     */
    public function getAppleRate(): int
    {
        return $this->appleRate;
    }

    /**
     * @return bool
     */
    public function isGlobalMute(): bool
    {
        return $this->globalMute;
    }

    /**
     * @param int $appleRate
     */
    public function setAppleRate(int $appleRate): void
    {
        $this->appleRate = $appleRate;
    }

    /**
     * @param bool $globalMute
     */
    public function setGlobalMute(bool $globalMute): void
    {
        $this->globalMute = $globalMute;
    }

    # Team settings functions

    /**
     * @return int
     */
    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    /**
     * @return int
     */
    public function getKeyboardPlayers(): int
    {
        return $this->keyboardPlayers;
    }

    /**
     * @return bool
     */
    public function isTeamDamage(): bool
    {
        return $this->teamDamage;
    }

    /**
     * @param int $maxPlayers
     */
    public function setMaxPlayers(int $maxPlayers): void
    {
        $this->maxPlayers = $maxPlayers;
    }

    /**
     * @param int $keyboardPlayers
     */
    public function setKeyboardPlayers(int $keyboardPlayers): void
    {
        $this->keyboardPlayers = $keyboardPlayers;
    }

    /**
     * @param bool $teamDamage
     */
    public function setTeamDamage(bool $teamDamage): void
    {
        $this->teamDamage = $teamDamage;
    }
}