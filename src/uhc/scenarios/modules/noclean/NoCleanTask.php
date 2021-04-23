<?php

declare(strict_types=1);

namespace uhc\scenarios\modules\noclean;

use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use uhc\player\GamePlayer;
use uhc\scenarios\defaults\NoClean;
use uhc\UHCLoader;

/**
 * Class NoCleanTask
 * @package uhc\scenarios\modules\noclean
 */
class NoCleanTask extends Task
{

    /** @var GamePlayer */
    private $player;

    /** @var NoClean */
    private $noClean;

    /** @var int */
    private $time = 15;

    /**
     * NoCleanTask constructor.
     * @param GamePlayer $player
     * @param NoClean $noClean
     */
    public function __construct(GamePlayer $player, NoClean $noClean)
    {
        $this->player = $player;
        $this->noClean = $noClean;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        $this->time--;

        if ($this->time <= 0) {
            $this->player->sendMessage(TextFormat::RED . 'Your invulnerability has worn off');
            unset($this->noClean->noclean[$this->player->getLowerCaseName()]);
            UHCLoader::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}