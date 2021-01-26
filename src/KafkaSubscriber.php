<?php

declare(strict_types=1);

namespace ReplicationToKafka;

use JsonException;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\EventSubscribers;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use RuntimeException;

class KafkaSubscriber extends EventSubscribers
{
    private Producer $producer;
    private ProducerTopic $topic;

    public function __construct()
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', Tools::getEnv('KAFKA_BROKER', 'r2k-kafka:9092'));

        if (Tools::getEnv('KAFKA_DEBUG', 'true') === 'true') {
            $conf->set('log_level', (string)LOG_DEBUG);
            $conf->set('debug', 'broker,topic,msg');
        }

        $this->producer = new Producer($conf);
        $this->topic = $this->producer->newTopic(Tools::getEnv('KAFKA_TOPIC', 'test'));
    }

    /**
     * @throws JsonException
     */
    public function allEvents(EventDTO $event): void
    {
        if (Tools::getEnv('REPLICATOR_DEBUG', 'true') === 'true') {
            echo $event;
        }

        $this->produceToKafka($event);
    }

    /**
     * @throws JsonException
     */
    protected function produceToKafka(EventDTO $event): void
    {
        $this->topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            json_encode($event, JSON_THROW_ON_ERROR),
            $event->getType()
        );

        $this->producer->poll(0);

        $result = 0;
        for ($flushRetries = 0; $flushRetries < (int)Tools::getEnv('KAFKA_RETRIES', '3'); $flushRetries++) {
            $result = $this->producer->flush((int)Tools::getEnv('KAFKA_TIMEOUT', '10000'));
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new RuntimeException('Unable to produce message to kafka', $result);
        }

        ReplicatorResume::save($event->getEventInfo()->getBinLogCurrent());
    }
}
