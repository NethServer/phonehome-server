<?php

namespace App\Exceptions;

use LogicException;

/**
 * @codeCoverageIgnore
 */
class NotImplemented extends LogicException
{
    public function __construct()
    {
        parent::__construct('Not Implemented');
    }
}
