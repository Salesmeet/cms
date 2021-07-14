<?php
namespace Ssslim\Libraries\Router;

class DispatchingException extends \RuntimeException
{
    /**
     * The error code.
     *
     * @var integer
     */
    protected $code = 500;
}
