<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use addon\items\Golden;
use Exception;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\tile\Chest;
use pocketmine\utils\TextFormat;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\scenarios\modules\timebomb\TimebombTask;
use uhc\scenarios\modules\timebomb\TimebombTile;
use uhc\scenarios\Scenario;
use uhc\UHCLoader;

/**
 * Class Timebomb
 * @package uhc\scenarios\defaults
 */
class Timebomb extends Scenario
{

    /**
     * Timebomb constructor.
     */
    public function __construct()
    {
        parent::__construct('TimeBomb', "Upon a player's death, a chest will spawn with the player's items along with a golden head", ItemFactory::get(ItemIds::CHEST_MINECART));
    }

    /**
     * @param Living $living
     * @throws Exception
     */
    private function spawn(Living $living): void
    {
        $items = $living->getArmorInventory()->getContents();

        if ($living instanceof GamePlayer) {
            $inventoryContents = $living->getInventory()->getContents();
            $owner = ($lastHit = $living->getData()->getLastHit()) != null && (time() - $lastHit['time']) <= 30 ? $lastHit['player'] : null;
        } else if ($living instanceof DisconnectMob) {
            $inventoryContents = $living->getContents();
            $owner = ($lastHit = $living->getData()->getLastHit()) != null && (time() - $lastHit['time']) <= 30 ? $lastHit['player'] : null;
        } else {
            return;
        }

        foreach ($inventoryContents as $item)
            $items[] = $item;
        $items[] = Golden::create();
        $face = 4;
        $vector = $living->asVector3()->floor();
        $firstBlock = BlockFactory::get(BlockIds::CHEST, $face);
        $firstTile = new TimebombTile($living->getLevelNonNull(), Chest::createNBT($vector, $face), $owner);/*Tile::createTile(TimebombTile::class, $living->getLevelNonNull(), Chest::createNBT($vector, $face), $owner);*/
        $living->getLevelNonNull()->setBlock($firstTile->asVector3(), $firstBlock);
        $blockFirst = $living->getLevelNonNull()->getBlock($firstTile->asVector3()->add(0, 1, 0));
        $living->getLevelNonNull()->setBlock($blockFirst->asVector3(), Block::get(BlockIds::AIR));

        $secondBlock = BlockFactory::get(Block::CHEST, $face);
        $secondTile = new TimebombTile($living->getLevelNonNull(), Chest::createNBT($vector->subtract(0, 0, $living->getZ() < 0 ? 1 : -1), $face), $owner);/*Tile::createTile(Tile::CHEST, $living->getLevelNonNull(), Chest::createNBT($vector->subtract(0, 0, $living->getZ() < 0 ? 1 : -1), $face));*/

        $living->getLevelNonNull()->setBlock($secondTile->asVector3(), $secondBlock);
        $blockSecond = $living->getLevelNonNull()->getBlock($secondTile->asVector3()->add(0, 1, 0));
        $living->getLevelNonNull()->setBlock($blockSecond->asVector3(), Block::get(BlockIds::AIR));
        $name = $living->getName();
        $name = $name . "'s";

        if ($firstTile instanceof TimebombTile && $secondTile instanceof TimebombTile) {
            $corpseName = TextFormat::YELLOW . $name . " Corpse";
            $firstTile->setName($corpseName);
            $secondTile->setName($corpseName);

            $firstTile->pairWith($secondTile);
            $secondTile->pairWith($firstTile);

            $firstTile->getInventory()->setContents($items);

            UHCLoader::getInstance()->getScheduler()->scheduleRepeatingTask(new TimebombTask($name, $firstTile->asPosition()), 20);
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $tile = $player->getLevelNonNull()->getTile($block);

        if ($tile instanceof TimebombTile)
            if ($tile->getOwner() != null && $tile->getOwner() != $player->getName())
                $event->setCancelled();
    }

    /**
     * @param PlayerDeathEvent $event
     * @throws Exception
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned() && $player->getData()->isAlive()) {
            $this->spawn($player);
            $event->setDrops([]);
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $tile = $player->getLevel()->getTile($block);

        if ($player instanceof GamePlayer && $tile instanceof TimebombTile) {
            $game = $player->getGame();

            if (!$game->isTeams()) {
                if ($tile->getOwner() != null && $tile->getOwner() != $player->getName())
                    $event->setCancelled();
            }
        }
    }
}