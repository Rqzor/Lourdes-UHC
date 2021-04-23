<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\game\utils\GameValues;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;

/**
 * Class DoNotDisturb
 * @package uhc\scenarios\defaults
 */
class DoNotDisturb extends Scenario
{

    /**
     * DoNotDisturb constructor.
     */
    public function __construct()
    {
        parent::__construct('Do Not Disturb', 'When you hit a player, no one can hit you and your opponent for 30 seconds', Item::get(387), self::PRIORITY_HIGH);
    }

    /**
     * @param EntityDamageByChildEntityEvent $event
     */
    public function handleDamageByChildEntity(EntityDamageByChildEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        $cancel = $event->isCancelled();

        if ($entity instanceof GamePlayer && $entity->isSpawned() && $entity->getData()->isAlive() && !$entity->isInPractice() || $entity instanceof DisconnectMob && $entity->getData() != null) {
            if ($damager instanceof GamePlayer && $damager->getData()->isAlive() && !$damager->isInPractice()) {
                if (!$cancel && $entity->getGame()->getState() == GameState::RUNNING && !$entity->getGame()->isTeams()) {
                    $playerData = $entity->getData()->getDoNotDisturb();
                    $damagerData = $damager->getData()->getDoNotDisturb();

                    if (GameValues::$RUNNING < GameValues::$GRACE_PERIOD)
                        $cancel = true;

                    if (!$cancel && $damagerData != null && $damagerData['player'] != $entity->getName() && (time() - $damagerData['time']) <= 20) {
                        $cancel = true;
                        $damager->sendMessage(TextFormat::RED . 'You cannot hit this player. Hit your opponent ' . $damagerData['player']);
                    }

                    if (!$cancel && $playerData != null && $playerData['player'] != $damager->getName() && (time() - $playerData['time']) <= 20) {
                        $cancel = true;
                        $damager->sendMessage(TextFormat::RED . "You can't hit this player");
                    }

                    if (!$cancel) {
                        $entity->getData()->setDoNotDisturb(['player' => $damager->getName(), 'time' => time()]);
                        $damager->getData()->setDoNotDisturb(['player' => $entity->getName(), 'time' => time()]);
                    }
                    $event->setCancelled($cancel);
                }
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function handleDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        $cancel = $event->isCancelled();

        if ($entity instanceof GamePlayer && $entity->isSpawned() && $entity->getData()->isAlive() && !$entity->isInPractice() || $entity instanceof DisconnectMob && $entity->getData() != null) {
            if ($damager instanceof GamePlayer && $damager->getData()->isAlive() && !$damager->isInPractice()) {
                if (!$cancel && $entity->getGame()->getState() == GameState::RUNNING && !$entity->getGame()->isTeams()) {
                    $playerData = $entity->getData()->getDoNotDisturb();
                    $damagerData = $damager->getData()->getDoNotDisturb();

                    if (GameValues::$RUNNING < GameValues::$GRACE_PERIOD)
                        $cancel = true;

                    if (!$cancel && $damagerData != null && $damagerData['player'] != $entity->getName() && (time() - $damagerData['time']) <= 30) {
                        $cancel = true;
                        $damager->sendMessage(TextFormat::RED . 'You cannot hit this player. Hit your opponent ' . $damagerData['player']);
                    }

                    if (!$cancel && $playerData != null && $playerData['player'] != $damager->getName() && (time() - $playerData['time']) <= 30) {
                        $cancel = true;
                        $damager->sendMessage(TextFormat::RED . "You can't hit this player");
                    }

                    if (!$cancel) {
                        $entity->getData()->setDoNotDisturb(['player' => $damager->getName(), 'time' => time()]);
                        $damager->getData()->setDoNotDisturb(['player' => $entity->getName(), 'time' => time()]);
                    }
                    $event->setCancelled($cancel);
                }
            }
        }
    }
}