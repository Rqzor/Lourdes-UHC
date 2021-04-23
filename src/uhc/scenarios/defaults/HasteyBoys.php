<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\scheduler\ClosureTask;
use uhc\scenarios\Scenario;
use uhc\UHCLoader;

/**
 * Class HasteyBoys
 * @package uhc\scenarios\defaults
 */
class HasteyBoys extends Scenario
{

    /**
     * HasteyBoys constructor.
     */
    public function __construct()
    {
        $item = ItemFactory::get(ItemIds::DIAMOND_HOE);
        $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 3));
        $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3));
        parent::__construct('HasteyBoys', 'All tools crafted will be enchanted with efficiency III and unbreaking III', $item);
    }

    /**
     * @param CraftItemEvent $event
     */
    public function handleCraft(CraftItemEvent $event): void
    {
        $items = array_values($event->getOutputs());

        if (count($items) === 1) {
            $item = $items[0];

            if ($item instanceof Pickaxe || $item instanceof Axe || $item instanceof Shovel) {
                $newItem = clone $item;
                $newItem->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 3));
                $newItem->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3));
                UHCLoader::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function (int $currentTick) use ($item, $newItem, $event): void {
                    $index = 0;
                    $inventory = $event->getPlayer()->getCursorInventory();
                    $value = $inventory->getItem($index)->equals($item);

                    if (!$value) {
                        foreach ($event->getPlayer()->getInventory()->getContents(true) as $slot => $inventoryItem) {
                            if ($inventoryItem->equals($item)) {
                                $value = true;
                                $inventory = $event->getPlayer()->getInventory();
                                $index = $slot;
                                break;
                            }
                        }
                    }
                    if ($value) $inventory->setItem($index, $newItem);
                }));
            }
        }
    }
}