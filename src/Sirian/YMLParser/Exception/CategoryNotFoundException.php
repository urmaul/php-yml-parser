<?php

namespace Sirian\YMLParser\Exception;

use Exception;

class CategoryNotFoundException extends YMLException
{
    public function __construct($message = "Category not found", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
