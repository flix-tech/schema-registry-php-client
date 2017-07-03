<?php

namespace FlixTech\SchemaRegistryApi;

trait HasPromisedProperties
{
    /**
     * @var \GuzzleHttp\Promise\PromiseInterface
     */
    protected $promise;

    protected function getPromisedProperty(string $property)
    {
        if ($this->{$property}) {
            return $this->{$property};
        }

        $this->wait();

        return $this->{$property};
    }

    public function wait()
    {
        return $this->promise->wait();
    }
}
