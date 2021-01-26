# php-mysql-replication-to-kafka-docker

Moves mysql replication events to kafka docker.

Env:

### `REPLICATOR_DEBUG: "false"`

print worker action to log

### `REPLICATOR_USER: "root"`

your mysql user (mandatory)

### `REPLICATOR_HOST: "r2k-mysql"`

your mysql host/ip (mandatory)

### `REPLICATOR_PASSWORD: "root"`

your mysql password (mandatory)

### `REPLICATOR_PORT: "3306"`

our mysql host port (default 3306)

### `REPLICATOR_SLAVE_ID: "100"`

script slave id for identification (SHOW SLAVE HOSTS)

### `REPLICATOR_START_FROM_GTID: ""`

GTID marker(s) to start from

### `REPLICATOR_START_BIN_LOG_FILE_NAME: ""`

bin log file name to start from

### `REPLICATOR_START_BIN_LOG_POSITION: "0"`

bin log position to start from

### `REPLICATOR_START_FROM_MARIA_DB_GTID: ""`

MariaDB GTID marker(s) to start from

### `REPLICATOR_TABLE_CACHE: "1024"`

some data are collected from information schema, this data is cached.

### `REPLICATOR_HEARTBEAT: "0.0"`

sets the interval in seconds between replication heartbeats.

### `REPLICATOR_LISTEN_ON_TABLES: "foo,bar"`

only listen on given tables (default all tables)

### `REPLICATOR_LISTEN_ON_DATABASES: "foo,bar"`

only listen on given databases (default all tables)

### `REPLICATOR_LISTEN_EVENT_IDS: "73,32"

only listen for given events, look for ids in MySQLReplication\Definitions\ConstEventType::class`

### `REPLICATOR_IGNORE_EVENT_IDS: "73,32"`

ignore given events, look for ids in MySQLReplication\Definitions\ConstEventType::class`

### `KAFKA_DEBUG: "false"`

shows rdkafka events

### `KAFKA_BROKER: "r2k-kafka:9092"`

brokers host:port

### `KAFKA_TOPIC: "test"`

topic name to publish mysql events

### `KAFKA_RETRIES: "3"`

how many tries worker should have before giving up and throw exception

### `KAFKA_TIMEOUT: "10000"`

rdkafa flush timeout ms

Also, you can look and docker-compose.yml in repo.