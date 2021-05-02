<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use uhc\event\GameStartEvent;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;

/**
 * Class SuperHeros
 * @package uhc\scenarios\defaults
 */
class SuperHeros extends Scenario
{

    /** @var array */
    private static $randomEffects = [
        Effect::SPEED,
        Effect::STRENGTH,
        Effect::RESISTANCE,
        Effect::HEALTH_BOOST,
    ];
    /** @var array */
    private $effects = [];

    /**
     * SuperHeros constructor.
     */
    public function __construct()
    {
        parent::__construct('Super Heros', 'All players receive randomEffect when the game begins', ItemFactory::get(ItemIds::BLAZE_POWDER));
    }

    /**
     * @param GamePlayer|string $player
     * @return bool
     */
    public function hasEffect($player): bool
    {
        $name = $player instanceof GamePlayer ? $player->getName() : $player;
        return isset($this->effects[$name]);
    }

    /**
     * @param $player
     * @return array
     */
    public function getEffect($player): array
    {
        $name = $player instanceof GamePlayer ? $player->getName() : $player;
        return $this->effects[$name];
    }

    /**
     * @param GamePlayer $player
     */
    public function addEffect(GamePlayer $player): void
    {
        if ($this->hasEffect($player)) {
            $effectData = $this->getEffect($player);
            $id = (int) $effectData[0];
            $amplifier = (int) $effectData[1];
            $player->addEffect(new EffectInstance(Effect::getEffect($id), INT32_MAX, $amplifier, false));
            return;
        }
        $id = $this->getRandomEffect();
        $amplifier = $id == 21 ? 2 : 1;
        $player->addEffect(new EffectInstance(Effect::getEffect($id), INT32_MAX, $amplifier, false));
        $this->effects[$player->getName()] = [$id, $amplifier];
        $player->sendMessage(TextFormat::GREEN . 'You are now a super hero. Look what effect you have received!');
    }

    /**
     * @return int
     */
    public function getRandomEffect(): int
    {
        return self::$randomEffects[array_rand(self::$randomEffects)];
    }

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void
    {
        $game = $event->getGame();

        foreach ($game->getPlayers('online') as $player)
            $this->addEffect($player);
    }
}