<?php

declare(strict_types=1);

namespace Czim\Service\Collections;

use Czim\Service\Contracts\ServiceCollectionInterface;
use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Exceptions\InvalidCollectionContentException;
use Czim\Service\Exceptions\ServiceNotFoundInCollectionException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Simple extension of the Collection object that is set up to only allow ServiceInterface entries.
 */
class ServiceCollection extends Collection implements ServiceCollectionInterface
{
    /**
     * @param ServiceInterface[] $items
     */
    public function __construct(array|Arrayable $items = [])
    {
        if ($items instanceof Arrayable) {
            $items = $this->getArrayableItems($items);
        }

        foreach ($items as $item) {
            $this->assertValidService($item);
        }

        parent::__construct($items);
    }

    public function get(mixed $key, mixed $default = null): ServiceInterface
    {
        return $this->offsetGet($key);
    }

    public function service(string $name): ServiceInterface
    {
        return $this->get($name);
    }

    /**
     * @param mixed $key
     * @return ServiceInterface
     * @throws ServiceNotFoundInCollectionException
     */
    public function offsetGet(mixed $key): ServiceInterface
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
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->assertValidService($value);

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
    protected function assertValidService(mixed $item, ?string $context = null, ?int $index = null): void
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
