<?php

namespace Maikuro\DistributedConfigurationBundle\Handler;

use Maikuro\DistributedConfigurationBundle\Model\KeyValue;
use Webmozart\KeyValueStore\Api\KeyValueStore;

/**
 * Class StoreHandler.
 */
class StoreHandler
{
    /**
     * $store.
     *
     * @var KeyValueStore
     */
    private $store;

    /**
     * Constructor.
     *
     * @param KeyValueStore $store
     */
    public function __construct(KeyValueStore $store)
    {
        $this->store = $store;
    }

    /**
     * get.
     *
     * @param string $key
     * @throw Webmozart\KeyValueStore\Api\NoSuchKeyException
     *
     * @return KeyValue
     */
    public function get($key)
    {
        $value = $this->store->getOrFail($key);

        $keyValue = new KeyValue();
        $keyValue->setKey($key);
        $keyValue->setValue($value);

        return $keyValue;
    }

    /**
     * remove.
     *
     * @param string $key
     * @throw Webmozart\KeyValueStore\Api\WriteException
     */
    public function remove($key)
    {
        $this->store->remove($key);
    }

    /**
     * flush.
     *
     * @param KeyValue $keyValue
     *
     * @throws \Exception
     */
    public function flush(KeyValue $keyValue)
    {
        $this->store->set($keyValue->getKey(), $keyValue->getValue());
    }
}
