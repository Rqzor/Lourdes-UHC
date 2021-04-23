<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\block\Wood2;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\ItemFactory;
use pocketmine\scheduler\ClosureTask;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;
use uhc\UHCLoader;

/**
 * Class Timber
 * @package uhc\scenarios\defaults
 */
class Timber extends Scenario
{

    /**
     * Timber constructor.
     */
    public function __construct()
    {
        parent::__construct('Timber', 'Mining a log from a tree will mine the entire tree', ItemFactory::get(279));
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        /** @var GamePlayer $player */
        $player = $event->getPlayer();

        if (!$player->isSpectator())
            if ($event->getBlock() instanceof Wood || $event->getBlock() instanceof Wood2)
                $this->breakBlock($player, $event->getBlock());
    }

    /**
     * @param GamePlayer $player
     * @param Block $block
     */
    private function breakBlock(GamePlayer $player, Block $block): void
    {
        $item = null;

        foreach ($block->getAllSides() as $side) {
            if ($side->getId() === $block->getId()) {
                $side->getLevel()->useBreakOn($side, $item, null, true);
                UHCLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player, $side): void {
                    $this->breakBlock($player, $side);
                }), 1);
            } else {
                foreach ($side->getAllSides() as $adjacentSide) {
                    if ($adjacentSide->getId() === $block->getId()) {
                        $adjacentSide->getLevel()->useBreakOn($adjacentSide, $item, null, true);
                        UHCLoader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($player, $adjacentSide): void {
                            $this->breakBlock($player, $adjacentSide);
                        }), 1);
                    }
                }
            }
        }
    }
}