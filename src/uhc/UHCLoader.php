<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use ReflectionException;
use uhc\game\Game;
use uhc\player\disconnect\DisconnectMob;
use uhc\scenarios\modules\timebomb\TimebombTile;
use uhc\task\GameTask;

/**
 * Class UHCLoader
 * @package uhc
 */
class UHCLoader extends PluginBase
{

    /** @var self */
    private static $instance;
    /** @var Game */
    private $game;

    public function onLoad()
    {
        # Register instance for used in static function
        self::$instance = $this;
    }

    public function onEnable()
    {
        # Create config
        $this->saveDefaultConfig();

        # Register listener
        $this->getServer()->getPluginManager()->registerEvents(new UHCListener(), $this);

        # Register tile
        try {
            Tile::registerTile(TimebombTile::class);
        } catch (ReflectionException $e) {
            $this->getLogger()->alert($e->getMessage());
        }

        # Register entities
        Entity::registerEntity(DisconnectMob::class, true);

        # Register game in variable
        $this->game = new Game($this);

        # Register and running main task
        $this->getScheduler()->scheduleRepeatingTask(new GameTask($this), 1);
    }

    /**
     * @return UHCLoader
     */
    public static function getInstance(): UHCLoader
    {
        return self::$instance;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }
}