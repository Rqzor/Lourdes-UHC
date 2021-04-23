<?php

declare(strict_types=1);

namespace uhc\game;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use ReflectionException;
use uhc\event\player\PlayerAddHostEvent;
use uhc\event\player\PlayerRemoveHostEvent;
use uhc\game\utils\GameUtils;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;

/**
 * Class StaffListener
 * @package uhc\game
 */
class StaffListener implements Listener
{

    /** @var array */
    private $countdown = [];
    /** @var array */
    private $data = [];

    /**
     * @param GamePlayer $player
     * @return array|null
     */
    public function getData(GamePlayer $player): ?array
    {
        return $this->data[$player->getName(false)] ?? null;
    }

    /**
     * @param GamePlayer $player
     * @param array $data
     */
    public function setData(GamePlayer $player, array $data): void
    {
        $this->data[$player->getName(false)] = $data;
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            if (in_array($block->getId(), [BlockIds::GOLD_ORE, BlockIds::DIAMOND_ORE])) {
                if (!isset($player->recentBlocks[$block->asPosition()->__toString()])) {
                    $surrounding = GameUtils::getSameSurrounding($block);

                    foreach ($surrounding as $current)
                        $player->recentBlocks[$current->asPosition()->__toString()] = true;
                    $count = count($surrounding);

                    foreach ($player->getGame()->getPlayers('host') as $host) {
                        if ($host->getData()->isModeStaff())
                            $host->sendMessage(TextFormat::GOLD . '[MOD] ' . TextFormat::GREEN . $player->getName(true) . TextFormat::GRAY . ' mined ' . $count . 'x of ' . TextFormat::GRAY . $block->getName());
                    }
                } else
                    unset($player->recentBlocks[$block->asPosition()->__toString()]);
            }

            if ($player->getData()->isModeStaff()) {
                $item = $player->getInventory()->getItemInHand();

                if ($item->getNamedTag()->hasTag('noDrop')) {
                    $event->setCancelled();
                }
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof GamePlayer && $entity->isSpawned() && !$entity->isInPractice() || $entity instanceof DisconnectMob && $entity->getData() != null) {
            if ($entity->getData()->isHost())
                $event->setCancelled();

            if (!$event->isCancelled() && $event->getFinalDamage() > 0) {
                if (isset($this->countdown[$entity->getName(false)])) {
                    $data = $this->countdown[$entity->getName(false)];

                    if (time() - $data < 7)
                        return;
                }

                try {
                    $causes = GameUtils::getDamageNames();
                } catch (ReflectionException $exception) {
                    $causes = [$event->getCause() => 'CAUSE_UNKNOWN'];
                }
                $cause = $causes[$event->getCause()];
                $cause = str_replace('_', ' ', $cause);
                $prefix = TextFormat::GOLD . '[DAMAGE] ' . TextFormat::GRAY . $cause;
                $damage = round($event->getFinalDamage() / 2, 2);
                $this->countdown[$entity->getName(false)] = time();

                foreach ($entity->getGame()->getPlayers('host') as $player) {
                    if ($player->getData()->isModeStaff()) {
                        if ($event instanceof EntityDamageByEntityEvent && ($damager = $event->getDamager()) instanceof GamePlayer) {
                            $player->sendMessage(TextFormat::GOLD . '[PVP] ' . TextFormat::GREEN . $entity->getName(true) . TextFormat::RED . ' is fighting with ' . TextFormat::GREEN . $damager->getName(true));
                        } else {
                            $player->sendMessage($prefix . ' ' . TextFormat::GREEN . $entity->getName(true) . TextFormat::GRAY . ' took ' . TextFormat::RED . $damage . TextFormat::GRAY . ' HP of damage');
                        }
                    }
                }
            }

            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();

                if ($damager instanceof GamePlayer && $damager->isSpawned() && $damager->getData()->isModeStaff()) {
                    $item = $damager->getInventory()->getItemInHand();

                    if ($item->getNamedTag()->hasTag('noDrop'))
                        $event->setCancelled();
                    $data = $this->getData($damager);

                    if ($data != null && $data['vanish'])
                        $event->setCancelled();

                    if ($item->getCustomName() == '§5Player Inventory') {
                        if ($entity instanceof GamePlayer)
                            GameUtils::openInventory($entity, $damager);
                    }
                }
            }
        }
    }

    /**
     * @param InventoryPickupItemEvent $event
     */
    public function handlePickupItem(InventoryPickupItemEvent $event): void
    {
        $inventory = $event->getInventory();
        $player = $inventory->getHolder();

        if ($player instanceof GamePlayer && $player->isSpawned() && $player->getData()->isHost()) {
            $data = $this->getData($player);

            if ($data != null && $data['vanish'])
                $event->setCancelled();
        }
    }

    /**
     * @param PlayerAddHostEvent $event
     */
    public function handleAddHost(PlayerAddHostEvent $event): void
    {
        $player = $event->getPlayer();
        $this->data[$player->getName(false)] = [
            'vanish' => false
        ];
    }

    /**
     * @param PlayerRemoveHostEvent $event
     */
    public function handleRemoveHost(PlayerRemoveHostEvent $event): void
    {
        $player = $event->getPlayer();

        if (isset($this->data[$player->getName(false)]))
            unset($this->data[$player->getName(false)]);
    }
}