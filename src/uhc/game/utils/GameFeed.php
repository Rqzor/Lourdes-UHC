<?php

declare(strict_types=1);

namespace uhc\game\utils;

use DateTime;
use twitter\TwitterAPI;
use uhc\game\Game;
use discord\Embed;
use discord\Message;
use discord\Webhook;
use uhc\scenarios\Scenario;
use uhc\sessions\types\PlayerSession;
use uhc\sessions\types\TeamSession;
use uhc\UHCLoader;

/**
 * Class GameFeed
 * @package uhc\game\utils
 */
final class GameFeed
{

    /**
     * @return Game
     */
    private static function getGame(): Game
    {
        return UHCLoader::getInstance()->getGame();
    }

    /**
     * @param int $time
     */
    public static function sendPostGame(int $time = 5): void
    {
        /** Config */
        $config = UHCLoader::getInstance()->getConfig()->get('webhooks');

        /** Parameters */
        $gameType = self::getGame()->isTeams() ? 'Teams of ' . self::getGame()->getSettings()->getMaxPlayers() : 'FFA';
        $scenarios = self::getGame()->getScenarios()->getActives() > 0 ? implode(PHP_EOL, (array_map(function (Scenario $scenario): string {
            return '• ' . $scenario->getName() . ' - ' . $scenario->getDescription();
        }, self::getGame()->getScenarios()->getActives()))) : '• None';
        $scenariosTwitter = self::getGame()->getScenarios()->getActives() > 0 ? implode(', ', (array_map(function (Scenario $scenario): string {
            return $scenario->getName();
        }, self::getGame()->getScenarios()->getActives()))) : 'None';

        /** Discord API */
        $webHook = new Webhook($config['channel-announce']);
        $embed = new Embed();
        $message = new Message();
        $message->setContent('@everyone');
        $embed->setColor(hexdec('7F25D9'));
        $embed->setTitle('Game Feed');
        $embed->addField('Game Type', 'UHC');
        $embed->addField('Gamemode', $gameType);
        $embed->addField('Start in', $time . ' minute(s)');
        $embed->addField('Scenarios', $scenarios);
        $embed->addField('Server IP', 'uhc.weekomnetwork.com');
        $embed->addField('Server PORT', '19132 (default)');
        $embed->setTimestamp(new DateTime());
        $message->addEmbed($embed);
        $webHook->send($message);

        /** Twitter API */
        $tweet = 'WeekomUHC [UHC-1] [NA]' . PHP_EOL . PHP_EOL . '• Start in: ' . $time . ' minute(s)' . PHP_EOL . '• Team Size: ' . $gameType . PHP_EOL . '• Nether: Disabled' . PHP_EOL . '• Scenarios: ' . $scenariosTwitter . PHP_EOL . PHP_EOL . '• IP: uhc.weekomnetwork.com';
        // $tweet = '       Game Feed ' . PHP_EOL . PHP_EOL . ' • Game Type: UHC ' . PHP_EOL . ' • Gamemode: ' . $gameType . PHP_EOL . ' • Scenarios: ' . $scenariosTwitter . PHP_EOL . ' • Start in ' . $time . ' minute(s) ' . PHP_EOL . ' • IP: uhc.weekomnetwork.com ' . PHP_EOL . '' . PHP_EOL . ' • Enter our discord for more news: https://discord.gg/Y96jcxrcbz';
        TwitterAPI::postTweet($tweet);
    }

    public static function sendPostWinner(): void
    {
        /** Config */
        $config = UHCLoader::getInstance()->getConfig()->get('webhooks');

        /** Discord API */
        $webHook = new Webhook($config['channel-winners']);
        $embed = new Embed();
        $message = new Message();
        $embed->setColor(hexdec('7F25D9'));
        $embed->setTitle('Game Winner');

        if (self::getGame()->isTeams()) {
            /** @var TeamSession $team */
            $team = array_values(self::getGame()->getTeams('alive'))[0];
            /** @var string $players */
            $players = implode(PHP_EOL, array_map(function (PlayerSession $player): string {
                return '• ' . $player->getName(true) . ' - ' . $player->getEliminations() . ' eliminations(s)';
            }, $team->getPlayers('alives')));
            $embed->setDescription('The game was UHC TO' . self::getGame()->getSettings()->getMaxPlayers());
            $embed->addField('Winner(s)', $players);

            /** Twitter API */
            $tweet = '   Game Winner' . PHP_EOL . PHP_EOL . 'Congratulations to team #' . $team->getTeamInt() . ' for winning the UHC TO' . self::getGame()->getSettings()->getMaxPlayers() . PHP_EOL . PHP_EOL . $players . PHP_EOL . PHP_EOL . 'If you want to play our UHC and be aware of the new updates, please join our discord: https://discord.gg/JkjGKmRNZh';
        } else {
            /** @var PlayerSession $player */
            $player = array_values(self::getGame()->getPlayers('alives'))[0];
            $embed->setDescription('The game was UHC FFA');
            $embed->addField('Winner', '• ' . $player->getName(true) . ' - ' . $player->getEliminations() . ' elimination(s)');

            /** Twitter API */
            $tweet = '   Game Winner' . PHP_EOL . PHP_EOL . 'Congratulations to player ' . $player->getName(true) . ' for winning the UHC FFA ' . PHP_EOL . PHP_EOL . '• Elimination(s) - ' . $player->getEliminations() . PHP_EOL . PHP_EOL . 'If you want to play our UHC and be aware of the new updates, please join our discord: https://discord.gg/JkjGKmRNZh';
        }
        $embed->setTimestamp(new DateTime());
        $message->addEmbed($embed);
        $webHook->send($message);

        /** Twitter API */
        TwitterAPI::postTweet($tweet);
    }

    /**
     * @param string $message
     */
    public static function sendPostKill(string $message): void
    {
        /** Config */
        $config = UHCLoader::getInstance()->getConfig()->get('webhooks');

        /** Discord API */
        $webHook = new Webhook($config['channel-kills']);
        $embed = new Embed();
        $msg = new Message();
        $embed->setTitle('Game Kills');
        $embed->setColor(hexdec('7F25D9'));
        $embed->setDescription($message);
        $msg->addEmbed($embed);
        $webHook->send($msg);
    }

    /**
     * @param string $player
     * @param string $staff
     * @param string $reason
     * @param string $time
     * @param string $server
     */
    public static function sendPostBan(string $player, string $staff, string $reason, string $time, string $server = 'UHC Server'): void
    {
        /** Config */
        $config = UHCLoader::getInstance()->getConfig()->get('webhooks');

        /** Discord API */
        $webHook = new Webhook($config['channel-bans']);
        $embed = new Embed();
        $message = new Message();
        $embed->setColor(hexdec('5e6963'));
        $embed->setTitle('Banned Player');
        $embed->setDescription('Player ' . $player . ' has been banned ' . $time . PHP_EOL . PHP_EOL . 'Reason: ' . $reason . PHP_EOL . 'Author: ' . $staff . PHP_EOL . 'Server: ' . $server);
        $embed->setTimestamp(new DateTime());
        $message->addEmbed($embed);
        $webHook->send($message);
    }
}