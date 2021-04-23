<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class Fireless
 * @package uhc\scenarios\defaults
 */
class Fireless extends Scenario
{

    /**
     * Fireless constructor.
     */
    public function __construct()
    {
        parent::__construct('Fireless', 'All types of fire damage are nullified', ItemFactory::get(ItemIds::FLINT_STEEL));
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if (in_array($event->getCause(), [EntityDamageEvent::CAUSE_FIRE, EntityDamageEvent::CAUSE_FIRE_TICK, EntityDamageEvent::CAUSE_LAVA])) {
            $event->setCancelled();
            $entity->extinguish();
        }
    }
}