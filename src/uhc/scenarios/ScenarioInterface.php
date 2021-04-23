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
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use uhc\event\GameStartEvent;

/**
 * Interface ScenarioInterface
 * @package uhc\scenarios
 */
interface ScenarioInterface
{

    public function handleEnable(): void;

    public function handleDisable(): void;

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void;

    /**
     * @param BlockPlaceEvent $event
     */
    public function handlePlace(BlockPlaceEvent $event): void;

    /**
     * @param CraftItemEvent $event
     */
    public function handleCraft(CraftItemEvent $event): void;

    /**
     * @param EntityDamageByChildEntityEvent $event
     */
    public function handleDamageByChildEntity(EntityDamageByChildEntityEvent $event): void;

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function handleDamageByEntity(EntityDamageByEntityEvent $event): void;

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void;

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void;

    /**
     * @param EntityDeathEvent $event
     */
    public function handleEntityDeath(EntityDeathEvent $event): void;

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void;

    /**
     * @param PlayerMoveEvent $event
     */
    public function handleMove(PlayerMoveEvent $event): void;

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void;

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void;


    /**
     * @param EntitySpawnEvent $event
     */
    public function handleEntitySpawn(EntitySpawnEvent $event): void;

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void;
}