<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\event\GameStartEvent;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;

/**
 * Class CatEyes
 * @package uhc\scenarios\defaults
 */
class CatEyes extends Scenario
{

    /**
     * CatEyes constructor.
     */
    public function __construct()
    {
        parent::__construct('Cat Eyes', 'All players receive night vision when the game begins', ItemFactory::get(ItemIds::ENDER_EYE));
    }

    /**
     * @param GamePlayer $player
     */
    public function addEffect(GamePlayer $player): void
    {
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), INT32_MAX, 1, false));
    }

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void
    {
        $game = $event->getGame();

        foreach ($game->getPlayers('online') as $player)
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), INT32_MAX, 1, false));
    }
}