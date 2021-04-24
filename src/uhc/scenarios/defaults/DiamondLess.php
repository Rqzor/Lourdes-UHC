<?php


namespace uhc\scenarios\defaults;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class DiamondLess
 * @package uhc\scenarios\defaults
 */
class DiamondLess extends Scenario
{

    /**
     * DiamondLess constructor.
     */
    public function __construct()
    {
        parent::__construct('Diamond Less', 'You can\'t obtain diamond by mining them', ItemFactory::get(ItemIds::DIAMOND), self::PRIORITY_HIGH);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();

        if ($block->getId() == BlockIds::DIAMOND_ORE){
            $event->setDrops([]);
        }
    }

}