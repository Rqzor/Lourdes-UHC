<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\scenarios\modules\noclean\NoCleanTask;
use uhc\scenarios\Scenario;
use uhc\UHCLoader;

/**
 * Class NoClean
 * @package uhc\scenarios\defaults
 */
class NoClean extends Scenario
{

    /** @var array */
    public $noclean = [];

    /**
     * NoClean constructor.
     */
    public function __construct()
    {
        parent::__construct('No Clean', 'When you kill a player, you will receive 15 seconds where they can not hurt you', Item::get(ItemIds::GOLDEN_APPLE));
    }

    /**
     * @param GamePlayer $player
     */
    private function createNoClean(GamePlayer $player): void
    {
        $this->addTask($player, new NoCleanTask($player, $this));
    }

    /**
     * @param GamePlayer $player
     * @param $task
     */
    private function addTask(GamePlayer $player, $task): void
    {
        $this->noclean[strtolower($player->getName())] = UHCLoader::getInstance()->getScheduler()->scheduleRepeatingTask($task, 20);
    }

    /**
     * @param string $player
     * @return bool
     */
    private function hasTask(string $player): bool
    {
        return isset($this->noclean[$player]);
    }

    /**
     * @param string $player
     * @return mixed|null
     */
    private function getTask(string $player)
    {
        return $this->noclean[$player] ?? null;
    }

    /**
     * @param string $player
     */
    private function cancelTask(string $player): void
    {
        if ($this->hasTask($player)) {
            $task = $this->getTask($player);
            UHCLoader::getInstance()->getScheduler()->cancelTask($task->getTaskId());
            unset($this->noclean[$player]);
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        if (!$event instanceof EntityDamageByEntityEvent)
            return;
        $victim = $event->getEntity();
        $damager = $event->getDamager();

        if ($victim instanceof GamePlayer && $victim->isSpawned() || $victim instanceof DisconnectMob && $victim->getData() != null)
            if ($this->getTask(strtolower($victim->getName())))
                $event->setCancelled();

        if ($damager instanceof GamePlayer && $damager->isSpawned() && $this->hasTask(strtolower($damager->getName())) && !$event->isCancelled()) {
            $this->cancelTask(strtolower($damager->getName()));
            $damager->sendMessage(TextFormat::RED . 'You have lost your invulnerability!');
        }
    }

    /**
     * @param EntityDeathEvent $event
     */
    public function handleEntityDeath(EntityDeathEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof DisconnectMob)
            return;

        if ($entity->getData() == null)
            return;
        $cause = $entity->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();

            if ($damager instanceof GamePlayer && $damager->isSpawned() && !$damager->isInPractice()) {
                $this->createNoClean($damager);
                $damager->sendMessage(TextFormat::GREEN . 'You have NoClean for 15 seconds');
            }
        } else {
            if ($cause instanceof EntityDamageEvent) {
                if (($last = $entity->getData()->getLastHit()) != null && (time() - $last['time']) <= 8) {
                    $damager = $entity->getGame()->getSessions()->getPlayer($last['player']);

                    if ($damager->isOnline()) {
                        $this->createNoClean($damager->getInstance());
                        $damager->getInstance()->sendMessage(TextFormat::GREEN . 'You have NoClean for 15 seconds');
                    }
                }
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned() && !$player->isInPractice()) {
            $cause = $player->getLastDamageCause();

            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();

                if ($damager instanceof GamePlayer && $damager->isSpawned() && !$damager->isInPractice()) {
                    $this->createNoClean($damager);
                    $damager->sendMessage(TextFormat::GREEN . 'You have NoClean for 15 seconds');
                }
            } else {
                if ($cause instanceof EntityDamageEvent) {
                    if (($last = $player->getData()->getLastHit()) != null && (time() - $last['time']) <= 8) {
                        $damager = $player->getGame()->getSessions()->getPlayer($last['player']);

                        if ($damager->isOnline()) {
                            $this->createNoClean($damager->getInstance());
                            $damager->getInstance()->sendMessage(TextFormat::GREEN . 'You have NoClean for 15 seconds');
                        }
                    }
                }
            }
        }
    }
}