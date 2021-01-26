<?php

declare(strict_types=1);

namespace ReplicationToKafka;

use JetBrains\PhpStorm\Pure;

class Tools
{
    #[Pure]
    public static function getEnv(
        string $name,
        string $default = ''
    ): string {
        $env = getenv($name);
        if ($env === false) {
            return $default;
        }

        return (string)$env;
    }

    public static function decodeEnvs(string $env): array
    {
        return array_filter((array)explode(',', $env));
    }
}