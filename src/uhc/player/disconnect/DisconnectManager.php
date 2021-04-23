<?php

declare(strict_types=1);

namespace uhc\player\disconnect;

use pocketmine\entity\Entity;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;
use uhc\UHCLoader;

/**
 * Class DisconnectManager
 * @package uhc\player\disconnect
 */
class DisconnectManager
{

    /** @var DisconnectMob[] */
    private $disconnected = [];

    /**
     * @return DisconnectMob[]
     */
    public function getDisconnectMobs(): array
    {
        return $this->disconnected;
    }

    /**
     * @param string $name
     * @return DisconnectMob|null
     */
    public function getDisconnected(string $name): ?DisconnectMob
    {
        return $this->disconnected[strtolower($name)] ?? null;
    }

    /**
     * @param GamePlayer|string $player
     * @return bool
     */
    public function isDisconnected($player): bool
    {
        return isset($this->disconnected[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
    }

    /**
     * @param GamePlayer $player
     */
    public function addDisconnected(GamePlayer $player): void
    {
        $nbt = Entity::createBaseNBT($player->asPosition());
        $mob = new DisconnectMob($player->getLevel(), $nbt, $player->getName());
        $mob->setNameTag(TextFormat::WHITE . $player->getName() . TextFormat::RED . ' (AFK)');
        $mob->setNameTagVisible(true);
        $mob->setNameTagAlwaysVisible(true);
        $mob->setHealth($player->getHealth());
        $mob->setMaxHealth($player->getMaxHealth());
        $mob->setContents($player->getInventory()->getContents());
        $mob->setArmorContents($player->getArmorInventory()->getContents());
        $mob->getArmorInventory()->setContents($player->getArmorInventory()->getContents());
        $player->getLevelNonNull()->addEntity($mob);
        $mob->spawnToAll();

        $this->disconnected[strtolower($player->getName())] = $mob;
    }

    /**
     * @param GamePlayer|string $player
     */
    public function removeDisconnected($player): void
    {
        if ($this->isDisconnected($player))
            unset($this->disconnected[$player instanceof GamePlayer ? $player->getLowerCaseName() : strtolower($player)]);
    }

    /**
     * @param GamePlayer $player
     */
    public function join(GamePlayer $player): void
    {
        if ($this->isDisconnected($player)) {
            $this->remove($player);
            $this->removeDisconnected($player);
        }
    }

    /**
     * @param GamePlayer $player
     */
    private function remove(GamePlayer $player): void
    {
        if ($this->isDisconnected($player)) {
	        $entity = $this->getDisconnected($player->getName());
            $player->setHealth($entity->getHealth());

            try {
                $player->teleport($entity->asLocation());
            } catch (AssumptionFailedError $exception) {
                // ignore
            }
            $entity->setContents([]);
            $entity->setArmorContents([]);

            if (!$entity->isClosed())
                $entity->flagForDespawn();
            else
                UHCLoader::getInstance()->getLogger()->warning('Entity for ' . $player->getName(true) . ' was closed before it could be despawned');
        }
    }
}