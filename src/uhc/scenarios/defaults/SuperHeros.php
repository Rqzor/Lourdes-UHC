<?php


namespace uhc\scenarios\defaults;


use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\event\GameStartEvent;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;

class SuperHeros extends Scenario
{

    /**
     * SuperHeros constructor.
     */
    public function __construct()
    {
        parent::__construct('Super Heros', 'All players receive randomEffect when the game begins', ItemFactory::get(ItemIds::BLAZE_POWDER));
    }

    /**
     * @param GamePlayer $player
     */
    public function addEffect(GamePlayer $player): void
    {
        $effectId = $this->getRandomEffect();
        $amplifier = $effectId == 21 ? 4 : 1;
        $player->addEffect(new EffectInstance(Effect::getEffect($effectId), INT32_MAX, $amplifier, false));
    }

    /**
     * @return int
     */
    public function getRandomEffect(): int
    {
        $effects = [
            Effect::SPEED,
            Effect::STRENGTH,
            Effect::RESISTANCE,
            Effect::HEALTH_BOOST,
        ];

        return $effects[array_rand($effects)];
    }

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void
    {
        $game = $event->getGame();

        foreach ($game->getPlayers('online') as $player) {
            $effectId = $this->getRandomEffect();
            $amplifier = $effectId == 21 ? 4 : 1;
            $player->addEffect(new EffectInstance(Effect::getEffect($effectId), INT32_MAX, $amplifier, false));
        }
    }

}