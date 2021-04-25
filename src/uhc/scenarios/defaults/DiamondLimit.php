<?php


namespace uhc\scenarios\defaults;


use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;
use pocketmine\utils\TextFormat as TE;

/**
 * Class DiamondLimit
 * @package uhc\scenarios\defaults
 */
class DiamondLimit extends Scenario
{

    /** @var array */
    public $diamondCount = [];

    /**
     * DiamondLimit constructor.
     */
    public function __construct()
    {
        parent::__construct('Diamond Limit', 'You can\'t mine more than 16 diamond', ItemFactory::get(ItemIds::DIAMOND, 0, 16), self::PRIORITY_HIGH);
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer) {
            if ($block->getId() == BlockIds::DIAMOND_ORE) {
                if ($this->canMineDiamond($player)) {
                    $this->addCount($player);
                    $count = $this->getCount($player);
                    $player->sendTip(TE::GREEN . 'Diamond Limit: ' . $count . '/16');
                } else {
                    $event->setDrops([]);
                    $event->setXpDropAmount(0);
                    $player->sendMessage(TE::RED . 'You have reached your limit of diamond !');
                }
            }
        }
    }

    /**
     * @param GamePlayer $player
     */
    public function addCount(GamePlayer $player): void
    {
        $this->diamondCount[$player->getName()] =+ 1;
    }

    /**
     * @param GamePlayer $player
     * @return int
     */
    public function getCount(GamePlayer $player): int
    {
        if (isset($this->diamondCount[$player->getName()])) {
            return $this->diamondCount[$player->getName()];
        }
        $this->diamondCount[$player->getName()] = 0;
        return $this->diamondCount[$player->getName()];
    }

    /**
     * @param GamePlayer $player
     * @return bool
     */
    public function canMineDiamond(GamePlayer $player): bool
    {
        if (!isset($this->diamondCount[$player->getName()])) {
            return true;
        }
        return $this->diamondCount[$player->getName()] < 16;
    }
}