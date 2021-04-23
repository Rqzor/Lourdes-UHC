<?php

declare(strict_types=1);

namespace uhc\practice;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use uhc\game\Game;
use uhc\game\utils\GameUtils;
use uhc\player\GamePlayer;
use uhc\UHCLoader;

/**
 * Class PracticeManager
 * @package uhc\practice
 */
class PracticeManager
{

    /** @var Game */
    private $game;
    /** @var Level|null */
    private $arena;
    /** @var array */
    private $kills = [];

    /**
     * PracticeManager constructor.
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;

        # Config
        $config = $this->game->getPlugin()->getConfig()->getAll();

        if (isset($config['practice']['arena']) && $config['practice']['arena'] != '') {
            $this->game->getPlugin()->getServer()->loadLevel($config['practice']['arena']);
            $this->arena = $this->game->getPlugin()->getServer()->getLevelByName($config['practice']['arena']);
        } else {
            $this->arena = null;
        }

        # Listener
        $this->game->getPlugin()->getServer()->getPluginManager()->registerEvents(new PracticeListener($this), $this->game->getPlugin());
    }

    /**
     * @return Level|null
     */
    public function getArena(): ?Level
    {
        return $this->arena;
    }

    /**
     * @param GamePlayer $player
     * @return int|null
     */
    public function getKills(GamePlayer $player): ?int
    {
        $name = $player->getName(false);
        return isset($this->kills[$name]) ? $this->kills[$name] : null;
    }

    /**
     * @param Level|null $arena
     */
    public function setArena(?Level $arena): void
    {
        $this->arena = $arena;
        $config = $this->game->getPlugin()->getConfig()->get('practice');
        $config['arena'] = $arena->getFolderName();
        $this->game->getPlugin()->getConfig()->set('practice', $config);
        $this->game->getPlugin()->getConfig()->save();
    }

    /**
     * @param GamePlayer $player
     */
    public function addKill(GamePlayer $player): void
    {
        $this->kills[$player->getName(false)]++;
    }

    /**
     * @param GamePlayer $player
     */
    private function getKit(GamePlayer $player): void
    {
        # Enchants
        $unbreaking = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::UNBREAKING), 3);
        $protection = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION), 2);
        $sharpness = new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 1);

        # Armor
        $helmet = ItemFactory::get(ItemIds::IRON_HELMET);
        $chestplate = ItemFactory::get(ItemIds::IRON_CHESTPLATE);
        $leggings = ItemFactory::get(ItemIds::IRON_LEGGINGS);
        $boots = ItemFactory::get(ItemIds::IRON_BOOTS);

        # Items
        $sword = ItemFactory::get(ItemIds::IRON_SWORD);
        $rod = ItemFactory::get(ItemIds::FISHING_ROD);
        $bow = ItemFactory::get(ItemIds::BOW);
        $gaps = ItemFactory::get(ItemIds::GOLDEN_APPLE, 0, 20);
        $arrows = ItemFactory::get(ItemIds::ARROW, 0, 32);

        # Add enchants (armor)
        $helmet->addEnchantment($protection);
        $helmet->addEnchantment($unbreaking);
        $chestplate->addEnchantment($protection);
        $chestplate->addEnchantment($unbreaking);
        $leggings->addEnchantment($protection);
        $leggings->addEnchantment($unbreaking);
        $boots->addEnchantment($protection);
        $boots->addEnchantment($unbreaking);

        # Add enchants (items)
        $sword->addEnchantment($sharpness);
        $sword->addEnchantment($unbreaking);
        $bow->addEnchantment($unbreaking);

        # Add armor to player
        $player->getArmorInventory()->setHelmet($helmet);
        $player->getArmorInventory()->setChestplate($chestplate);
        $player->getArmorInventory()->setLeggings($leggings);
        $player->getArmorInventory()->setBoots($boots);

        # Add items to player
        $player->getInventory()->setItem(0, $sword);
        $player->getInventory()->setItem(1, $rod);
        $player->getInventory()->setItem(2, $bow);
        $player->getInventory()->setItem(3, $gaps);
        $player->getInventory()->setItem(10, $arrows);
    }

    /**
     * @param GamePlayer $player
     * @param bool $new
     */
    public function joinArena(GamePlayer $player, bool $new = true): void
    {
        if ($this->getArena() == null) {
            $player->sendMessage(TextFormat::RED . 'The practice is not enabled');
            return;
        }

        if (!isset($this->kills[$player->getName(false)]))
            $this->kills[$player->getName(false)] = 0;

        if ($new) {
            $this->broadcast(TextFormat::GREEN . $player->getName(true) . ' joined the practice');
            $player->sendMessage(TextFormat::GREEN . '[P] If you want to quit the practice, you just have to put /practice quit in the chat');
        }
        $player->setInPractice(true);
        $player->teleport($this->getArena()->getSpawnLocation());

        if ($player->getAllowFlight())
            $player->setAllowFlight(true);
        $player->reset();
        $this->getKit($player);
        $player->changeScoreboard();
    }

    /**
     * @param GamePlayer $player
     */
    public function quitArena(GamePlayer $player): void
    {
        $player->setInPractice(false);
        $player->teleport($player->getServer()->getDefaultLevel()->getSpawnLocation());
        $player->reset();
        $player->getInventory()->setContents(GameUtils::getKit('waiting'));
        $player->changeScoreboard();
    }

    /**
     * @param string $message
     */
    public function broadcast(string $message): void
    {
        foreach (UHCLoader::getInstance()->getGame()->getPlayers('practice') as $player)
            $player->sendMessage($message);
    }
}