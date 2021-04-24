<?php

declare(strict_types=1);

namespace uhc\scenarios;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use uhc\event\GameStartEvent;
use uhc\game\Game;
use uhc\game\utils\GameState;
use uhc\scenarios\defaults\Backpacks;
use uhc\scenarios\defaults\BloodDiamonds;
use uhc\scenarios\defaults\Bookception;
use uhc\scenarios\defaults\CatEyes;
use uhc\scenarios\defaults\Cutclean;
use uhc\scenarios\defaults\DeathPole;
use uhc\scenarios\defaults\DiamondLess;
use uhc\scenarios\defaults\DiamondLimit;
use uhc\scenarios\defaults\DoNotDisturb;
use uhc\scenarios\defaults\DoubleOres;
use uhc\scenarios\defaults\Fireless;
use uhc\scenarios\defaults\HasteyBoys;
use uhc\scenarios\defaults\NoClean;
use uhc\scenarios\defaults\NoFall;
use uhc\scenarios\defaults\Rodless;
use uhc\scenarios\defaults\Snowless;
use uhc\scenarios\defaults\SuperHeros;
use uhc\scenarios\defaults\Timber;
use uhc\scenarios\defaults\Timebomb;

/**
 * Class ScenarioManager
 * @package uhc\scenarios
 */
class ScenarioManager implements ScenarioInterface
{

    /** @var Game */
    private $game;

    /** @var Scenario[] */
    private $scenarios = [];

    /** @var Scenario[] */
    private $actives = [];

    /**
     * ScenarioManager constructor.
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        # Register game
        $this->game = $game;

        # Register scenarios
        $this->registerScenario(new Backpacks());
        $this->registerScenario(new BloodDiamonds());
        $this->registerScenario(new CatEyes());
        $this->registerScenario(new Cutclean());
        $this->registerScenario(new DeathPole());
        $this->registerScenario(new DoNotDisturb());
        $this->registerScenario(new DoubleOres());
        $this->registerScenario(new Fireless());
        $this->registerScenario(new HasteyBoys());
        $this->registerScenario(new NoClean());
        $this->registerScenario(new NoFall());
        $this->registerScenario(new Rodless());
        $this->registerScenario(new Snowless());
        $this->registerScenario(new Timber());
        $this->registerScenario(new Timebomb());
        $this->registerScenario(new Bookception());
        $this->registerScenario(new DiamondLess());
        $this->registerScenario(new DiamondLimit());
        $this->registerScenario(new SuperHeros());

        //TODO

        # Register listener
        $this->getGame()->getPlugin()->getServer()->getPluginManager()->registerEvents(new ScenarioListener(), $this->getGame()->getPlugin());
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }

    /**
     * @return Scenario[]
     */
    public function getScenarios(): array
    {
        return $this->scenarios;
    }

    /**
     * @param Scenario $scenario
     */
    private function registerScenario(Scenario $scenario): void
    {
        $this->scenarios[$scenario->getName()] = $scenario;
    }

    /**
     * @return Scenario[]
     */
    public function getActives(): array
    {
        return $this->actives;
    }

    /**
     * @param Scenario $scenario
     * @return bool
     */
    public function isActive(Scenario $scenario): bool
    {
        return isset($this->actives[$scenario->getName()]);
    }

    /**
     * @param string $scenarioName
     * @return Scenario|null
     */
    public function isActiveByName(string $scenarioName): ?Scenario
    {
        foreach ($this->getActives() as $scenario) {
            if (strtolower($scenario->getName()) == strtolower($scenarioName)) {
                return $scenario;
            }
        }
        return null;
    }

    /**
     * @param Scenario $scenarioBase
     */
    public function addActive(Scenario $scenarioBase): void
    {
        if (!$this->isActive($scenarioBase)) {
            $scenario = clone $scenarioBase;
            $this->actives[$scenario->getName()] = $scenario;
            $scenario->handleEnable();
            $this->sort();
        }
    }

    /**
     * @param Scenario $scenarioBase
     */
    public function removeActive(Scenario $scenarioBase): void
    {
        if ($this->isActive($scenarioBase)) {
            unset($this->actives[$scenarioBase->getName()]);
            $scenarioBase->handleDisable();
        }
    }

    /**
     * Sorts the scenarios by priority
     */
    public function sort()
    {
        uasort($this->actives, function (Scenario $firstScenario, Scenario $secondScenario) {
            return $firstScenario->getPriority() <=> $secondScenario->getPriority();
        });
    }

    /**
     * @param string $methodName
     * @param Event $event
     * @return Event
     */
    private function call(string $methodName, Event $event)
    {
        if ($this->getGame()->getState() != GameState::WAITING) {
            foreach ($this->getActives() as $scenario) {
                $scenario->$methodName($event);
            }
        }
        return $event;
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function handlePlace(BlockPlaceEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param CraftItemEvent $event
     */
    public function handleCraft(CraftItemEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }


    /**
     * @param EntityDamageByChildEntityEvent $event
     */
    public function handleDamageByChildEntity(EntityDamageByChildEntityEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function handleDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param EntityDeathEvent $event
     */
    public function handleEntityDeath(EntityDeathEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function handleMove(PlayerMoveEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param GameStartEvent $event
     */
    public function handleStart(GameStartEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    /**
     * @param EntitySpawnEvent $event
     */
    public function handleEntitySpawn(EntitySpawnEvent $event): void
    {
        $this->call(__FUNCTION__, $event);
    }

    public function handleEnable(): void
    {
    }

    public function handleDisable(): void
    {
    }
}