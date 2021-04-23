<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class Rodless
 * @package uhc\scenarios\defaults
 */
class Rodless extends Scenario
{

    /**
     * Rodless constructor.
     */
    public function __construct()
    {
        parent::__construct('Rodless', 'Fishing rod cannot be used', ItemFactory::get(ItemIds::FISHING_ROD));
    }

    /**
     * @param CraftItemEvent $event
     */
    public function handleCraft(CraftItemEvent $event): void
    {
        $items = $event->getOutputs();

        foreach ($items as $item) {
            if ($item->getId() == ItemIds::FISHING_ROD)
                $event->setCancelled();
        }
    }
}