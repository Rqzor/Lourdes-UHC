<?php

declare(strict_types=1);

namespace uhc\game\utils;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use ReflectionClass;
use ReflectionException;
use uhc\form\FormUtils;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\UHCLoader;

/**
 * Class GameUtils
 * @package uhc\game\utils
 */
final class GameUtils
{

    /** @var string[] */
    private static $damageNames;
    /** @var string[][] */
    private static $items = [
        'waiting' => [
            0 => '276:0:1:§bPractice:§7Use to join arena',
            4 => '339:0:1:§6Game Information:§7Use to see game settings',
            8 => '54:0:1:§eScenarios:§7Use it to see the scenarios'
        ],
        'default' => [
            0 => '364:0:64',
            1 => '334:0:20'
        ],
        'spectator' => [
            0 => '345:0:1:§bRandom player',
            1 => '339:0:1:§ePlayers list'
        ],
        'staff' => [
            0 => '345:0:1:§bRandom player',
            1 => '339:0:1:§ePlayers list',
            2 => '347:0:1:§9Vanish',
            3 => '340:0:1:§5Player Inventory'
        ]
    ];

    /**
     * @return string[]
     * @throws ReflectionException
     */
    public static function getDamageNames(): array
    {
        return self::$damageNames ?? (self::$damageNames = array_flip(array_filter((new ReflectionClass(EntityDamageEvent::class))->getConstants(), function (string $name): bool {
                return stripos($name, 'CAUSE_') !== false;
            }, ARRAY_FILTER_USE_KEY)));
    }

    /**
     * @param string $data
     * @return Item
     */
    private static function convertedItem(string $data): Item
    {
        $data = explode(':', $data);

        $item = Item::get((int) $data[0], (int) $data[1], (int) $data[2]);

        if (isset($data[3]))
            $item->setCustomName($data[3]);

        if (isset($data[4]))
            $item->setLore([$data[4]]);

        return $item;
    }

    /**
     * @param PlayerInteractEvent $event
     * @param GamePlayer $player
     * @param Item $item
     * @param int $state
     */
    public static function getFunctionByItem(PlayerInteractEvent $event, GamePlayer $player, Item $item, int $state = GameState::WAITING): void
    {
        $game = $player->getGame();

        switch ($state) {
            case GameState::WAITING:
                switch ($item->getCustomName()) {
                    case '§bPractice':
                        $event->setCancelled();
                        $game->getPractice()->joinArena($player);
                        break;

                    case '§eScenarios':
                        $event->setCancelled();
                        self::createChestContentScenarios($player);
                        break;

                    case '§6Game Information':
                        $event->setCancelled();
                        FormUtils::createInformationForm($player);
                        break;
                }
                break;

            case GameState::SETUP:
            case GameState::STARTING:
            case GameState::RUNNING:
            case GameState::RESTARTING:
                switch ($item->getCustomName()) {
                    case '§bRandom player':
                        if (!$player->getData()->isAlive()) {
                            $event->setCancelled();
                            self::randomTeleport($player);
                        }
                        break;

                    case '§ePlayers list':
                        if (!$player->getData()->isAlive()) {
                            $event->setCancelled();
                            FormUtils::listPlayers($player);
                        }
                        break;

                    case '§9Vanish':
                        if ($player->getData()->isHost()) {
                            $event->setCancelled();
                            self::setVanish($player);
                        }
                        break;

                    case '§5Player Inventory':
                        if ($player->getData()->isHost()) {
                            $event->setCancelled();
                        }
                        break;
                }
                break;
        }
    }

    /**
     * @param string $type
     * @return array
     */
    public static function getKit(string $type): array
    {
        $items = [];

        if (!isset(self::$items[$type]))
            $type = 'waiting';
        $itemsData = self::$items[$type];

        foreach ($itemsData as $slot => $data) {
            $item = self::convertedItem($data);

            if ($type != 'default') {
                $namedtag = $item->getNamedTag();
                $namedtag->setInt('noDrop', 1);
                $item->setNamedTag($namedtag);
            }

            $items[$slot] = $item;
        }

        return $items;
    }

    /**
     * @param Block $block
     * @param int $radius
     * @return array
     */
    public static function getSameSurrounding(Block $block, int $radius = 2)
    {
        $output = [];
        for ($x = $block->getFloorX() - $radius; $x <= $block->getFloorX() + $radius; $x++) {
            for ($y = $block->getFloorY() - $radius; $y <= $block->getFloorY() + $radius; $y++) {
                for ($z = $block->getFloorZ() - $radius; $z <= $block->getFloorZ() + $radius; $z++) {
                    $current = $block->getLevel()->getBlockAt($x, $y, $z);

                    if ($current->getId() !== $block->getId())
                        continue;

                    if (!isset($output[$current->asPosition()->__toString()]))
                        $output[$current->asPosition()->__toString()] = $current;
                }
            }
        }
        return $output;
    }

    /**
     * @param Player|GamePlayer $player
     * @param Player|GamePlayer $staff
     */
    public static function openInventory(Player $player, Player $staff): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $i = 0;

        foreach ($player->getInventory()->getContents() as $item) {
            $menu->getInventory()->setItem($i, $item);
            $i++;
        }
        $menu->getInventory()->setItem(36, ItemFactory::get(102));
        $menu->getInventory()->setItem(37, ItemFactory::get(102));
        $menu->getInventory()->setItem(38, ItemFactory::get(102));
        $menu->getInventory()->setItem(39, ItemFactory::get(102));
        $menu->getInventory()->setItem(40, ItemFactory::get(102));
        $menu->getInventory()->setItem(41, ItemFactory::get(102));
        $menu->getInventory()->setItem(42, ItemFactory::get(102));
        $menu->getInventory()->setItem(43, ItemFactory::get(102));
        $menu->getInventory()->setItem(44, ItemFactory::get(102));
        $menu->getInventory()->setItem(45, $player->getArmorInventory()->getItem(0));
        $menu->getInventory()->setItem(46, $player->getArmorInventory()->getItem(1));
        $menu->getInventory()->setItem(47, $player->getArmorInventory()->getItem(2));
        $menu->getInventory()->setItem(48, $player->getArmorInventory()->getItem(3));
        $menu->getInventory()->setItem(49, ItemFactory::get(102));
        $menu->getInventory()->setItem(50, ItemFactory::get(102));
        $menu->getInventory()->setItem(51, ItemFactory::get(102));
        $menu->getInventory()->setItem(52, ItemFactory::get(102));
        $menu->getInventory()->setItem(53, ItemFactory::get(102));
        $menu->send($staff, TextFormat::YELLOW . $player->getName(true) . "'s inventory");
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            return $transaction->discard();
        });
    }

    /**
     * @param GamePlayer $player
     */
    public static function createChestContentScenarios(GamePlayer $player): void
    {
        $scenarioManager = $player->getGame()->getScenarios();
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $i = 0;

        foreach ($scenarioManager->getActives() as $scenario) {
            $item = $scenario->getRepresentativeItem();
            $item->setCustomName(TextFormat::GOLD . $scenario->getName());
            $item->setLore([TextFormat::WHITE . $scenario->getDescription()]);
            $menu->getInventory()->setItem($i, $item);
            $i++;
        }
        $menu->send($player, TextFormat::GOLD . 'Scenarios actives');
        $menu->setListener(function (InvMenuTransaction $transaction): InvMenuTransactionResult {
            return $transaction->discard();
        });
    }

    /**
     * @param GamePlayer $player
     */
    private static function randomTeleport(GamePlayer $player): void
    {
        if (count($players = array_values($player->getGame()->getPlayers('alive') + $player->getGame()->getPlayers('offline'))) == 0) {
            $player->sendMessage(TextFormat::RED . 'There are no players');
            return;
        }
        $random = array_rand($players, 1);

        if (!isset($players[$random])) {
            self::randomTeleport($player);
            return;
        }
        /** @var GamePlayer|DisconnectMob $target */
        $target = $players[$random];

        if ($target instanceof DisconnectMob && $target->getData() == null) {
            self::randomTeleport($player);
            return;
        }

        if ($target instanceof GamePlayer && (!$target->isSpawned() || !$target->getData()->isAlive())) {
            self::randomTeleport($player);
            return;
        }
        $player->teleport($target->asPosition());
        $player->sendMessage(TextFormat::GREEN . 'Teleport to ' . $target->getName(true));
    }

    /**
     * @param GamePlayer $player
     * @param bool $force
     */
    public static function setVanish(GamePlayer $player, bool $force = false): void
    {
        $data = UHCLoader::getInstance()->getGame()->getStaffManager()->getData($player);

        if ($force == true) {
            foreach (UHCLoader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                if ($player !== $p) {
                    $p->showPlayer($player);
                }
            }
            return;
        }

        if ($data != null) {
            if (!$data['vanish']) {
                $data['vanish'] = true;
                UHCLoader::getInstance()->getGame()->getStaffManager()->setData($player, $data);
                $player->sendMessage(TextFormat::GREEN . 'The vanish was activated');
            } else {
                $data['vanish'] = false;
                UHCLoader::getInstance()->getGame()->getStaffManager()->setData($player, $data);
                $player->sendMessage(TextFormat::RED . 'The vanish was deactivated');

                foreach (UHCLoader::getInstance()->getServer()->getOnlinePlayers() as $p) {
                    if ($player !== $p) {
                        $p->showPlayer($player);
                    }
                }
            }
        }
    }

    /**
     * @param GamePlayer $player
     * @param string $soundName
     * @param float|int $volume
     * @param float|int $pitch
     */
    public static function addSound(GamePlayer $player, string $soundName, float $volume = 1, float $pitch = 1): void
    {
        $pk = new PlaySoundPacket();
        $pk->soundName = $soundName;
        $pk->x = $player->x;
        $pk->y = $player->y;
        $pk->z = $player->z;
        $pk->volume = $volume;
        $pk->pitch = $pitch;
        $player->dataPacket($pk);
    }

    /**
     * @param GamePlayer $player
     */
    public static function sendArrowDingSound(GamePlayer $player): void
    {
        $packet = new LevelEventPacket();
        $packet->evid = LevelEventPacket::EVENT_SOUND_ORB;
        $packet->data = 1;
        $packet->position = $player->asVector3();
        $player->batchDataPacket($packet);
    }

}