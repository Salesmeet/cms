<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


function load_class($class, $instantiate = TRUE)
{
    static $objects = array();

    // Does the class exist?  If so, we're done...
    if (isset($objects[$class])) {
        return $objects[$class];
    }

    if (file_exists(APPPATH . 'libraries/' . $class . EXT)) {
        require(APPPATH . 'libraries/' . $class . EXT);
    } else {
        require(BASEPATH . 'libraries/' . $class . EXT);
    }

    if ($instantiate == FALSE) {
        $objects[$class] = TRUE;
        return $objects[$class];
    }

    $name = ($class != 'Controller') ? 'CI_' . $class : $class;
    $ww = new $name();

    $objects[$class] = $ww;
    return $objects[$class];
}

/**
 * Loads the main config.php file
 *
 * @access    private
 * @return    array
 */
function &get_config()
{
    static $main_conf;

    if (!isset($main_conf)) {
        if (!file_exists(APPPATH . 'config/config' . EXT)) {
            exit('The configuration file config' . EXT . ' does not exist.');
        }

        require(APPPATH . 'config/config' . EXT);

        if (!isset($config) OR !is_array($config)) {
            exit('Your config file does not appear to be formatted correctly.');
        }

        $main_conf[0] =& $config;
    }
    return $main_conf[0];
}

/**
 * Error Handler
 *
 * This function lets us invoke the exception class and
 * display errors using the standard error template located
 * in application/errors/errors.php
 * This function will send the error page directly to the
 * browser and exit.
 *
 * @access    public
 * @return    void
 */
function show_error($message)
{
    /** @var \Ssslim\Libraries\Exceptions $exc */
    $exc = load_class('Loader')->getExceptions();
    echo $exc->show_error('An Error Was Encountered', $message);
    exit;
}


/**
 * 404 Page Handler
 *
 * This function is similar to the show_error() function above
 * However, instead of the standard error template it displays
 * 404 errors.
 *
 * @access    public
 * @return    void
 */
function show_404($page = '')
{
    /** @var \Ssslim\Core\Libraries\Exceptions $exc */
    $exc = load_class('Loader')->getExceptions();
    $exc->show_404($page);
    exit;
}

/*
 * ------------------------------------------------------
 *  Instantiate the base classes
 * ------------------------------------------------------
 */


/** @var CI_Loader $LOAD */
$LOAD = load_class('Loader');
spl_autoload_register([$LOAD, 'ssslim_autoloader'], true);

$EXC = $LOAD->getExceptions();
set_error_handler([$EXC, 'ssslim_exception_handler']);

$routing = $LOAD->getRouterImplementation();
$routing->doRoute();


/*
 * ------------------------------------------------------
 *  Close the DB connection if one exists
 * ------------------------------------------------------
 */
if (class_exists('CI_DB') AND isset($CI->db)) {
    $CI->db->close();
}

?>