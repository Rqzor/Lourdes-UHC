<?php

declare(strict_types=1);

namespace uhc\task\utils;

/**
 * Class GameTaskUtils
 * @package uhc\task\utils
 */
final class GameTaskUtils
{

    /**
     * @param int $secs
     * @return int
     */
    public static function secondsToTicks(int $secs): int
    {
        return $secs * 20;
    }

    /**
     * @param int $mins
     * @return int
     */
    public static function minutesToTicks(int $mins): int
    {
        return $mins * 1200;
    }
}