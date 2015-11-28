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
     * Checks whether the item is a service that may be stored/retrieved/expected
     *
     * @param mixed  $item
     * @param string $context
     * @param null   $index
     * @throws InvalidCollectionContentException
     */
    protected function checkValidService($item, $context = null, $index = null)
    {
        if ( ! is_a($item, ServiceInterface::class)) {

            throw new InvalidCollectionContentException(
                'Invalid entry for ServiceCollection'
                . ( is_null($index) ?  null : " at index {$index}" )
                . ( $context ? ' ' . $context : null )
            );
        }
    }

    /**
     * @param ServiceInterface[] $items
     */
    public function __construct($items = [])
    {
        $items = is_array($items) ? $items : $this->getArrayableItems($items);

        foreach ($items as $index => $item) {

            $this->checkValidService($item);
        }

        parent::__construct($items);
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $default
     * @return ServiceInterface
     */
    public function get($key, $default = null)
    {
        return $this->offsetGet($key);
    }

    /**
     * Synonym for get
     *
     * @param string $name
     * @return ServiceInterface
     */
    public function service($name)
    {
        return $this->get($name);
    }

    /**
     * @param  mixed $key
     * @return ServiceInterface
     * @throws ServiceNotFoundInCollectionException
     */
    public function offsetGet($key)
    {
        if ( ! isset($this->items[$key])) {

            throw new ServiceNotFoundInCollectionException("Service with key '{$key}' not found in collection");
        }

        return parent::offsetGet($key);
    }

    /**
     * @param  mixed            $key
     * @param  ServiceInterface $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->checkValidService($value);

        parent::offsetSet($key, $value);
    }

}
