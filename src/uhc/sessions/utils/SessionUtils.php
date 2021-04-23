<?php

declare(strict_types=1);

namespace uhc\sessions\utils;

use pocketmine\utils\TextFormat;
use ReflectionClass;

/**
 * Class SessionUtils
 * @package uhc\sessions\utils
 */
final class SessionUtils
{

    /** @var string[] */
    private static $colors = [];

    /**
     * @return string
     */
    public static function getRandomColor(): string
    {
        if (count(self::$colors) <= 0) {
            $disallowed = ['EOL', 'ESCAPE', 'OBFUSCATED', 'BOLD', 'STRIKETHROUGH', 'UNDERLINE', 'ITALIC', 'RESET', 'BLACK'];
            $class = new ReflectionClass(TextFormat::class);
            $colors = array_filter($class->getConstants(), function (string $name) use ($disallowed) {
                return !in_array($name, $disallowed);
            }, ARRAY_FILTER_USE_KEY);
        } else {
            $colors = self::$colors;
        }
        return ($colors[array_rand($colors)]);
    }
}