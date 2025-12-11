<?php

declare(strict_types=1);

namespace Marshal\Application;

final class Config
{
    private static bool $isInitialized = false;
    private static array $config = [];

    private function __construct()
    {
    }

    private function __clone(): void
    {
    }

    public static function has(string $key): bool
    {
        return isset(self::$config[$key]);
    }

    public static function get(string $key): mixed
    {
        if (! self::has($key)) {
            throw new \InvalidArgumentException("Config has no $key set");
        }

        return self::$config[$key];
    }

    public static function initialize(array $config): void
    {
        if (true === self::$isInitialized) {
            throw new \RuntimeException("Config already initialized");
        }

        self::$config = $config;
        self::$isInitialized = true;
    }
}
