<?php

namespace ChannelEngine\Infrastructure\Serializer\Concrete;

use ChannelEngine\Infrastructure\Serializer\Serializer;
use Exception;

/**
 * Class NativeSerializer
 *
 * @package ChannelEngine\Infrastructure\Serializer\Concrete
 */
class NativeSerializer extends Serializer
{
    /**
     * Serializes data.
     *
     * @param mixed $data Data to be serialized.
     *
     * @return string String representation of the serialized data.
     */
    protected function doSerialize($data)
    {
        return serialize($data);
    }

    /**
     * Unserializes data.
     *
     * @param string $serialized Serialized data.
     *
     * @return mixed Unserialized data.
     */
    protected function doUnserialize($serialized)
    {
        try {
            $unserialized = unserialize($serialized);
        } catch (Exception $e) {
            return null;
        }

        return $unserialized;
    }
}