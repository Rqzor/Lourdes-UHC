<?php

declare(strict_types=1);

namespace uhc\game;

use apibossbar\DiverseBossBar;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use uhc\border\BorderManager;
use uhc\commands\AliasCommand;
use uhc\commands\defaults\BanCommand;
use uhc\commands\defaults\GamemodeCommand;
use uhc\commands\defaults\KickCommand;
use uhc\commands\defaults\PardonCommand;
use uhc\commands\defaults\TellCommand;
use uhc\commands\FlyCommand;
use uhc\commands\FreezeCommand;
use uhc\commands\GlobalMuteCommand;
use uhc\commands\HelpopCommand;
use uhc\commands\MuteCommand;
use uhc\commands\NickCommand;
use uhc\commands\PingCommand;
use uhc\commands\PracticeCommand;
use uhc\commands\PrefixCommand;
use uhc\commands\RankCommand;
use uhc\commands\RespawnCommand;
use uhc\commands\ScenariosCommand;
use uhc\commands\StaffCommand;
use uhc\commands\TeamChatCommand;
use uhc\commands\TeamCommand;
use uhc\commands\TopKillsCommand;
use uhc\commands\UHCCommand;
use uhc\event\GameStartEvent;
use uhc\game\task\RandomTeleportTask;
use uhc\game\utils\GameFeed;
use uhc\game\utils\GameState;
use uhc\game\utils\GameUtils;
use uhc\game\utils\GameValues;
use uhc\player\disconnect\DisconnectManager;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\practice\PracticeManager;
use uhc\scenarios\ScenarioManager;
use uhc\sessions\SessionManager;
use uhc\sessions\types\PlayerSession;
use uhc\sessions\types\TeamSession;
use uhc\UHCLoader;

/**
 * Class Game
 * @package uhc\game
 */
class Game
{

    /** @var UHCLoader */
    private $plugin;

    /** @var StaffListener */
    private $staffManager;

    /** @var BorderManager */
    private $border;
    /** @var SessionManager */
    private $sessions;
    /** @var DisconnectManager */
    private $disconnectManager;
    /** @var PracticeManager */
    private $practice;
    /** @var ScenarioManager */
    private $scenarios;
    /** @var DiverseBossBar */
    private $bossBar;

    /** @var GameSettings */
    private $settings;

    /** @var Level|null */
    private $level = null;
    /** @var int */
    private $state = GameState::WAITING;
    /** @var bool */
    private $teams = false;

    /** @var int[] */
    private $broadcastStarting = [60, 30, 10, 5, 4, 3, 2, 1];

    /**
     * Game constructor.
     * @param UHCLoader $plugin
     */
    public function __construct(UHCLoader $plugin)
    {
        $this->plugin = $plugin;

        $this->initManagers();
        $this->initListeners();
        $this->initCommands();
        $this->initSettings();
    }

    /**
     * @return UHCLoader
     */
    public function getPlugin(): UHCLoader
    {
        return $this->plugin;
    }

    private function initManagers(): void
    {
        $this->border = new BorderManager($this);
        $this->sessions = new SessionManager();
        $this->disconnectManager = new DisconnectManager();
        $this->practice = new PracticeManager($this);
        $this->scenarios = new ScenarioManager($this);
        $this->bossBar = new DiverseBossBar();
    }

    private function initListeners(): void
    {
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents(new GameListener($this), $this->getPlugin());
        $this->staffManager = new StaffListener();
        $this->getPlugin()->getServer()->getPluginManager()->registerEvents($this->staffManager, $this->getPlugin());
    }

    private function initCommands(): void
    {
        # Unregister commands
        $commands = [
            'gamemode',
            'me',
            'ban',
            'ban-ip',
            'banlist',
            'pardon',
            'pardon-ip',
            'tell'
        ];

        foreach ($commands as $command) {
            $commandUnregister = $this->getPlugin()->getServer()->getCommandMap()->getCommand($command);

            if ($commandUnregister != null) {
                $this->getPlugin()->getServer()->getCommandMap()->unregister($commandUnregister);
            }
        }

        # Register commands
        $commands = [
            # Register defaults
            new BanCommand(),
            new GamemodeCommand(),
            new KickCommand(),
            new PardonCommand(),
            new TellCommand(),
            # Register new
            new AliasCommand(),
            new FlyCommand(),
            new FreezeCommand(),
            new GlobalMuteCommand(),
            new HelpopCommand(),
            new NickCommand(),
            new MuteCommand(),
            new PingCommand(),
            new PracticeCommand(),
            new PrefixCommand(),
            new RankCommand(),
            new RespawnCommand(),
            new ScenariosCommand(),
            new StaffCommand(),
            new TeamChatCommand(),
            new TeamCommand(),
            new TopKillsCommand(),
            new UHCCommand()
        ];

        foreach ($commands as $command) {
            $this->getPlugin()->getServer()->getCommandMap()->register($command->getName(), $command);
        }
    }

    private function initSettings(): void
    {
        $this->settings = new GameSettings();

        # Times
        new GameValues($this->getPlugin()->getConfig()->get('times'));
    }

    /**
     * @return StaffListener
     */
    public function getStaffManager(): StaffListener
    {
        return $this->staffManager;
    }

    /**
     * @return BorderManager
     */
    public function getBorder(): BorderManager
    {
        return $this->border;
    }

    /**
     * @return SessionManager
     */
    public function getSessions(): SessionManager
    {
        return $this->sessions;
    }

    /**
     * @return DisconnectManager
     */
    public function getDisconnectManager(): DisconnectManager
    {
        return $this->disconnectManager;
    }

    /**
     * @return PracticeManager
     */
    public function getPractice(): PracticeManager
    {
        return $this->practice;
    }

    /**
     * @return ScenarioManager
     */
    public function getScenarios(): ScenarioManager
    {
        return $this->scenarios;
    }

    /**
     * @return DiverseBossBar
     */
    public function getBossBar(): DiverseBossBar
    {
        return $this->bossBar;
    }

    /**
     * @return GameSettings
     */
    public function getSettings(): GameSettings
    {
        return $this->settings;
    }

    /**
     * @return Level|null
     */
    public function getLevel(): ?Level
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @return bool
     */
    public function isTeams(): bool
    {
        return $this->teams;
    }

    /**
     * @param Level|null $level
     */
    public function setLevel(?Level $level): void
    {
        $this->level = $level;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }

    /**
     * @param bool $teams
     */
    public function setTeams(bool $teams): void
    {
        $this->teams = $teams;
    }

    /**
     * @param string $type
     * @return GamePlayer[]|PlayerSession[]|DisconnectMob[]|null
     */
    public function getPlayers($type = 'online'): ?array
    {
        switch ($type) {
            case 'online':
                return array_filter($this->getPlugin()->getServer()->getOnlinePlayers(), function (Player $player): bool {
                    return $player instanceof GamePlayer && $player->isSpawned();
                });

            case 'alive':
                return array_filter($this->getPlugin()->getServer()->getOnlinePlayers(), function (Player $player): bool {
                    return $player instanceof GamePlayer && $player->isSpawned() && $player->getData()->isAlive();
                });

            case 'alives':
                return array_filter($this->getSessions()->getPlayers(), function (PlayerSession $player): bool {
                    return $player->isScattering() && $player->isAlive();
                });

            case 'offline':
                return array_filter($this->getLevel()->getEntities(), function (Entity $entity): bool {
                    return $entity instanceof DisconnectMob && $entity->getData() != null;
                });

            case 'spectators':
                return array_filter($this->getPlugin()->getServer()->getOnlinePlayers(), function (Player $player): bool {
                    return $player instanceof GamePlayer && $player->isSpawned() && $player->getData()->isSpectator();
                });

            case 'host':
                return array_filter($this->getPlugin()->getServer()->getOnlinePlayers(), function (Player $player): bool {
                    return $player instanceof GamePlayer && $player->isSpawned() && $player->getData()->isHost();
                });

            case 'noteam':
                return array_filter($this->getPlugin()->getServer()->getOnlinePlayers(), function (Player $player): bool {
                    return $player instanceof GamePlayer && $player->isSpawned() && $player->getData()->getTeam() == null;
                });

            case 'scattering':
                return array_filter($this->getSessions()->getPlayers(), function (PlayerSession $player): bool {
                    return !$player->isHost() && $player->isScattering();
                });

            case 'practice':
                return array_filter($this->getPlugin()->getServer()->getOnlinePlayers(), function (Player $player): bool {
                    return $player instanceof GamePlayer && $player->isSpawned() && $player->isInPractice();
                });
        }
        return null;
    }

    /**
     * @param string $type
     * @return TeamSession[]|null
     */
    public function getTeams($type = 'all'): ?array
    {
        switch ($type) {
            case 'all':
                return $this->getSessions()->getTeams();

            case 'alive':
                return array_filter($this->getSessions()->getTeams(), function (TeamSession $team): bool {
                    return count($team->getPlayers('alive')) != 0;
                });

            case 'alives':
                return array_filter($this->getSessions()->getTeams(), function (TeamSession $team): bool {
                    return count($team->getPlayers('alives')) != 0;
                });

            case 'scattering':
                return array_filter($this->getSessions()->getTeams(), function (TeamSession $team): bool {
                    return count($team->getPlayers('alive')) != 0 && $team->isScattering();
                });
        }
        return null;
    }

    public function startSetup(): void
    {
        $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new RandomTeleportTask($this), 20);
    }

    /**
     * @param GamePlayer $player
     */
    public function scatterPlayer(GamePlayer $player): void
    {
        $x = mt_rand(-$this->getBorder()->getSize(), $this->getBorder()->getSize());
        $z = mt_rand(-$this->getBorder()->getSize(), $this->getBorder()->getSize());
        $y = $this->getLevel()->getHighestBlockAt($x, $z);

        if ($y <= 1)
            $y = 127;
        else {
            $y += 2;

            if ($this->getState() != GameState::RUNNING)
                $player->setImmobile(true);
        }
        $player->getData()->setScattering(true);
        $player->reset();
        $player->teleport(new Position($x, $y, $z, $this->getLevel()));
        $player->getInventory()->setContents(GameUtils::getKit('default'));
    }

    /**
     * @param TeamSession|GamePlayer $session
     * @param false $respawn
     */
    public function scatterTeam($session, $respawn = false): void
    {
        if (!$respawn) {
            $x = mt_rand(-$this->getBorder()->getSize(), $this->getBorder()->getSize());
            $z = mt_rand(-$this->getBorder()->getSize(), $this->getBorder()->getSize());
            $y = $this->getLevel()->getHighestBlockAt($x, $z);
            $pos = new Position($x, $y, $z, $this->getLevel());

            if ($pos->y <= 1)
                $pos->y = 127;
            else
                $pos->y += 2;
            $players = $session->getPlayers('alive');
            array_walk($players, function (GamePlayer $player) use ($pos): void {
                if ($this->getState() != GameState::RUNNING)
                    $player->setImmobile(true);
                $player->getData()->setScattering(true);
                $player->reset();
                $player->teleport($pos);
                $player->getInventory()->setContents(GameUtils::getKit('default'));
            });
            $session->setScattering(true);
            $session->setScatterPosition($pos);
        } else {
            $pos = $session->getTeam()->getScatterPosition();
            $pos->y = $this->getLevel()->getHighestBlockAt($pos->x, $pos->z) + 1;
            $session->getData()->setScattering(true);
            $session->reset();
            $session->teleport($pos);
            $session->getInventory()->setContents(GameUtils::getKit('default'));
        }
    }

    /**
     * @param GamePlayer $player
     */
    public function respawn(GamePlayer $player): void
    {
        if ($this->isTeams()) {
            if ($player->getData()->getTeam() == null)
                $this->getSessions()->addTeam($player);
            $team = $player->getTeam();

            if ($player->getTeam()->isScattering()) {
                $this->scatterTeam($player, true);
            } else {
                $this->scatterTeam($team);
            }
        } else {
            $this->scatterPlayer($player);
        }
    }

    public function checkWinner(): void
    {
        /** @var TeamSession[]|PlayerSession[] $objects */
        $objects = array_values(($this->isTeams() ? $this->getTeams('alive') : $this->getPlayers('alives')));

        if (count($objects) == 1) {
            $this->setState(GameState::RESTARTING);
            GameFeed::sendPostWinner();
            $players = $this->getPlugin()->getServer()->getOnlinePlayers();
            array_walk($players, function (Player $player) use ($objects): void {
                if ($player instanceof GamePlayer && $player->isSpawned()) {
                    $player->changeScoreboard();
                    GameUtils::addSound($player, 'mob.shulker.teleport');

                    if ($this->isTeams() && $player->getTeam() != null && $player->getTeam()->getTeamInt() == $objects[0]->getTeamInt() || !$this->isTeams() && $player->getName() == $objects[0]->getName())
                        $player->sendTitle(TextFormat::LIGHT_PURPLE . 'Congratulations', TextFormat::GREEN . ($this->isTeams() ? 'Your team won!' : 'You win!'), 20, 10 * 20, 20);
                    else
                        $player->sendTitle(TextFormat::LIGHT_PURPLE . ($this->isTeams() ? 'Team Winner: ' . TextFormat::WHITE . '#' . $objects[0]->getTeamInt() : 'Winner: ' . TextFormat::WHITE . $objects[0]->getName(true)), TextFormat::GREEN . 'Did you have fun spectating?', 20, 10 * 20, 20);
                }
            });
        }
    }

    public function update(): void
    {
        switch ($this->getState()) {
            case GameState::STARTING:
                GameValues::$STARTING--;
                $nextTime = GameValues::$STARTING;
                $broadcastMatches = array_filter($this->broadcastStarting, function (int $broadcastTime) use ($nextTime): bool {
                    return $nextTime === $broadcastTime;
                });

                if (count($broadcastMatches) > 0) {
                    $broadcastTime = $broadcastMatches[array_key_first($broadcastMatches)];
                    $this->getPlugin()->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . 'UHC will start in ' . $broadcastTime . ' second(s)');

                    foreach ($this->getPlayers('online') as $player)
                        GameUtils::addSound($player, 'note.flute');
                }

                if (GameValues::$STARTING == 0) {
                    $this->getPlugin()->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . 'UHC started. Good luck!');
                    $this->setState(GameState::RUNNING);
                    (new GameStartEvent($this))->call();
                    $this->getBossBar()->setTitle(TextFormat::WHITE . 'Final heal will occur in: ' . TextFormat::LIGHT_PURPLE . gmdate('i:s', (GameValues::$FINAL_HEAL - GameValues::$RUNNING)));
                    $this->getBossBar()->setPercentage(1.0);
                    $players = $this->getPlayers('online');
                    array_walk($players, function (GamePlayer $player): void {
                        GameUtils::addSound($player, 'cauldron.explode');
                        $player->changeScoreboard();
                        $this->getBossBar()->addPlayer($player);

                        if ($player->isImmobile())
                            $player->setImmobile(false);
                    });
                }
                break;

            case GameState::RUNNING:
                GameValues::$RUNNING++;

                if (GameValues::$RUNNING == GameValues::$FINAL_HEAL) {
                    $this->getBossBar()->setTitle(TextFormat::WHITE . 'Grace will end in: ' . TextFormat::LIGHT_PURPLE . gmdate('i:s', (GameValues::$GRACE_PERIOD - GameValues::$RUNNING)));
                    $this->getBossBar()->setPercentage(1.0);
                    /** @var GamePlayer[]|DisconnectMob[] $players */
                    $players = $this->getPlayers('alive') + $this->getPlayers('offline');
                    array_walk($players, function ($player): void {
                        $player->setHealth($player->getMaxHealth());

                        if ($player instanceof GamePlayer) {
                            $player->sendMessage(TextFormat::GREEN . 'Your life was regenerated');
                            GameUtils::addSound($player, 'random.levelup');
                        }
                    });
                } elseif (GameValues::$RUNNING < GameValues::$FINAL_HEAL) {
                    $this->getBossBar()->setTitle(TextFormat::WHITE . 'Final heal will occur in: ' . TextFormat::LIGHT_PURPLE . gmdate('i:s', (GameValues::$FINAL_HEAL - GameValues::$RUNNING)));
                    $this->getBossBar()->setPercentage(round((1.0 - (GameValues::$RUNNING / GameValues::$FINAL_HEAL)), 2));
                }

                if (GameValues::$RUNNING == GameValues::$GLOBAL_MUTE) {
                    $this->getSettings()->setGlobalMute(false);
                    $this->getPlugin()->getServer()->broadcastMessage(TextFormat::DARK_GREEN . 'Global Mute was successfully disabled');
                }

                if (GameValues::$RUNNING == GameValues::$GRACE_PERIOD) {
                    $this->getBossBar()->removeAllPlayers();
                    $this->getPlugin()->getServer()->broadcastMessage(TextFormat::DARK_AQUA . 'Grace period ended. Good luck!');
                } elseif (GameValues::$RUNNING < GameValues::$GRACE_PERIOD && GameValues::$RUNNING > GameValues::$FINAL_HEAL) {
                    $this->getBossBar()->setTitle(TextFormat::WHITE . 'Grace will end in: ' . TextFormat::LIGHT_PURPLE . gmdate('i:s', (GameValues::$GRACE_PERIOD - GameValues::$RUNNING)));
                    $this->getBossBar()->setPercentage(round((1.0) - (GameValues::$RUNNING - GameValues::$FINAL_HEAL) / (GameValues::$GRACE_PERIOD - GameValues::$FINAL_HEAL), 2));
                }
                break;
        }
    }

    // > - Others

    public function fixTeleport(): Position
    {
        $x = mt_rand(-$this->getBorder()->getSize(), $this->getBorder()->getSize());
        $z = mt_rand(-$this->getBorder()->getSize(), $this->getBorder()->getSize());
        $y = $this->getLevel()->getHighestBlockAt($x, $z);
        return new Position($x, $y + 2, $z, $this->getLevel());
    }
}