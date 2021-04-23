<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\block\BlockIds;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Location;
use pocketmine\tile\Skull;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;

/**
 * Class DeathPole
 * @package uhc\scenarios\defaults
 */
class DeathPole extends Scenario
{

    /**
     * DeathPole constructor.
     */
    public function __construct()
    {
        parent::__construct('DeathPole', 'When the player dies, a statue is created', ItemFactory::get(ItemIds::SKULL_BLOCK, 3));
    }

    public function handleEntityDeath(EntityDeathEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof DisconnectMob && $entity->getData() != null)
            $this->spawnDeathPole($entity->asLocation());
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned() && !$player->isInPractice()) {
            $this->spawnDeathPole($player->asLocation());
        }
    }

    /**
     * @param Location $location
     */
    private function spawnDeathPole(Location $location): void
    {
        $y = $location->getFloorY();
        $location->y = $y + 1;
        $location->getLevelNonNull()->setBlockIdAt($location->getFloorX(), $location->getFloorY(), $location->getFloorZ(), BlockIds::NETHER_BRICK_FENCE);
        $location->y += 1;
        $block = new \pocketmine\block\Skull(Skull::TYPE_HUMAN);
        $block->setComponents($location->getFloorX(), $location->getFloorY(), $location->getFloorZ());
        $block->setLevel($location->getLevel());
        $block->place(ItemFactory::get(ItemIds::SKULL_BLOCK, Skull::TYPE_HUMAN), $block, $block, 1, $block, null);
    }
}