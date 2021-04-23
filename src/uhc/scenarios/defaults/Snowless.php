<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class Snowless
 * @package uhc\scenarios\defaults
 */
class Snowless extends Scenario
{
	
	/**
     * Snowless constructor.
     */
	public function __construct()
	{
		parent::__construct('Snowless', 'Snowballs cannot be used', ItemFactory::get(ItemIds::SNOWBALL));
	}
	
	/**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($item->getId() == ItemIds::SNOWBALL)
            $event->setCancelled();
    }
}