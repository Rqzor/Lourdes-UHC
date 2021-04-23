<?php

declare(strict_types=1);

namespace uhc\game\utils;

/**
 * Class GameState
 * @package uhc\game\utils
 */
final class GameState
{

    /** @var int */
    public const WAITING = 0;
    /** @var int */
    public const SETUP = 1;
    /** @var int */
    public const STARTING = 2;
    /** @var int */
    public const RUNNING = 3;
    /** @var int */
    public const RESTARTING = 4;
}