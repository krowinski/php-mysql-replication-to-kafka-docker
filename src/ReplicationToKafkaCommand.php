<?php

declare(strict_types=1);

namespace ReplicationToKafka;

use Exception;
use MySQLReplication\Config\ConfigBuilder;
use MySQLReplication\MySQLReplicationFactory;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplicationToKafkaCommand extends Command
{
    protected static $defaultName = 'app:r2k';

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $binlog = ReplicatorResume::startFromPosition();

        $defaultFileName = '';
        $defaultFilePosition = 0;
        $binLogStartFromFileName = Tools::getEnv('REPLICATOR_START_BIN_LOG_FILE_NAME', $defaultFileName);
        $binLogStartFromPosition = (int)Tools::getEnv('REPLICATOR_START_BIN_LOG_POSITION', (string)$defaultFilePosition);
        if ($binlog !== null && $defaultFileName !== $binLogStartFromFileName && $defaultFilePosition !== $binLogStartFromPosition) {
            $binLogStartFromFileName = $binlog->getBinFileName();
            $binLogStartFromPosition = $binlog->getBinLogPosition();
        }

        $configBuilder = (new ConfigBuilder())
            ->withUser(Tools::getEnv('REPLICATOR_USER', 'root'))
            ->withHost(Tools::getEnv('REPLICATOR_HOST', 'r2k-mysql'))
            ->withPassword(Tools::getEnv('REPLICATOR_PASSWORD', 'root'))
            ->withPort((int)Tools::getEnv('REPLICATOR_PORT', '3306'))
            ->withSlaveId((int)Tools::getEnv('REPLICATOR_SLAVE_ID', '100'))
            ->withGtid(Tools::getEnv('REPLICATOR_START_FROM_GTID', ''))
            ->withBinLogFileName($binLogStartFromFileName)
            ->withBinLogPosition($binLogStartFromPosition)
            ->withMariaDbGtid(Tools::getEnv('REPLICATOR_START_FROM_MARIA_DB_GTID', ''))
            ->withTableCacheSize((int)Tools::getEnv('REPLICATOR_TABLE_CACHE', '128'))
            ->withHeartbeatPeriod((float)Tools::getEnv('REPLICATOR_HEARTBEAT', '0.0'))
            ->withTablesOnly(Tools::decodeEnvs(Tools::getEnv('REPLICATOR_LISTEN_ON_TABLES', '')))
            ->withDatabasesOnly(Tools::decodeEnvs(Tools::getEnv('REPLICATOR_LISTEN_ON_DATABASES', '')))
            ->withEventsOnly(Tools::decodeEnvs(Tools::getEnv('REPLICATOR_LISTEN_EVENT_IDS', '')))
            ->withEventsIgnore(Tools::decodeEnvs(Tools::getEnv('REPLICATOR_IGNORE_EVENT_IDS', '')));

        $binLogStream = new MySQLReplicationFactory($configBuilder->build());
        $binLogStream->registerSubscriber(new KafkaSubscriber());
        $binLogStream->run();

        return Command::SUCCESS;
    }
}