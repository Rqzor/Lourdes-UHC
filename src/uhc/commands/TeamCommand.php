<?php

declare(strict_types=1);

namespace uhc\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use uhc\game\utils\GameState;
use uhc\player\GamePlayer;

/**
 * Class TeamCommand
 * @package uhc\commands
 */
class TeamCommand extends Command
{

    /** @var array */
    private $invite = [];

    /**
     * TeamCommand constructor.
     */
    public function __construct()
    {
        parent::__construct('team');
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

        if (!$sender->getGame()->isTeams()) {
            $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Please complete the arguments.');
            return;
        }

        switch (strtolower($args[0])) {
            case 'create':
            case 'crear':
            case 'criar':
                if ($sender->getGame()->getState() != GameState::WAITING || !$sender->getData()->isAlive()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getTeam() != null) {
                    $sender->sendMessage(TextFormat::RED . 'You already have a team.');
                    return;
                }
                $sender->getGame()->getSessions()->addTeam($sender);
                $sender->sendMessage(TextFormat::GREEN . 'You have created team #' . $sender->getTeam()->getTeamInt());
                $sender->getServer()->broadcastMessage(TextFormat::GRAY . 'Team #' . $sender->getTeam()->getTeamInt() . ' was created by ' . TextFormat::GREEN . $sender->getName());
                break;

            case 'invite':
            case 'invitar':
            case 'convidar':
                if ($sender->getGame()->getState() != GameState::WAITING || !$sender->getData()->isAlive()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getTeam() == null) {
                    $sender->sendMessage(TextFormat::RED . 'You dont have a team.');
                    return;
                }

                if ($sender->getTeam()->getOwner() != $sender->getName()) {
                    $sender->sendMessage(TextFormat::RED . 'You cant use this command because you dont own the team');
                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments ' . TextFormat::GRAY . '(/team invite [player])');
                    return;
                }

                if (count($sender->getTeam()->getPlayers()) == $sender->getGame()->getSettings()->getMaxPlayers()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot invite anyone else to the team because it is complete.');
                    return;
                }
                $player = $sender->getServer()->getPlayer($args[1]);

                if (!$player instanceof GamePlayer) {
                    $sender->sendMessage(TextFormat::RED . 'Player is not online. Try again.');
                    return;
                }

                if ($player->getInput() == GamePlayer::KEYBOARD) {
                    if (count($sender->getTeam()->getPlayers('keyboard')) == $sender->getGame()->getSettings()->getKeyboardPlayers()) {
                        $sender->sendMessage(TextFormat::RED . 'You cannot invite any keyboard player.');
                        return;
                    }
                }

                if ($player->getTeam() != null) {
                    $sender->sendMessage(TextFormat::RED . 'Player is already in another team');
                    return;
                }
                $this->invite[$player->getName()][$sender->getName()] = $sender->getTeam()->getTeamInt();
                $sender->sendMessage(TextFormat::GREEN . 'You have invited the player to your team successfully');
                $player->sendMessage(TextFormat::GRAY . 'You have received a request to join team ' . TextFormat::GREEN . '#' . $sender->getTeam()->getTeamInt() . TextFormat::GRAY . ' by ' . TextFormat::GREEN . $sender->getName(true));
                break;

            case 'accept':
            case 'aceptar':
            case 'aceitar':
            	if ($sender->getGame()->getState() != GameState::WAITING || !$sender->getData()->isAlive()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getTeam() != null) {
                    $sender->sendMessage(TextFormat::RED . 'You already have a team.');
                    return;
                }

                if (!isset($this->invite[$sender->getName()])) {
                    $sender->sendMessage(TextFormat::RED . 'You have not received invitations for now.');
                    return;
                }
                $invites = $this->invite[$sender->getName()];
                $team = null;
                $player = null;

                if (isset($args[1])) {
                    if (!isset($invites[$args[1]])) {
                        $sender->sendMessage(TextFormat::RED . 'You have not received any invitation from ' . TextFormat::GRAY . $args[1]);
                        return;
                    }
                    $team = $invites[$args[1]];
                    $player = $args[1];
                } else {
                    $team = array_values($invites)[0];
                    $player = array_keys($invites)[0];
                }
                $team = $sender->getGame()->getSessions()->getTeam($team);

                if ($team == null) {
                    unset($this->invite[$sender->getName()][$player]);
                    $sender->sendMessage(TextFormat::RED . 'An error occurred.');
                    return;
                }

                if (count($team->getPlayers()) == $sender->getGame()->getSettings()->getMaxPlayers()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot join the team because it is complete.');
                    unset($this->invite[$sender->getName()][$player]);
                    return;
                }

                if ($sender->getInput() == GamePlayer::KEYBOARD) {
                    if (count($team->getPlayers('keyboard')) == $sender->getGame()->getSettings()->getKeyboardPlayers()) {
                        $sender->sendMessage(TextFormat::RED . 'You cant join the team because it no longer accepts keyboard players');
                        unset($this->invite[$sender->getName()][$player]);
                        return;
                    }
                }
                $sender->getData()->setTeam($team->getTeamInt());
                $team->broadcast(TextFormat::GREEN . $sender->getName(true) . ' has joined the team');
                unset($this->invite[$sender->getName()]);
                break;

            case 'leave':
                if ($sender->getGame()->getState() != GameState::WAITING || !$sender->getData()->isAlive()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getTeam() == null) {
                    $sender->sendMessage(TextFormat::RED . 'You dont have a team.');
                    return;
                }

                if ($sender->getTeam()->getOwner() == $sender->getName()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot leave the team because you are the owner');
                    return;
                }
                $teamInt = $sender->getTeam()->getTeamInt();
                $sender->getData()->setTeam(null);
                ($sender->getGame()->getSessions()->getTeam($teamInt))->broadcast(TextFormat::RED . $sender->getName(true) . ' has left the team');
                break;

            case 'kick':
            case 'kickar':
                if ($sender->getGame()->getState() != GameState::WAITING || !$sender->getData()->isAlive()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getTeam() == null) {
                    $sender->sendMessage(TextFormat::RED . 'You dont have a team.');
                    return;
                }

                if ($sender->getTeam()->getOwner() != $sender->getName()) {
                    $sender->sendMessage(TextFormat::RED . 'You cant use this command because you dont own the team');
                    return;
                }

                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED . 'Please complete the arguments ' . TextFormat::GRAY . '(/team kick [player])');
                    return;
                }
                $player = $sender->getServer()->getPlayer($args[1]);

                if ($player instanceof GamePlayer)
                    $player = $player->getName();
                else
                    $player = $args[1];

                if (($session = $sender->getGame()->getSessions()->getPlayer($player)) == null || $session->getTeam() != $sender->getTeam()->getTeamInt()) {
                    $sender->sendMessage(TextFormat::RED . 'Player ' . TextFormat::GRAY . $player . TextFormat::RED . ' is not in the team');
                    return;
                }
                $session->setTeam(null);

                if ($session->isOnline())
                    $session->getInstance()->sendMessage(TextFormat::RED . 'You have been kicked from the team.');
                $sender->getTeam()->broadcast(TextFormat::RED . 'Player ' . TextFormat::GRAY . $player . TextFormat::RED . ' was kicked from the team');
                break;

            case 'disband':
            case 'disolver':
            case 'dissolver':
                if ($sender->getGame()->getState() != GameState::WAITING || !$sender->getData()->isAlive()) {
                    $sender->sendMessage(TextFormat::RED . 'You cannot use this command.');
                    return;
                }

                if ($sender->getTeam() == null) {
                    $sender->sendMessage(TextFormat::RED . 'You dont have a team.');
                    return;
                }

                if ($sender->getTeam()->getOwner() != $sender->getName()) {
                    $sender->sendMessage(TextFormat::RED . 'You cant use this command because you dont own the team');
                    return;
                }

                foreach ($sender->getTeam()->getPlayers('all') as $session) {
                    if ($session->isOnline())
                        $session->getInstance()->sendMessage(TextFormat::RED . 'The team was dissolved by the owner');
                    $session->setTeam(null);
                }
                break;

            case 'list':
            case 'lista':
                if (!$sender->hasPermission('list.team.command.permission')) {
                    $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                    return;
                }
                $sender->sendMessage(TextFormat::LIGHT_PURPLE . 'List of teams created' . PHP_EOL);

                foreach ($sender->getGame()->getSessions()->getTeams() as $team) {
                    $sender->sendMessage(TextFormat::WHITE . 'Team #' . $team->getTeamInt() . ': ' . TextFormat::LIGHT_PURPLE . implode(', ', array_keys($team->getPlayers())));
                }
                break;

            case 'forceset':
                if (!$sender->hasPermission('list.team.command.permission')) {
                    $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                    return;
                }
                break;

            default:
                $sender->sendMessage(TextFormat::RED . 'This sub command does not exist. Please try again');
                break;
        }
    }
}