<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\scenarios\Scenario;

/**
 * Class NoFall
 * @package uhc\scenarios\defaults
 */
class NoFall extends Scenario
{

    /**
     * NoFall constructor.
     */
    public function __construct()
    {
        $item = ItemFactory::get(ItemIds::DIAMOND_BOOTS);
        $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::FEATHER_FALLING), 2));
        parent::__construct('No Fall', 'All types of fall damage are nullified', $item);
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        if ($event->getCause() == EntityDamageEvent::CAUSE_FALL)
            $event->setCancelled();
    }
}