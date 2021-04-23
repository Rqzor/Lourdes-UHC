<?php

declare(strict_types=1);

namespace uhc\form;

use addon\AddonLoader;
use form\types\CustomForm;
use form\types\SimpleForm;
use pocketmine\level\Level;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use uhc\game\Game;
use uhc\game\utils\GameFeed;
use uhc\game\utils\GameState;
use uhc\game\utils\GameValues;
use uhc\player\disconnect\DisconnectMob;
use uhc\player\GamePlayer;
use uhc\scenarios\Scenario;
use uhc\UHCLoader;

/**
 * Class FormUtils
 * @package uhc\form
 */
final class FormUtils
{

    /** @var array */
    private static $data = [];

    /**
     * @return Game
     */
    private static function getGame(): Game
    {
        return UHCLoader::getInstance()->getGame();
    }

    /**
     * @param GamePlayer $player
     * @param string $data
     */
    private static function setData(GamePlayer $player, string $data): void
    {
        self::$data[$player->getName()] = $data;
    }

    /**
     * @param GamePlayer $player
     * @param bool $extraData
     */
    public static function createMainForm(GamePlayer $player, bool $extraData = false): void
    {
        $form = new SimpleForm(function (GamePlayer $player, int $data = null): bool {
            $result = $data;

            if ($result === null) {
                return false;
            }

            switch ($result) {
                case 0:
                    self::createWorldConfiguration($player);
                    break;

                case 1:
                    self::createTimesConfiguration($player);
                    break;

                case 2:
                    self::createBorderConfiguration($player);
                    break;

                case 3:
                    self::createTeamConfiguration($player);
                    break;

                case 4:
                    self::createScenarioConfiguration($player);
                    break;
            }
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Settings');
        if (!$extraData)
            $form->setContent(TextFormat::GRAY . 'Choose what you want to configure');
        else
            $form->setContent(TextFormat::GRAY . 'Choose what you want to configure' . PHP_EOL . self::$data[$player->getName()]);
        $form->addButton(TextFormat::WHITE . 'World settings' . PHP_EOL . TextFormat::GRAY . 'Tap to configure');
        $form->addButton(TextFormat::WHITE . 'Times settings' . PHP_EOL . TextFormat::GRAY . 'Tap to configure');
        $form->addButton(TextFormat::WHITE . 'Border settings' . PHP_EOL . TextFormat::GRAY . 'Tap to configure');
        $form->addButton(TextFormat::WHITE . 'Teams settings' . PHP_EOL . TextFormat::GRAY . 'Tap to configure');
        $form->addButton(TextFormat::WHITE . 'Scenarios settings' . PHP_EOL . TextFormat::GRAY . 'Tap to configure');
        $form->sendToPlayer($player);
    }

    /**
     * @param GamePlayer $player
     */
    private static function createWorldConfiguration(GamePlayer $player): void
    {
        $form = new CustomForm(function (GamePlayer $player, array $data = null): bool {
            if ($data == null) {
                self::setData($player, TextFormat::RED . '[WORLD] ERROR: Data return null');
                self::createMainForm($player, true);
                return false;
            }

            if (!$player->getServer()->getLevelByName($data[1]) instanceof Level) {
                self::setData($player, TextFormat::RED . '[WORLD] ERROR: The world you have chosen is not loaded or does not exist');
                self::createMainForm($player, true);
                return false;
            }
            $level = $player->getServer()->getLevelByName($data[1]);
            $player->getGame()->setLevel($level);
            $player->getGame()->getBorder()->setIterator($level);
            $player->getGame()->getSettings()->setAppleRate((int) $data[2]);
            self::setData($player, TextFormat::GREEN . '[WORLD] The world was configured successfully');
            self::createMainForm($player, true);
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'World settings');
        $form->addLabel(TextFormat::GRAY . 'If you dont know how to configure, please tell other staff');
        $form->addInput(TextFormat::WHITE . 'World name', TextFormat::GRAY . 'Put the name of the world', $player->getGame()->getLevel() != null ? self::getGame()->getLevel()->getFolderName() : null);
        $form->addSlider(TextFormat::WHITE . 'Apple rate', 1, 100, -1, $player->getGame()->getSettings()->getAppleRate());
        $form->sendToPlayer($player);
    }

    private static function createTimesConfiguration(GamePlayer $player): void
    {
        $form = new CustomForm(function (GamePlayer $player, array $data = null): bool {
            if ($data == null) {
                self::setData($player, TextFormat::RED . '[TIMES] ERROR: Data return null');
                self::createMainForm($player, true);
                return false;
            }

            if ($player->getGame()->getState() != GameState::WAITING) {
                self::setData($player, TextFormat::RED . '[TIMES] ERROR: You cant configure because the game started');
                self::createMainForm($player, true);
                return false;
            }
            $final_heal = (int) $data[1];
            $global_mute = (int) $data[2];
            $grace_period = (int) $data[3];

            if ($final_heal >= $grace_period) {
                self::setData($player, TextFormat::RED . '[TIMES] ERROR: The final heal time cannot be greater than or equal to the grace period');
                self::createMainForm($player, true);
                return false;
            }
            GameValues::$FINAL_HEAL = $final_heal;
            GameValues::$GLOBAL_MUTE = $global_mute;
            GameValues::$GRACE_PERIOD = $grace_period;
            self::setData($player, TextFormat::GREEN . '[TIMES] The times was configured successfully');
            self::createMainForm($player, true);
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Time settings');
        $form->addLabel(TextFormat::GRAY . 'If you dont know how to configure, please tell other staff');
        $form->addSlider(TextFormat::WHITE . 'Final heal', 60, 2400, -1, GameValues::$FINAL_HEAL);
        $form->addSlider(TextFormat::WHITE . 'GlobalMute', 60, 1500, -1, GameValues::$GLOBAL_MUTE);
        $form->addSlider(TextFormat::WHITE . 'Grace Period', 60, 2400, -1, GameValues::$GRACE_PERIOD);
        $form->sendToPlayer($player);
    }

    /**
     * @param GamePlayer $player
     */
    private static function createBorderConfiguration(GamePlayer $player): void
    {
        $form = new CustomForm(function (GamePlayer $player, array $data = null): bool {
            if ($data == null) {
                self::setData($player, TextFormat::RED . '[BORDER] ERROR: Data return null');
                self::createMainForm($player, true);
                return false;
            }

            if ($player->getGame()->getState() != GameState::WAITING) {
                self::setData($player, TextFormat::RED . '[BORDER] ERROR: You cant configure because the game started');
                self::createMainForm($player, true);
                return false;
            }
            $borderInitial = (int) $data[1];
            $dataBorder = explode('-', $data[2]);
            $borders = [];

            foreach ($dataBorder as $text) {
                $d = explode(':', $text);
                $borders[(int) $d[0]] = (int) $d[1];
            }

            if ($data[3] == true && $player->getGame()->getLevel() == null) {
                self::setData($player, TextFormat::RED . '[BORDER] Error: Please, to create the border set the name of the world');
                self::createMainForm($player, true);
                return false;
            }
            $player->getGame()->getBorder()->setSize($borderInitial);
            $player->getGame()->getBorder()->setBorders($borders);
            $player->getGame()->getBorder()->update();
            $player->getGame()->getBorder()->create();
            self::setData($player, TextFormat::GREEN . '[BORDER] The border was configured successfully');
            self::createMainForm($player, true);
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Border settings');
        $form->addLabel(TextFormat::GRAY . 'If you dont know how to configure, please tell other staff');
        $form->addInput(TextFormat::WHITE . 'Initial size', TextFormat::GRAY . '', '1000');
        $form->addInput(TextFormat::WHITE . 'Borders: ' . TextFormat::GRAY . 'TODO: In this option if you do not understand, please do not modify the text to avoid errors', TextFormat::GRAY . 'Put the corresponding format', '35:750-45:500-50:250-55:100-60:50-65:25');
        $form->addToggle(TextFormat::WHITE . 'Create border', false);
        $form->sendToPlayer($player);
    }

    /**
     * @param GamePlayer $player
     */
    private static function createTeamConfiguration(GamePlayer $player): void
    {
        $form = new CustomForm(function (GamePlayer $player, array $data = null): bool {
            if ($data == null) {
                self::setData($player, TextFormat::RED . '[TEAM] ERROR: Data return null');
                self::createMainForm($player, true);
                return false;
            }

            if ($player->getGame()->getState() != GameState::WAITING) {
                self::setData($player, TextFormat::RED . '[TEAMS] ERROR: You cant configure because the game started');
                self::createMainForm($player, true);
                return false;
            }
            $player->getGame()->setTeams($data[1]);
            $player->getGame()->getSettings()->setTeamDamage($data[2]);
            $player->getGame()->getSettings()->setMaxPlayers((int) $data[3]);
            $player->getGame()->getSettings()->setKeyboardPlayers((int) $data[4]);
            self::setData($player, TextFormat::GREEN . '[TEAM] The teams was configured successfully');
            self::createMainForm($player, true);
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Team settings');
        $form->addLabel(TextFormat::GRAY . 'If you dont know how to configure, please tell other staff');
        $form->addToggle(TextFormat::WHITE . 'Enabled', $player->getGame()->isTeams());
        $form->addToggle(TextFormat::WHITE . 'Damage team', $player->getGame()->getSettings()->isTeamDamage());
        $form->addSlider(TextFormat::WHITE . 'Maximum players', 2, 20, -1, $player->getGame()->getSettings()->getMaxPlayers());
        $form->addSlider(TextFormat::WHITE . 'Maximum players keyboard', 1, 20, -1, $player->getGame()->getSettings()->getKeyboardPlayers());
        $form->sendToPlayer($player);
    }

    /**
     * @param GamePlayer $player
     */
    private static function createScenarioConfiguration(GamePlayer $player): void
    {
        $form = new CustomForm(function (GamePlayer $player, array $data = null): bool {
            if ($data == null) {
                self::setData($player, TextFormat::RED . '[SCENARIOS] ERROR: Data return null');
                self::createMainForm($player, true);
                return false;
            }

            if ($player->getGame()->getState() != GameState::WAITING) {
                if (!$player->hasPermission('change.scenarios')) {
                    self::setData($player, TextFormat::RED . '[SCENARIOS] ERROR: You not have permissions for change scenarios in uhc running');
                    self::createMainForm($player, true);
                    return false;
                }
            }
            $scenarios = array_values($player->getGame()->getScenarios()->getScenarios());

            for ($i = 0; $i < count($scenarios); $i++) {
                $scenario = $scenarios[$i];
                assert($scenario instanceof Scenario);
                $boolean = $data[$i + 1];

                if ($boolean)
                    $player->getGame()->getScenarios()->addActive($scenario);
                else
                    $player->getGame()->getScenarios()->removeActive($scenario);
            }
            self::setData($player, TextFormat::GREEN . '[SCENARIOS] You have activated the following scenarios: ' . implode(', ', array_keys($player->getGame()->getScenarios()->getActives())));
            self::createMainForm($player, true);
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Scenarios settings');
        $form->addLabel(TextFormat::GRAY . 'If you dont know how to configure, please tell other staff');

        foreach ($player->getGame()->getScenarios()->getScenarios() as $scenario)
            $form->addToggle(TextFormat::WHITE . $scenario->getName(), ($player->getGame()->getScenarios()->isActive($scenario)));
        $form->sendToPlayer($player);
    }

    /**
     * @param GamePlayer $player
     */
    public static function listPlayers(GamePlayer $player): void
    {
        if (count($players = array_values($player->getGame()->getPlayers('alive') + $player->getGame()->getPlayers('offline'))) == 0) {
            $player->sendMessage(TextFormat::RED . 'There are no players');
            return;
        }
        $form = new SimpleForm(function (GamePlayer $player, int $data = null) use ($players): bool {
            if ($data === null)
                return false;

            if (!isset($players[$data])) {
                $player->sendMessage(TextFormat::RED . 'Failed to teleport. Try again');
                return false;
            }
            /** @var GamePlayer|DisconnectMob $target */
            $target = $players[$data];

            if ($target instanceof DisconnectMob && $target->getData() == null) {
                $player->sendMessage(TextFormat::RED . 'Failed to teleport. Try again');
                return false;
            }

            if ($target instanceof GamePlayer && (!$target->isSpawned() || !$target->getData()->isAlive())) {
                $player->sendMessage(TextFormat::RED . 'Failed to teleport. Try again');
                return false;
            }
            $player->teleport($target->asPosition());
            $player->sendMessage(TextFormat::GREEN . 'Teleport to ' . $target->getName(true));
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'List players');
        $form->setContent(TextFormat::GRAY . 'Pressure the player who wants to teleport');

        foreach ($players as $target)
            if ($target instanceof DisconnectMob)
                $form->addButton(TextFormat::WHITE . $target->getName() . ' (AFK)' . PHP_EOL . TextFormat::GRAY . 'Press to teleport');
            elseif ($target instanceof GamePlayer)
                $form->addButton(TextFormat::WHITE . $target->getName(true) . PHP_EOL . TextFormat::GRAY . 'Press to teleport');
        $form->sendToPlayer($player);
    }


    /**
     * @param GamePlayer $player
     * @param string $playerBan
     */
    public static function createBanForm(GamePlayer $player, string $playerBan): void
    {
        $config = new Config(AddonLoader::getInstance()->getDataFolder() . 'bans.yml', Config::YAML);
        $form = new CustomForm(function (GamePlayer $player, array $data = null) use ($playerBan, $config): bool {
            if ($data == null)
                return false;
            $reason = $data[2];

            if ($data[1] == 'permanent')
                $duration = 'permanent';
            else
                $duration = (int) $data[1];
            $config->set($playerBan, [
                'duration' => ($duration != 'permanent' ? (time() + ($duration * 86400)) : 'permanent'),
                'reason' => $reason,
                'staff' => $player->getName()
            ]);
            $config->save();
            GameFeed::sendPostBan($playerBan, $player->getName(), $reason, ($duration == 'permanent' ? 'permanently' : 'for ' . $duration . ' day(s)'));
            $player->getServer()->broadcastMessage(TextFormat::RED . $playerBan . TextFormat::GRAY . ' has been banned ' . ($duration == 'permanent' ? 'permanently' : 'for ' . $duration . ' day(s)') . ' by ' . TextFormat::RED . $player->getName() . '. ' . TextFormat::GRAY . 'Reason: ' . TextFormat::RED . $reason);

            if (($playerBan = $player->getServer()->getPlayer($playerBan)) instanceof GamePlayer)
                $playerBan->close('', TextFormat::RED . 'You were banned by ' . $reason . PHP_EOL . TextFormat::WHITE . 'Duration: ' . TextFormat::RED . ($duration == 'permanent' ? 'permanently' : $duration . ' day(s)'));
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Ban player');
        $form->addLabel(TextFormat::GRAY . "# Duration: You can place the days that the player will be banned or you can place 'permanent' if the player will be banned indefinitely " . PHP_EOL . '# Reason: You can indicate the reason why the player will be banned.');
        $form->addInput(TextFormat::WHITE . 'Duration', '', '1');
        $form->addInput(TextFormat::WHITE . 'Reason', '', 'Cheating');
        $form->sendToPlayer($player);
    }

    /**
     * @param GamePlayer $player
     */
    public static function createInformationForm(GamePlayer $player): void
    {
        $form = new SimpleForm(function (GamePlayer $player, int $data = null): bool {
            if ($data === null)
                return false;
            return true;
        });
        $form->setTitle('§6Game Configuration');
        $form->setContent('§6Information ' . PHP_EOL . '§7- Gamemode: §f' . ($player->getGame()->isTeams() ? 'Teams of ' . $player->getGame()->getSettings()->getMaxPlayers() : 'FFA') . PHP_EOL . '§7- Apple Rate: §f' . $player->getGame()->getSettings()->getAppleRate() . '% ' . PHP_EOL . PHP_EOL . '§6Thanks for playing on our server');
        $form->addButton('§7Exit');
        $form->sendToPlayer($player);
    }

    public static function createAnnounceForm(GamePlayer $player): void
    {
        $form = new CustomForm(function (GamePlayer $player, array $data = null): bool {
            if ($data === null)
                return false;
            GameFeed::sendPostGame((int) $data[1]);
            $player->sendMessage(TextFormat::GREEN . '[GAME ANNOUNCE] UHC published successfully.');
            return true;
        });
        $form->setTitle(TextFormat::LIGHT_PURPLE . 'Game announce');
        $form->addLabel(TextFormat::GRAY . 'Select the wait time to start the UHC');
        $form->addSlider(TextFormat::WHITE . 'Wait time', 1, 20, -1, 5);
        $form->sendToPlayer($player);
    }
}