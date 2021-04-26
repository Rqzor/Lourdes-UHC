<?php

declare(strict_types=1);

namespace uhc\player\disconnect;

use addon\AddonLoader;
use pocketmine\entity\Zombie;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use uhc\game\Game;
use uhc\sessions\types\PlayerSession;
use uhc\sessions\types\TeamSession;
use uhc\UHCLoader;

/**
 * Class DisconnectMob
 * @package uhc\player\disconnect
 */
class DisconnectMob extends Zombie
{

    /** @var string */
    private $name;
    /** @var null|string */
    private $fakeName;
    /** @var Item[] */
    private $contents = [];
    /** @var Item[] */
    private $armorContents = [];

    /**
     * DisconnectMob constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param string $name
     * @param string|null $fakeName
     */
    public function __construct(Level $level, CompoundTag $nbt, string $name = 'afk', string $fakeName = null)
    {
        $this->name = $name;
        $this->fakeName = $fakeName;
        parent::__construct($level, $nbt);
    }

    protected function initEntity(): void
    {
        if ($this->name == 'afk')
            $this->flagForDespawn();
        parent::initEntity();
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return UHCLoader::getInstance()->getGame();
    }

    /**
     * @return PlayerSession|null
     */
    public function getData(): ?PlayerSession
    {
        return $this->getGame()->getSessions()->getPlayer($this->getName(false));
    }

    /**
     * @return TeamSession|null
     */
    public function getTeam(): ?TeamSession
    {
        return $this->getData()->getTeam() != null ? $this->getGame()->getSessions()->getTeam($this->getData()->getTeam()) : null;
    }

    /**
     * @param bool $fakeName
     * @return string
     */
    public function getName(bool $fakeName = false): string
    {
        $c = new Config(AddonLoader::getInstance()->getDataFolder() . 'nicks.yml', Config::YAML);

        if ($fakeName && $c->exists($this->name))
            return (string) $c->get($this->name);
        return $this->name;
    }

    /**
     * @return Item[]
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @return Item[]
     */
    public function getArmorContents(): array
    {
        return $this->armorContents;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param Item[] $contents
     */
    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * @param Item[] $armorContents
     */
    public function setArmorContents(array $armorContents): void
    {
        $this->armorContents = $armorContents;
    }

    /**
     * @return array
     */
    public function getDrops(): array
    {
        return [];
    }

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick): bool
    {
        if ($currentTick % 20 === 0)
            $this->setNameTag(($this->getGame()->isTeams() ? ($this->getTeam() != null ? $this->getTeam()->getFormat() . ' ' : '') : '') . TextFormat::RED . $this->getName(true) . TextFormat::WHITE . ' ' . round($this->getHealth() / 2, 2) . TextFormat::RED . '❤' . PHP_EOL . TextFormat::YELLOW . $this->getData()->getDevice() . TextFormat::GRAY . ' | ' . TextFormat::YELLOW . $this->getData()->getInput());

        return parent::onUpdate($currentTick);
    }

    public function close(): void
    {
        if ($this->closed)
            UHCLoader::getInstance()->getLogger()->warning('Attempted to close a closed disconnected player');
        parent::close();
    }
}
