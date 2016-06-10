<?php

namespace Maikuro\DistributedConfigurationBundle\Model;

use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * Class KeyValue.
 *
 * @ExclusionPolicy("all")
 * @AccessorOrder("custom", custom = {"key", "value"})
 */
class KeyValue
{
    /**
     * $key.
     *
     * @var string
     * @Expose
     */
    private $key;

    /**
     * $value.
     *
     * @var string
     * @Expose
     */
    private $value;

    /**
     * setKey.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * setValue.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * getValue.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * getKey.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}
