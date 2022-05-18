<?php

namespace Czim\Service\Collections;

use Czim\Service\Contracts\ServiceCollectionInterface;
use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Exceptions\InvalidCollectionContentException;
use Czim\Service\Exceptions\ServiceNotFoundInCollectionException;
use Illuminate\Support\Collection;

/**
 * Simple extension of the Collection object that is set up to only allow
 * ServiceInterface entries.
 */
class ServiceCollection extends Collection implements ServiceCollectionInterface
{
    /**
     * @param ServiceInterface[] $items
     */
    public function __construct($items = [])
    {
        $items = is_array($items) ? $items : $this->getArrayableItems($items);

        foreach ($items as $item) {
            $this->checkValidService($item);
        }

        parent::__construct($items);
    }

    /**
     * @param mixed  $key
     * @param mixed  $default
     * @return ServiceInterface
     */
    public function get($key, $default = null): ServiceInterface
    {
        return $this->offsetGet($key);
    }

    /**
     * @param string $name
     * @return ServiceInterface
     */
    public function service(string $name): ServiceInterface
    {
        return $this->get($name);
    }

    /**
     * @param mixed $key
     * @return ServiceInterface
     * @throws ServiceNotFoundInCollectionException
     */
    public function offsetGet($key): ServiceInterface
    {
        if (! isset($this->items[$key])) {
            throw new ServiceNotFoundInCollectionException(
                "Service with key '{$key}' not found in collection"
            );
        }

        return parent::offsetGet($key);
    }

    /**
     * @param mixed            $key
     * @param ServiceInterface $value
     */
    public function offsetSet($key, $value): void
    {
        $this->checkValidService($value);

        parent::offsetSet($key, $value);
    }

    /**
     * Checks whether the item is a service that may be stored/retrieved/expected.
     *
     * @param mixed       $item
     * @param string|null $context
     * @param int|null    $index
     * @throws InvalidCollectionContentException
     */
    protected function checkValidService($item, ?string $context = null, ?int $index = null)
    {
        if (! $item instanceof ServiceInterface) {
            throw new InvalidCollectionContentException(
                'Invalid entry for ServiceCollection'
                . ($index === null ?  null : " at index {$index}" )
                . ($context ? ' ' . $context : null)
            );
        }
    }
}
