<?php

namespace uhc\event\player;

use pocketmine\event\Event;
use uhc\player\GamePlayer;

/**
 * Class PlayerEvent
 * @package uhc\event\player
 */
class PlayerEvent extends Event
{
    
    /** @var GamePlayer */
    protected $player;

    /**
     * PlayerEvent constructor.
     * @param GamePlayer $player
     */
    public function __construct(GamePlayer $player)
    {
        $this->player = $player;
    }

    /**
     * @return GamePlayer
     */
    public function getPlayer(): GamePlayer
    {
        return $this->player;
    }
}