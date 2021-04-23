<?php

declare(strict_types=1);

namespace uhc\practice;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;

/**
 * Class PracticeListener
 * @package uhc\practice
 */
class PracticeListener implements Listener
{

    /** @var PracticeManager */
    private $manager;

    /**
     * PracticeListener constructor.
     * @param PracticeManager $manager
     */
    public function __construct(PracticeManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return PracticeManager
     */
    private function getManager(): PracticeManager
    {
        return $this->manager;
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();

        if ($player instanceof GamePlayer && $player->isSpawned() && $player->isInPractice()) {
            $health = $player->getHealth();
            $final = $event->getFinalDamage();

            if (($health - $final) <= 0) {
                $event->setCancelled();
                $player->sendTip(TextFormat::RED . TextFormat::BOLD . 'You died!');
                $this->getManager()->joinArena($player, false);

                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();

                    if ($damager instanceof GamePlayer && $damager->isInPractice()) {
                        $this->getManager()->addKill($damager);
                        $damager->setHealth(20);
                        $this->getManager()->broadcast(TextFormat::WHITE . $player->getName(true) . TextFormat::GRAY . ' [' . $this->getManager()->getKills($player) . '] ' . TextFormat::RED . 'was slain by ' . TextFormat::WHITE . $damager->getName(true) . TextFormat::GRAY . ' [' . $this->getManager()->getKills($damager) . ']');
                    }
                } else {
                    $this->getManager()->broadcast(TextFormat::WHITE . $player->getName(true) . TextFormat::GRAY . ' [' . $this->getManager()->getKills($player) . '] ' . TextFormat::RED . 'died');
                }
            }
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     */
    public function handleDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned() && $player->isInPractice())
            $event->setCancelled();
    }
}