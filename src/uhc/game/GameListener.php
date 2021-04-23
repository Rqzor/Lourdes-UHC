<?php

declare(strict_types=1);

namespace uhc\game;

use pocketmine\block\BlockIds;
use pocketmine\entity\Effect;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Item;
use pocketmine\item\Shears;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameFeed;
use uhc\game\utils\GameState;
use uhc\game\utils\GameUtils;
use uhc\game\utils\GameValues;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;

/**
 * Class GameListener
 * @package uhc\game
 */
class GameListener implements Listener
{

    /** @var Game */
    private $game;

    /**
     * GameListener constructor.
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @return Game
     */
    private function getGame(): Game
    {
        return $this->game;
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function handleBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            if ($this->getGame()->getState() != GameState::RUNNING) {
                if (!$player->hasPermission('build.permission'))
                    $event->setCancelled();
            }

            if (!$event->isCancelled()) {
                $block = $event->getBlock();

                if ($block->getId() == BlockIds::LEAVES || $block->getId() == BlockIds::LEAVES2) {
                    $max = 100;

                    if ($event->getItem() instanceof Shears)
                        $max /= 1.5;

                    if (mt_rand(0, (int)$max) <= $this->getGame()->getSettings()->getAppleRate())
                        $event->setDropsVariadic(Item::get(Item::APPLE));
                }
            }
        }
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function handlePlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            if ($this->getGame()->getState() != GameState::RUNNING) {
                if (!$player->hasPermission('build.permission'))
                    $event->setCancelled();
            }
        }
    }

    /**
     * @param EntityDamageByChildEntityEvent $event
     */
    public function handleDamageByChildEntity(EntityDamageByChildEntityEvent $event): void
	{
		$entity = $event->getEntity();
		$damager = $event->getDamager();
		$child = $event->getChild();
		$cancel = $event->isCancelled();
		
		if ($entity instanceof GamePlayer && $entity->isSpawned() && !$entity->isInPractice() || $entity instanceof DisconnectMob && $entity->getData() != null) {
			if ($child instanceof Projectile) {
				if (!$damager instanceof GamePlayer)
					return;
					
				if (!$cancel && $this->getGame()->getState() == GameState::RUNNING) {
                    if ($this->getGame()->isTeams() && !$this->getGame()->getSettings()->isTeamDamage())
                        $cancel = $entity->getData()->getTeam() == $damager->getData()->getTeam();

                    if (GameValues::$RUNNING < GameValues::$GRACE_PERIOD)
                        if ($event->getCause() == $event::CAUSE_ENTITY_ATTACK || $event->getCause() == $event::CAUSE_PROJECTILE)
                            $cancel = true;

                    if (!$cancel && $damager->getData()->isFreeze())
                        $cancel = true;

                    if (!$cancel) {
                        $entity->getData()->setLastHit(['player' => $damager->getName(true), 'time' => time()]);
                        $damager->getData()->setLastHit(['player' => $entity->getName(true), 'time' => time()]);
                    }
                }
			}
		}

        if ($damager instanceof GamePlayer && $child instanceof Arrow)
            if (!$cancel && $damager->hasPermission('sounds.permission'))
                GameUtils::sendArrowDingSound($damager);
		$event->setCancelled($cancel);
	}

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function handleDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $entity = $event->getEntity();
        $damager = $event->getDamager();
        $cancel = $event->isCancelled();

        if ($entity instanceof GamePlayer && $entity->isSpawned() && !$entity->isInPractice() || $entity instanceof DisconnectMob && $entity->getData() != null) {
            if ($damager instanceof GamePlayer && !$damager->isInPractice()) {
                if (!$cancel && $this->getGame()->getState() == GameState::RUNNING) {
                    if ($this->getGame()->isTeams() && !$this->getGame()->getSettings()->isTeamDamage())
                        $cancel = $entity->getData()->getTeam() == $damager->getData()->getTeam();

                    if (GameValues::$RUNNING < GameValues::$GRACE_PERIOD)
                        if ($event->getCause() == $event::CAUSE_ENTITY_ATTACK || $event->getCause() == $event::CAUSE_PROJECTILE)
                            $cancel = true;

                    if (!$cancel && $damager->getData()->isFreeze())
                        $cancel = true;

                    if (!$cancel) {
                        $entity->getData()->setLastHit(['player' => $damager->getName(), 'time' => time()]);
                        $damager->getData()->setLastHit(['player' => $entity->getName(), 'time' => time()]);
                    }
                }
            }
        }
        $event->setCancelled($cancel);
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function handleDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        $cancel = $event->isCancelled();

        if ($entity instanceof GamePlayer && $entity->isSpawned() && !$entity->isInPractice() || $entity instanceof DisconnectMob && $entity->getData() != null) {
            if ($this->getGame()->getState() != GameState::RUNNING) {
                $cancel = true;

                if (($this->getGame()->getState() == GameState::STARTING || $this->getGame()->getState() == GameState::SETUP) && $entity->getData()->isAlive() && $entity->getLevel()->getFolderName() == $this->getGame()->getLevel()->getFolderName())
                    $entity->setImmobile(true);
            } else {
                if (GameValues::$RUNNING < GameValues::$GRACE_PERIOD)
                    if ($event->getCause() == $event::CAUSE_ENTITY_ATTACK || $event->getCause() == $event::CAUSE_PROJECTILE)
                        $cancel = true;

                if ($entity->getData()->isFreeze())
                    $cancel = true;
            }
        }

        if ($event->getCause() == EntityDamageEvent::CAUSE_VOID)
            $entity->teleport($entity->getLevelNonNull()->getSpawnLocation());
        $event->setCancelled($cancel);
    }

    /**
     * @param EntityDeathEvent $event
     */
    public function handleEntityDeath(EntityDeathEvent $event): void
    {
        $player = $event->getEntity();
        $cause = $player->getLastDamageCause();
        $message = '';

        if ($player instanceof DisconnectMob && $player->getData() != null) {
        	$lastHit = $player->getData()->getLastHit();
        	
        	if ($lastHit != null && (time() - $lastHit['time']) <= 30) {
        		$damager = $this->getGame()->getSessions()->getPlayer($lastHit['player']);
        		$damager->addEliminations();
        		$damager->setLastHit(null);
        		$damager->setDoNotDisturb(null);
   		        $message = TextFormat::RED . '(AFK) ' . $player->getName() . TextFormat::GRAY . ' [' . $player->getData()->getEliminations() . '] ' . TextFormat::YELLOW . 'was slain by ' . TextFormat::RED . $damager->getName(true) . TextFormat::GRAY . ' [' . $damager->getEliminations() . ']';
        	} else {
        		if ($cause instanceof EntityDamageEvent) {
        			$id = $cause->getCause();
                    $causes = [EntityDamageEvent::CAUSE_STARVATION => 'died of hunger', EntityDamageEvent::CAUSE_CUSTOM => 'died', EntityDamageEvent::CAUSE_ENTITY_ATTACK => 'died', EntityDamageEvent::CAUSE_CONTACT => 'died', EntityDamageEvent::CAUSE_FALL => 'fell too hard', EntityDamageEvent::CAUSE_LAVA => 'swam in lava', EntityDamageEvent::CAUSE_MAGIC => 'has mysteriously died', EntityDamageEvent::CAUSE_SUFFOCATION => 'died suffocated', EntityDamageEvent::CAUSE_FIRE => 'got burned', EntityDamageEvent::CAUSE_FIRE_TICK => 'got burned', EntityDamageEvent::CAUSE_SUICIDE => 'committed suicide', EntityDamageEvent::CAUSE_PROJECTILE => 'was shot', EntityDamageEvent::CAUSE_DROWNING => 'drowned', EntityDamageEvent::CAUSE_ENTITY_EXPLOSION => 'exploded', EntityDamageEvent::CAUSE_BLOCK_EXPLOSION => 'exploded'];
                    $message = TextFormat::RED . '(AFK) ' . $player->getName() . TextFormat::GRAY . ' [' . $player->getData()->getEliminations() . '] ' . TextFormat::YELLOW . (isset($causes[$id]) ? $causes[$id] : 'died');
        		}
        	}
            $event->setDrops($player->getArmorContents() + $player->getContents());

            $player->getData()->setSpectator(true);
            $player->getData()->setContents($player->getArmorContents(), $player->getContents());
            $player->getData()->setLastPosition($player->getPosition());
            $player->getData()->setLastHit(null);
            $player->getData()->setDoNotDisturb(null);

            $this->getGame()->checkWinner();

            $this->getGame()->getPlugin()->getServer()->broadcastMessage($message);
            GameFeed::sendPostKill(TextFormat::clean($message));
        }
    }

    /**
     * @param EntityRegainHealthEvent $event
     */
    public function handleRegainHealth(EntityRegainHealthEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Living)
            return;

        if ($this->getGame()->getState() != GameState::RUNNING)
            return;

        if ($entity->hasEffect(Effect::REGENERATION))
            return;
        $event->setCancelled();
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function handleChat(PlayerChatEvent $event): void
    {
        $message = $event->getMessage();
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            if ($player->getData()->isHost()) {
                if (substr($message, 0, 1) == '!') {
                    $event->setCancelled();
                    $players = $this->getGame()->getPlayers('host');

                    array_walk($players, function (GamePlayer $host) use ($player, $message): void {
                        $host->sendMessage(TextFormat::LIGHT_PURPLE . '[SC] ' . TextFormat::GRAY . $player->getName() . ': ' . TextFormat::WHITE . substr($message, 1));
                    });
                    return;
                }
            }

            if ($this->getGame()->getSettings()->isGlobalMute() && !$player->hasPermission('globalmute.permission')) {
                $event->setCancelled();
            }

            if ($player->getData()->isMute()) {
                $event->setCancelled();
            }

            if (!$event->isCancelled())
                $event->setFormat($player->getFormat() . ' ' . TextFormat::WHITE . $message);
        }
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     */
    public function handleCommandPreprocess(PlayerCommandPreprocessEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            $args = explode(' ', $event->getMessage());

            if ($args[0] == '/kill' || $args[0] == '/suicide') {
                if ($this->getGame()->getState() != GameState::RUNNING || !$player->getData()->isAlive())
                    $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function handleDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        $message = '';

        if ($player instanceof GamePlayer && $player->isSpawned() && !$player->isInPractice()) {
        	$lastHit = $player->getData()->getLastHit();
        
        	if ($lastHit != null && (time() - $lastHit['time']) <= 30) {
        		$damager = $this->getGame()->getSessions()->getPlayer($lastHit['player']);
        		$damager->addEliminations();
        		$damager->setLastHit(null);
        		$damager->setDoNotDisturb(null);
   		        $message = TextFormat::RED . $player->getName(true) . TextFormat::GRAY . ' [' . $player->getData()->getEliminations() . '] ' . TextFormat::YELLOW . 'was slain by ' . TextFormat::RED . $damager->getName(true) . TextFormat::GRAY . ' [' . $damager->getEliminations() . ']';
        	} else {
        		if ($cause instanceof EntityDamageEvent) {
        			$id = $cause->getCause();
                    $causes = [EntityDamageEvent::CAUSE_STARVATION => 'died of hunger', EntityDamageEvent::CAUSE_CUSTOM => 'died', EntityDamageEvent::CAUSE_ENTITY_ATTACK => 'died', EntityDamageEvent::CAUSE_CONTACT => 'died', EntityDamageEvent::CAUSE_FALL => 'fell too hard', EntityDamageEvent::CAUSE_LAVA => 'swam in lava', EntityDamageEvent::CAUSE_MAGIC => 'has mysteriously died', EntityDamageEvent::CAUSE_SUFFOCATION => 'died suffocated', EntityDamageEvent::CAUSE_FIRE => 'got burned', EntityDamageEvent::CAUSE_FIRE_TICK => 'got burned', EntityDamageEvent::CAUSE_SUICIDE => 'committed suicide', EntityDamageEvent::CAUSE_PROJECTILE => 'was shot', EntityDamageEvent::CAUSE_DROWNING => 'drowned', EntityDamageEvent::CAUSE_ENTITY_EXPLOSION => 'exploded', EntityDamageEvent::CAUSE_BLOCK_EXPLOSION => 'exploded'];
                    $message = TextFormat::RED . $player->getName(true) . TextFormat::GRAY . ' [' . $player->getData()->getEliminations() . '] ' . TextFormat::YELLOW . (isset($causes[$id]) ? $causes[$id] : 'died');
        		}
        	}
        	$player->getData()->setSpectator(true);
            $player->getData()->setContents($player->getArmorInventory()->getContents(), $player->getInventory()->getContents());
            $player->getData()->setLastPosition($player->getPosition());
            $player->getData()->setLastHit(null);
            $player->getData()->setDoNotDisturb(null);
            $player->setSpawn($player->getPosition()->add(0, 1, 0));

            $this->getGame()->checkWinner();

            $event->setDeathMessage($message);
            GameFeed::sendPostKill(TextFormat::clean($message));
        }
    }

    /**
     * @param PlayerDropItemEvent $event
     */
    public function handleDropItem(PlayerDropItemEvent $event): void
    {
        $item = $event->getItem();

        if ($item->getNamedTag()->hasTag('noDrop')) {
            $event->setCancelled();
        }
    }

    /**
     * @param PlayerExhaustEvent $event
     */
    public function handleExhaust(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            if ($this->getGame()->getState() != GameState::RUNNING)
                $event->setCancelled();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function handleInteract(PlayerInteractEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($player instanceof GamePlayer) {
            $state = $this->getGame()->getState();

            if ($item->getNamedTag()->hasTag('noDrop'))
                $event->setCancelled();

            if ($action == $event::RIGHT_CLICK_AIR)
                GameUtils::getFunctionByItem($event, $player, $item, $state);
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            $player->join();
            $event->setJoinMessage(TextFormat::GRAY . '[' . TextFormat::GREEN . '+' . TextFormat::GRAY . '] ' . TextFormat::WHITE . $player->getName(true));
        }
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function handleMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned() && $player->isOnline()) {
            if ($player->getData()->isFreeze())
                $event->setCancelled();
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function handleQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->isSpawned()) {
            $player->quit();
            $event->setQuitMessage(TextFormat::GRAY . '[' . TextFormat::RED . '-' . TextFormat::GRAY . '] ' . TextFormat::WHITE . $player->getName(true));
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function handleRespawn(PlayerRespawnEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof GamePlayer && $player->getData()->isSpectator()) {
            $player->reset(3);
            $player->getInventory()->setContents(GameUtils::getKit('spectator'));
        }
    }
}
