<?php

declare(strict_types=1);

namespace uhc\game\utils;

/**
 * Class GameValues
 * @package uhc\game\utils
 */
class GameValues
{

    /** @var int */
    public static $STARTING;
    /** @var int */
    public static $RUNNING = 0;

    /** @var int */
    public static $FINAL_HEAL;
    /** @var int */
    public static $GLOBAL_MUTE;
    /** @var int */
    public static $GRACE_PERIOD;

    /**
     * GameValues constructor.
     * @param array $times
     */
    public function __construct(array $times)
    {
        self::$STARTING = $times['starting'];

        self::$FINAL_HEAL = $times['final-heal'];
        self::$GLOBAL_MUTE = $times['global-mute'];
        self::$GRACE_PERIOD = $times['grace-period'];
    }
}