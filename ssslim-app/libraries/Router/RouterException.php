<?php
namespace Ssslim\Libraries\Router;

class RouterException extends \RuntimeException
{
    /**
     * The error code.
     *
     * @var integer
     */
    protected $code = 500;
}
