<?php

declare(strict_types=1);

namespace uhc\scenarios;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use uhc\event\GameStartEvent;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;

/**
 * Class ScenarioListener
 * @package uhc\scenarios
 */
class ScenarioListener implements Listener, ScenarioInterface
{

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handleBreak($event);
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function handlePlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handlePlace($event);
    }


    /**
     * @param CraftItemEvent $event
     */
    public function handleCraft(CraftItemEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handleCraft($event);
    }

    /**
     * @param EntityDamageByChildEntityEvent $event
     */
    public function handleDamageByChildEntity(EntityDamageByChildEntityEvent $event): void
    {
        $player = $event->getEntity();

        if ($player instanceof GamePlayer && $player->isSpawned() || $player instanceof DisconnectMob && $player->getData() != null)
            $player->getGame()->getScenarios()->handleDamageByChildEntity($event);
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function handleDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();

        if ($player instanceof GamePlayer && $player->isSpawned() || $player instanceof DisconnectMob && $player->getData() != null)
            $player->getGame()->getScenarios()->handleDamageByEntity($event);
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();

        if ($player instanceof GamePlayer && $player->isSpawned() || $player instanceof DisconnectMob && $player->getData() != null)
            $player->getGame()->getScenarios()->handleDamage($event);
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handleDeath($event);
    }

    /**
     * @param EntityDeathEvent $event
     */
    public function handleEntityDeath(EntityDeathEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof DisconnectMob && $entity->getData() != null)
            $entity->getGame()->getScenarios()->handleEntityDeath($event);
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handleInteract($event);
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function handleMove(PlayerMoveEvent $event): void
    {
    }

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void
    {
        $game = $event->getGame();
        $game->getScenarios()->handleStart($event);
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handleJoin($event);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned())
            $player->getGame()->getScenarios()->handleQuit($event);
    }

    /**
     * @param EntitySpawnEvent $event
     */
    public function handleEntitySpawn(EntitySpawnEvent $event): void
    {
    }

    public function handleEnable(): void
    {
    }

    public function handleDisable(): void
    {
    }
}