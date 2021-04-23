<?php

declare(strict_types=1);

namespace uhc\event;

use pocketmine\event\Event;
use uhc\game\Game;

/**
 * Class GameEvent
 * @package uhc\event
 */
class GameEvent extends Event
{

    /** @var Game */
    protected $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }
}