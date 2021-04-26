<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\event\GameStartEvent;
use uhc\scenarios\Scenario;

/**
 * Class Anonymous
 * @package uhc\scenarios\defaults
 */
class Anonymous extends Scenario
{

    /** @var string[] */
    private $randomNames = [
        'xKalsk',
        'Ana2012'
    ];
    /** @var array */
    private $players = [];

    /**
     * Anonymous constructor.
     */
    public function __construct()
    {
        parent::__construct('Anonymous', '', ItemFactory::get(ItemIds::));
    }

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void
    {
        $game = $event->getGame();

        foreach ($game->getPlayers('online') as $player)
            $player->sendMessage('lol pa');
    }
}