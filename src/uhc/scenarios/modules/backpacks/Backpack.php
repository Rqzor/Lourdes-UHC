<?php

declare(strict_types=1);

namespace uhc\scenarios\modules\backpacks;

use muqsit\invmenu\InvMenu;
use pocketmine\Player;
use uhc\scenarios\defaults\Backpacks;

/**
 * Class Backpack
 * @package uhc\scenarios\modules\backpacks
 */
class Backpack
{

    /** @var BackpackInventory */
    private $chest;

    /** @var string */
    private $nameChest = "Team backpack";

    /**
     * Backpack constructor.
     */
    public function __construct()
    {
        $this->chest = InvMenu::create(Backpacks::MENU_TYPE_BACKPACK_CHEST);
        $this->chest->setName($this->nameChest);
    }

    /**
     * @param Player $player
     */
    public function send(Player $player): void
    {
        $this->chest->send($player);
    }
}