<?php

declare(strict_types=1);

namespace uhc\sessions\types;

use addon\AddonLoader;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\utils\Config;
use uhc\player\GamePlayer;

/**
 * Class PlayerSession
 * @package uhc\sessions\types
 */
class PlayerSession
{

    /** @var string */
    protected $name;
    /** @var int */
    private $eliminations = 0;
    /** @var null|int */
    private $team = null;
    /** @var bool */
    private $spectator = false;
    /** @var bool */
    private $host = false;
    /** @var bool */
    private $scattering = false;
    /** @var null|string */
    private $device = null;
    /** @var null|string */
    private $input = null;
    /** @var bool */
    private $mute = false;
    /** @var bool */
    private $freeze = false;
    /** @var null|array */
    private $lastHit = null;
    /** @var null|array */
    private $doNotDisturb = null;
    /** @var array */
    private $contents = [];
    /** @var null|Position */
    private $lastPosition = null;
    /** @var bool */
    private $modeStaff = false;

    /**
     * PlayerSession constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
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
     * @return int
     */
    public function getEliminations(): int
    {
        return $this->eliminations;
    }

    /**
     * @return int|null
     */
    public function getTeam(): ?int
    {
        return $this->team;
    }

    /**
     * @return bool
     */
    public function isSpectator(): bool
    {
        return $this->spectator;
    }

    /**
     * @return bool
     */
    public function isHost(): bool
    {
        return $this->host;
    }

    public function isAlive(): bool
    {
        return !$this->isHost() && !$this->isSpectator();
    }

    /**
     * @return bool
     */
    public function isScattering(): bool
    {
        return $this->scattering;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return Server::getInstance()->getPlayer($this->getName()) instanceof GamePlayer;
    }

    /**
     * @return string|null
     */
    public function getDevice(): ?string
    {
        return $this->device;
    }

    /**
     * @return string|null
     */
    public function getInput(): ?string
    {
        return $this->input;
    }

    /**
     * @return bool
     */
    public function isMute(): bool
    {
        return $this->mute;
    }

    /**
     * @return bool
     */
    public function isFreeze(): bool
    {
        return $this->freeze;
    }

    /**
     * @return array
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @return array|null
     */
    public function getLastHit(): ?array
    {
        return $this->lastHit;
    }

    /**
     * @return array|null
     */
    public function getDoNotDisturb(): ?array
    {
        return $this->doNotDisturb;
    }

    /**
     * @return Position|null
     */
    public function getLastPosition(): ?Position
    {
        return $this->lastPosition;
    }

    /**
     * @return bool
     */
    public function isModeStaff(): bool
    {
        return $this->modeStaff;
    }

    /**
     * @return GamePlayer
     */
    public function getInstance(): GamePlayer
    {
        $player = Server::getInstance()->getPlayer($this->getName());
        assert($player instanceof GamePlayer);
        return $player;
    }

    public function addEliminations(): void
    {
        $this->eliminations++;
    }

    /**
     * @param int $eliminations
     */
    public function setEliminations(int $eliminations): void
    {
        $this->eliminations = $eliminations;
    }

    /**
     * @param int|null $team
     */
    public function setTeam(?int $team): void
    {
        $this->team = $team;
    }

    /**
     * @param bool $spectator
     */
    public function setSpectator(bool $spectator): void
    {
        $this->spectator = $spectator;
    }

    /**
     * @param bool $host
     */
    public function setHost(bool $host): void
    {
        $this->host = $host;
    }

    /**
     * @param bool $scattering
     */
    public function setScattering(bool $scattering): void
    {
        $this->scattering = $scattering;
    }

    /**
     * @param string|null $device
     */
    public function setDevice(?string $device): void
    {
        $this->device = $device;
    }

    /**
     * @param string|null $input
     */
    public function setInput(?string $input): void
    {
        $this->input = $input;
    }

    /**
     * @param bool $mute
     */
    public function setMute(bool $mute): void
    {
        $this->mute = $mute;
    }

    /**
     * @param bool $freeze
     */
    public function setFreeze(bool $freeze): void
    {
        $this->freeze = $freeze;
    }

    /**
     * @param array $armorContents
     * @param array $contents
     */
    public function setContents(array $armorContents, array $contents): void
    {
        $this->contents = [
            'armorContents' => array_map(function (Item $item) {
                return $item->jsonSerialize();
            }, $armorContents),
            'contents' => array_map(function (Item $item) {
                return $item->jsonSerialize();
            }, $contents)
        ];
    }

    /**
     * @param array|null $lastHit
     */
    public function setLastHit(?array $lastHit): void
    {
        $this->lastHit = $lastHit;
    }

    /**
     * @param array|null $doNotDisturb
     */
    public function setDoNotDisturb(?array $doNotDisturb): void
    {
        $this->doNotDisturb = $doNotDisturb;
    }

    /**
     * @param Position|null $lastPosition
     */
    public function setLastPosition(?Position $lastPosition): void
    {
        $this->lastPosition = $lastPosition;
    }

    /**
     * @param bool $modeStaff
     */
    public function setModeStaff(bool $modeStaff): void
    {
        $this->modeStaff = $modeStaff;
    }
}