<?php

declare(strict_types=1);

namespace uhc\border;

use pocketmine\scheduler\Task;

/**
 * Class BorderTask
 * @package uhc\border
 */
class BorderTask extends Task
{

    /** @var BorderManager */
    private $border;
    /** @var int */
    private $iterator = 0;

    public function __construct(BorderManager $border)
    {
        $this->border = $border;
    }

    /**
     * @return BorderManager
     */
    public function getBorder(): BorderManager
    {
        return $this->border;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        $size = $this->getBorder()->getSize();

        switch ($this->iterator) {
            case 0:
                $this->getBorder()->createWall(-$size, $size, $size, $size);
                break;
            case 1:
                $this->getBorder()->createWall(-$size, $size, -$size, -$size);
                break;
            case 2:
                $this->getBorder()->createWall(-$size, -$size, -$size, $size);
                break;
            case 3:
                $this->getBorder()->createWall($size, $size, -$size, $size);
                $this->cancel();
                $this->destroy();
                return;
        }
        $this->iterator++;
    }

    private function cancel(): void
    {
        $this->getBorder()->getGame()->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
    }

    private function destroy(): void
    {
        foreach ($this as $key => $value)
            unset($this->$key);
    }
}