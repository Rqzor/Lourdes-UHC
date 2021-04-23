<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class Cutclean
 * @package uhc\scenarios\defaults
 */
class Cutclean extends Scenario
{

    /**
     * Cutclean constructor.
     */
    public function __construct()
    {
        $item = ItemFactory::get(ItemIds::DIAMOND_PICKAXE);
        $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SILK_TOUCH)));
        parent::__construct('Cutclean', 'Iron and gold ore smelt automatically after mining', $item);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $drops = $event->getDrops();
        $xp = $event->getXpDropAmount();

        switch ($block->getItemId()) {
            case BlockIds::IRON_ORE:
                $drops = [ItemFactory::get(ItemIds::IRON_INGOT)];
                $xp = mt_rand(1, 3);
                break;
            case BlockIds::GOLD_ORE:
                $drops = [ItemFactory::get(ItemIds::GOLD_INGOT)];
                $xp = mt_rand(2, 4);
        }
        $event->setDrops($drops);
        $event->setXpDropAmount($xp);
    }
}