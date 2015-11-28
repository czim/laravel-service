<?php
namespace Czim\Service\Contracts;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

interface ServiceCollectionInterface extends ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{

    /**
     * Synonym for get
     *
     * @param string $name
     * @return ServiceInterface
     */
    public function service($name);

}
