<?php

declare(strict_types=1);

namespace Phel\Lang;

interface RestInterface
{
    /**
     * Return the sequence without the first element. If the sequence is empty returns an empty sequence.
     */
    public function rest(): RestInterface;
}
