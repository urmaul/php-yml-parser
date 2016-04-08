<?php

namespace Sirian\YMLParser\Exception;

use Exception;

class CurrencyNotFoundException extends YMLException
{
    public function __construct($message = "Currency not found", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
