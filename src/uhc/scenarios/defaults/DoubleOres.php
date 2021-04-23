<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class DoubleOres
 * @package uhc\scenarios\defaults
 */
class DoubleOres extends Scenario
{

    /**
     * DoubleOres constructor.
     */
    public function __construct()
    {
        parent::__construct('Double Ores', 'All ores mined are multiplied by 2', ItemFactory::get(ItemIds::GOLD_INGOT), self::PRIORITY_HIGH);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $xp = $event->getXpDropAmount();

        if (in_array($event->getBlock()->getId(), [BlockIds::COAL_ORE, BlockIds::IRON_ORE, BlockIds::LAPIS_ORE, BlockIds::GOLD_ORE, BlockIds::REDSTONE_ORE, BlockIds::DIAMOND_ORE])) {
            $drops = $event->getDrops();

            foreach ($drops as $drop) {
                $drop->setCount($drop->getCount() * 2);
                $xp = mt_rand(2, 4);
            }
            $event->setDrops($drops);
            $event->setXpDropAmount($xp);
        }
    }
}