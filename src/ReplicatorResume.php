<?php

declare(strict_types=1);

namespace ReplicationToKafka;

use MySQLReplication\BinLog\BinLogCurrent;

class ReplicatorResume
{
    private static ?string $fileAndPath = null;

    public static function save(BinLogCurrent $binLogCurrent): void
    {
        echo 'saving file:' . $binLogCurrent->getBinFileName() . ', position:' . $binLogCurrent->getBinLogPosition() . ' bin log position' . PHP_EOL;

        file_put_contents(self::getFileAndPath(), serialize($binLogCurrent), LOCK_EX);
    }

    private static function getFileAndPath(): string
    {
        if (null === self::$fileAndPath) {
            self::$fileAndPath = sys_get_temp_dir() . '/bin-log-replicator-last-position';
        }

        return self::$fileAndPath;
    }

    public static function startFromPosition(): ?BinLogCurrent
    {
        if (!is_file(self::getFileAndPath())) {
            return null;
        }

        /** @var BinLogCurrent $binLogCurrent */
        /** @noinspection UnserializeExploitsInspection */
        $binLogCurrent = unserialize(file_get_contents(self::getFileAndPath()));

        echo 'starting from file:' . $binLogCurrent->getBinFileName() . ', position:' . $binLogCurrent->getBinLogPosition() . ' bin log position' . PHP_EOL;

        return $binLogCurrent;
    }
}