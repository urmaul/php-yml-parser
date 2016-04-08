<?php

namespace Sirian\YMLParser\Exception;

use Exception;

class FileNotFoundException extends YMLException
{
    public function __construct($message = "File not found", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
