<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class BloodDiamonds
 * @package uhc\commands\defaults
 */
class BloodDiamonds extends Scenario
{

    /**
     * BloodDiamonds constructor.
     */
    public function __construct()
    {
        parent::__construct('Blood Diamonds', 'Every time a player mines a diamond, the player takes half a heart of damage', ItemFactory::get(ItemIds::DIAMOND_ORE));
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();

        if ($block->getId() == BlockIds::DIAMOND_ORE)
            $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_MAGIC, 1));
    }
}