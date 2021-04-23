<?php

declare(strict_types=1);

namespace uhc\scenarios\modules\timebomb;

use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Chest;

/**
 * Class TimebombTile
 * @package uhc\scenarios\modules\timebomb
 */
class TimebombTile extends Chest
{

    /** @var string|null */
    private $owner;

    /**
     * TimebombTile constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param string|null $owner
     */
    public function __construct(Level $level, CompoundTag $nbt, string $owner = null)
    {
        $this->owner = $owner;
        parent::__construct($level, $nbt);
    }

    /**
     * @return string|null
     */
    public function getOwner(): ?string
    {
        return $this->owner;
    }
}