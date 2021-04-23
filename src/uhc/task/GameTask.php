<?php

declare(strict_types=1);

namespace uhc\task;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;
use uhc\task\utils\GameTaskUtils;
use uhc\UHCLoader;

/**
 * Class GameTask
 * @package uhc\task
 */
class GameTask extends Task
{

    /** @var UHCLoader */
    private $plugin;
    /** @var int */
    private $currentTick = 0;

    /**
     * GameTask constructor.
     * @param UHCLoader $plugin
     */
    public function __construct(UHCLoader $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return UHCLoader
     */
    private function getPlugin(): UHCLoader
    {
        return $this->plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        $oneSecond = GameTaskUtils::secondsToTicks(1);

        if ($this->currentTick % $oneSecond == 0) {
            $this->getPlugin()->getGame()->update();
            $this->getPlugin()->getGame()->getBorder()->check();
            $this->updatePlayers();
            $this->updateDisconnectMobs();
        }

        $this->currentTick++;
    }

    private function updatePlayers(): void
    {
        foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
            if ($player instanceof GamePlayer && $player->isSpawned()) {
                $player->updateScoreboard();
                $player->setNameTag(($player->getData()->isHost() ? TextFormat::YELLOW . '[Host] ' : '') . ($player->getGame()->isTeams() ? ($player->getTeam() != null ? $player->getTeam()->getFormat() . ' ' : '') : '') . TextFormat::RED . $player->getName(true) . TextFormat::WHITE . ' ' . round($player->getHealth() / 2, 2) . TextFormat::RED . '❤' . PHP_EOL . TextFormat::YELLOW . $player->getDeviceOS(true) . TextFormat::GRAY . ' | ' . TextFormat::YELLOW . $player->getInput(true));

                # Staff
                $data = $this->getPlugin()->getGame()->getStaffManager()->getData($player);

                if ($data != null && $data['vanish']) {
                    $players = $this->getPlugin()->getServer()->getOnlinePlayers();
                    array_walk($players, function (Player $p) use ($player) {
                        if ($p instanceof GamePlayer && $p->isSpawned())
                            if (!$p->getData()->isHost())
                                $p->hidePlayer($player);
                            else
                                $p->showPlayer($player);
                    });
                }
            }
        }
    }

    private function updateDisconnectMobs(): void
    {
        foreach ($this->getPlugin()->getGame()->getDisconnectManager()->getDisconnectMobs() as $disconnectMob) {
            $disconnectMob->setNameTag(($this->getPlugin()->getGame()->isTeams() ? ($disconnectMob->getTeam() != null ? $disconnectMob->getTeam()->getFormat() . ' ' : '') : '') . TextFormat::RED . $disconnectMob->getName(true) . TextFormat::WHITE . ' ' . round($disconnectMob->getHealth() / 2, 2) . TextFormat::RED . '❤' . PHP_EOL . TextFormat::YELLOW . $disconnectMob->getData()->getDevice() . TextFormat::GRAY . ' | ' . TextFormat::YELLOW . $disconnectMob->getData()->getInput());
        }
    }
}