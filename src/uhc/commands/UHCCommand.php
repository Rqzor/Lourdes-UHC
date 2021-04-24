<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\event\player\PlayerAddHostEvent;
use uhc\event\player\PlayerRemoveHostEvent;
use uhc\form\FormUtils;
use uhc\game\utils\GameState;
use uhc\game\utils\GameUtils;
use uhc\game\utils\GameValues;
use uhc\player\GamePlayer;
use uhc\sessions\types\PlayerSession;

/**
 * Class UHCCommand
 * @package uhc\commands
 */
class UHCCommand extends Command
{

    /**
     * UHCCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('uhc', 'Main command to use the UHC');
        $this->setPermission('uhc.command.permission');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof GamePlayer)
            return;

        if (!$this->testPermission($sender))
            return;

        if (!isset($args[0])) {
            if ($sender->getData()->isHost())
                FormUtils::createMainForm($sender);
            else
                $sender->sendMessage(TextFormat::RED . '/uhc <start|announce|host|time|players|remove>');
            return;
        }

        switch ($args[0]) {
            case 'start':
                if ($sender->getGame()->getState() != GameState::WAITING) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getGame()->getLevel() == null) {
                    $sender->sendMessage(TextFormat::RED . 'Please configure the event');
                    return;
                }
                $sender->getGame()->setState(GameState::SETUP);

                foreach ($sender->getGame()->getPlayers('online') as $player) {
                    if ($player->getData()->isAlive())
                        if ($sender->getGame()->isTeams() && $player->getTeam() == null)
                            $sender->getGame()->getSessions()->addTeam($player);

                    if ($player->isInPractice()) {
                        $player->setInPractice(false);
                    }

                    if ($player->getAllowFlight())
                        $player->setAllowFlight(true);
                    $player->reset();
                    $player->teleport($sender->getGame()->getLevel()->getSpawnLocation()->add(0, 4));
                    $player->changeScoreboard();
                }
                $sender->getGame()->startSetup();
                $sender->getGame()->getSettings()->setGlobalMute(true);
                break;

            case 'announce':
                if ($sender->getGame()->getState() != GameState::WAITING) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }
                FormUtils::createAnnounceForm($sender);
                break;

            case 'host':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
                    return true;
                }
                $player = $sender->getServer()->getPlayer($args[1]);

                if (!$player instanceof GamePlayer) {
                    $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
                    return true;
                }

                if (!$player->getData()->isHost()) {
                    $player->getData()->setHost(true);
                    $player->getData()->setSpectator(false);
                    $player->sendMessage(TextFormat::GREEN . 'You were added as another host of the event');
                    $sender->sendMessage(TextFormat::GREEN . 'You added player ' . $player->getName() . ' as another host of the event');
                    (new PlayerAddHostEvent($player))->call();
                } else {
                    $player->getData()->setHost(false);
                    GameUtils::setVanish($player, true);
                    $player->sendMessage(TextFormat::RED . 'You were removed as the event host');
                    $sender->sendMessage(TextFormat::RED . 'You have eliminated the player ' . $player->getName() . ' as host of the event');
                    (new PlayerRemoveHostEvent($player))->call();
                }

                if ($sender->getGame()->getState() == GameState::RUNNING)
                    $player->changeScoreboard();
                break;

            case 'time':
                if (!isset($args[1]))
                    $minutes = 1 * 60;
                else
                    $minutes = (int) $args[1] * 60;

                if (!isset($args[2]))
                    $seconds = 1;
                else
                    $seconds = (int) $args[2];
                $time = $minutes + $seconds;
                GameValues::$RUNNING = $time;
                break;

            case 'players':
                if (count($sender->getGame()->getPlayers('alives')) == 0) {
                    $sender->sendMessage(TextFormat::RED . 'There are no players right now.');
                    return true;
                }
                $players = implode(', ', array_map(function (PlayerSession $player): string {
                    return TextFormat::WHITE . $player->getName() . ($player->isOnline() ? TextFormat::GREEN . ' (ONLINE)' : TextFormat::RED . ' (OFFLINE)');
                }, $sender->getGame()->getPlayers('alives')));
                $sender->sendMessage(TextFormat::GRAY . 'Players alive: ' . $players);
                break;

            case 'remove':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.' . TextFormat::GRAY . ' (/uhc remove <player>)');
                    return;
                }
                $player = $sender->getGame()->getSessions()->getPlayer($args[1]);

                if ($player == null) {
                    $sender->sendMessage(TextFormat::RED . 'Player not found. Try again.');
                    return;
                }
                $player->setSpectator(true);
                $sender->sendMessage(TextFormat::GRAY . 'Player ' . TextFormat::RED . $player->getName() . TextFormat::GRAY . ' has been successfully removed from the UHC');
                $sender->getGame()->checkWinner();
                break;

            default:
                $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                break;
        }
    }
}