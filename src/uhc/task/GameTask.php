<?php

declare(strict_types=1);

namespace uhc\task;

use pocketmine\scheduler\Task;
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
        }

        $this->currentTick++;
    }
}