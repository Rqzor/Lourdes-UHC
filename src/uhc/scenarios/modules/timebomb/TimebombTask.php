<?php

namespace uhc\scenarios\modules\timebomb;

use pocketmine\level\Explosion;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use uhc\UHCLoader;

/**
 * Class TimebombTask
 * @package uhc\scenarios\modules\timebomb
 */
class TimebombTask extends Task
{

    /** @var string */
    private $name;

    /** @var Position */
    private $position;

    /** @var int */
    private $countdown = 30;

    /** @var FloatingTextParticle */
    private $particle;

    /**
     * TimebombTask constructor.
     * @param string $name
     * @param Position $position
     */
    public function __construct(string $name, Position $position)
    {
        $this->name = $name;
        $this->position = $position;
        $this->particle = new FloatingTextParticle($position->asVector3()->add(0, 1), TextFormat::LIGHT_PURPLE . $this->countdown, TextFormat::LIGHT_PURPLE . $this->name . TextFormat::WHITE . ' corpse will explode in: ');
        $this->sendParticle();
    }

    public function explode(): void
    {
        $explosion = new Explosion($this->position, 5);
        $explosion->explodeA();
        $explosion->explodeB();
    }

    private function sendParticle(): void
    {
        $this->position->getLevelNonNull()->addParticle($this->particle);
    }

    private function updateParticle(): void
    {
        $this->particle->setText(TextFormat::LIGHT_PURPLE . $this->countdown);
        $this->sendParticle();
    }

    private function removeParticle(): void
    {
        $this->particle->setInvisible();
        $this->sendParticle();
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick): void
    {
        if(--$this->countdown <= 0) {
            $this->explode();
            $this->removeParticle();
            $this->getHandler()->cancel();
        } else {
            $game = UHCLoader::getInstance()->getGame();
            $this->updateParticle();

            if ($game->getState() !== 3) {
                $this->removeParticle();
                $this->getHandler()->cancel();
            }
        }
    }
}