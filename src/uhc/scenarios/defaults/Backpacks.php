<?php

declare(strict_types=1);

namespace uhc\scenarios\defaults;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use uhc\scenarios\modules\backpacks\Backpack;
use uhc\scenarios\modules\backpacks\BackpackCommand;
use uhc\scenarios\modules\backpacks\BackpackMenuMetadata;
use uhc\scenarios\Scenario;
use uhc\sessions\types\TeamSession;
use uhc\UHCLoader;

/**
 * Class Backpacks
 * @package uhc\scenarios\defaults
 */
class Backpacks extends Scenario
{

    /** @var string */
    public const MENU_TYPE_BACKPACK_CHEST = 'backpack:weekom';

    /** @var array */
    public $backpacks = [];

    /**
     * Backpacks constructor.
     */
    public function __construct()
    {
        $item = ItemFactory::get(ItemIds::CHEST);
        parent::__construct('Backpacks', 'Access an extra inventory to store your items in', $item);

        # Register inventory
        $this->registerInventory();
    }

    private function registerInventory(): void
    {
        if (InvMenuHandler::getMenuType(self::MENU_TYPE_BACKPACK_CHEST) === null) {
            InvMenuHandler::registerMenuType(new BackpackMenuMetadata(self::MENU_TYPE_BACKPACK_CHEST, 27, WindowTypes::CONTAINER, BlockFactory::get(BlockIds::CHEST)));
        }
    }

    /**
     * @param TeamSession $team
     * @return bool
     */
    public function hasBackpack(TeamSession $team): bool
    {
        return isset($this->backpacks[$team->getTeamInt()]);
    }

    /**
     * @param TeamSession $team
     * @return Backpack
     */
    public function getBackpack(TeamSession $team): Backpack
    {
        if (!$this->hasBackpack($team))
            $this->createBackpack($team);
        return $this->backpacks[$team->getTeamInt()];
    }

    /**
     * @param TeamSession $team
     */
    public function createBackpack(TeamSession $team): void
    {
        if (isset($this->backpacks[$team->getTeamInt()]))
            return;
        $this->backpacks[$team->getTeamInt()] = new Backpack();
    }

    public function handleEnable(): void
    {
        $command = new BackpackCommand();
        UHCLoader::getInstance()->getServer()->getCommandMap()->register($command->getName(), $command);
        $this->refreshCommands();
    }

    public function handleDisable(): void
    {
        $command = new BackpackCommand();
        UHCLoader::getInstance()->getServer()->getCommandMap()->unregister($command);
        $this->refreshCommands();
    }
}