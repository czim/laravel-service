<?php

namespace Czim\Service\Contracts;

use ArrayAccess;
use Countable;
use Czim\Service\Collections\ServiceCollection;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * @mixin ServiceCollection
 */
interface ServiceCollectionInterface extends ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    /**
     * @param string $name
     * @return ServiceInterface
     */
    public function service(string $name): ServiceInterface;
}
