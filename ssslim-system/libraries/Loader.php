<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Code Igniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, pMachine, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Loader Class
 *
 * Loads views and files
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		Rick Ellis
 * @category	Loader
 * @link		http://www.codeigniter.com/user_guide/libraries/loader.html
 */

class CI_Loader {

	// All these are set automatically. Don't mess with them.
	var $_ci_ob_level;
	var $_ci_view_path		= '';
	var $_ci_cached_vars	= array();
	var $_ci_classes		= array();
	var $_ci_helpers		= array();
	var $_ci_varmap			= array('unit_test' => 'unit', 'user_agent' => 'agent');
	var $CI;
	
	private $_class_cache = array();


	/**
	 * Constructor
	 *
	 * Sets the path to the view files and gets the initial output buffering level
	 *
	 * @access	public
	 */
	function __construct()
	{
//		$this->CI = &get_instance();
		$this->_ci_view_path = APPPATH.'views/';
		$this->_ci_ob_level  = ob_get_level();
				
	}

    public function ssslim_autoloader($class)
    {
        $map = ['Ssslim/Core/Libraries/' => BASEPATH . 'libraries/', 'Ssslim/Libraries/' => APPPATH . 'libraries/', 'Ssslim/Controllers/' => APPPATH . 'controllers/' ];

        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . EXT;

        foreach ($map as $pfx => $basePath) {

            if (0 === strpos($logicalPathPsr4, $pfx)) {
                $classFile = $basePath . substr($logicalPathPsr4, strlen($pfx) - 1);
                include($classFile);
                return true;
            }
        }
        return false;
    }

	// --------------------------------------------------------------------
	
	/**
	 * Loads a config file
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE)
	{			
		$CI =& get_instance();
		$CI->config->load($file, $use_sections, $fail_gracefully);
	}

	// --------------------------------------------------------------------


    /**
     * @param $view
     * @param array $vars
     * @param bool $return
     * @return String
     */

	function view($view, $vars = array(), $return = FALSE)
	{

		// Set the path to the requested file
        $ext = pathinfo($view, PATHINFO_EXTENSION);
        $file = ($ext == '') ? $view.EXT : $view;
        $path = $this->_ci_view_path.$file;

		if ( ! file_exists($path))	show_error('Unable to load the requested file: '.$file);

		if (is_array($vars))extract($vars);

		ob_start();
				
		// If the PHP installation does not support short tags we'll
		// do a little string replacement, changing the short tags
		// to standard PHP echo statements.
	
		include($path);

        $buffer = ob_get_contents();
        @ob_end_clean();

		// Return the file data if requested
		if ($return === TRUE) return $buffer;
        else echo $buffer;
	}

	// --------------------------------------------------------------------
	
	function getDB($config = '') {
		
		$key = "db_$config"; // for the singleton cache (only 1 connection per DB group)
		if (!empty($this->_class_cache[$key])) return $this->_class_cache[$key];

		include(APPPATH.'config/database'.EXT);
		$group = ($config == '') ? $active_group : $config;

		if ( ! isset($db[$group]))	show_error('You have specified an invalid database connection group: '.$group);
		$params = $db[$group];

		require_once(BASEPATH.'database/DB_driver'.EXT);
		require_once(BASEPATH.'database/drivers/mysqli/mysqli_driver'.EXT);

		return $this->_class_cache[$key] = new DB($params, $this->getLogger());
	}

	function getRouterImplementation() {
		if (!empty($this->_class_cache['RouterImplementation'])) return $this->_class_cache['RouterImplementation'];
		return $this->_class_cache['RouterImplementation'] = new \Ssslim\Libraries\Router\Implementation($this);
	}

	function getConfig() {
		if (!empty($this->_class_cache['Config'])) return $this->_class_cache['Config'];
		return $this->_class_cache['Config'] = new \Ssslim\Core\Libraries\Config();
	}

    function getLogger() {
        if (!empty($this->_class_cache['Logger'])) return $this->_class_cache['Logger'];
        return $this->_class_cache['Logger'] = new \Ssslim\Core\Libraries\Logger($this->getConfig());
    }

    function getExceptions() {
        if (!empty($this->_class_cache['Exceptions'])) return $this->_class_cache['Exceptions'];
        return $this->_class_cache['Exceptions'] = new \Ssslim\Core\Libraries\Exceptions($this->getLogger());
    }

    function getToken() {
		if (!empty($this->_class_cache['Token'])) return $this->_class_cache['Token'];
		return $this->_class_cache['Token'] = new \Ssslim\Libraries\Token();
	}

	function getNetwork() {
		if (!empty($this->_class_cache['Network'])) return $this->_class_cache['Network'];
		return $this->_class_cache['Network'] = new \Ssslim\Libraries\Network();
	}


	function getCacheFactory() {
		if (!empty($this->_class_cache['CacheFactory'])) return $this->_class_cache['CacheFactory'];
		return $this->_class_cache['CacheFactory'] = new \Ssslim\Libraries\Cache\CacheFactory($this->getLogger());
	}

	function getUserFactory() {
		if (!empty($this->_class_cache['UserFactory'])) return $this->_class_cache['UserFactory'];
		return $this->_class_cache['UserFactory'] = new \Ssslim\Libraries\User\UserFactory($this->getLogger(), $this->getDB(), $this->getCacheFactory(), $this->getToken());
	}

	function getAppCore() {
		if (!empty($this->_class_cache['AppCore'])) return $this->_class_cache['AppCore'];
		return $this->_class_cache['AppCore'] = new \Ssslim\Libraries\AppCore($this->getLogger(), $this->getDB(), $this, $this->getCacheFactory(), $this->getUserFactory(), $this->getToken());
	}

	function getLeadsManager() {
		if (!empty($this->_class_cache['LeadsManager'])) return $this->_class_cache['LeadsManager'];
		return $this->_class_cache['LeadsManager'] = new \Ssslim\Libraries\LeadsManager($this->getLogger(), $this->getDB(), $this->getCacheFactory());
	}

	function getForms(){
		if (!empty($this->_class_cache['Forms'])) return $this->_class_cache['Forms'];
		return $this->_class_cache['Forms'] = new \Ssslim\Libraries\Forms($this->getLogger());
	}

	function getMailManager(){
		if (!empty($this->_class_cache['MailManager'])) return $this->_class_cache['MailManager'];
		return $this->_class_cache['MailManager'] = new \Ssslim\Libraries\MailManager($this->getLogger(), $this, $this->getDB(), $this->getUserFactory());
	}

	function getPagination(){
		if (!empty($this->_class_cache['Pagination'])) return $this->_class_cache['Pagination'];
		return $this->_class_cache['Pagination'] = new \Ssslim\Libraries\Pagination($this);
	}

}
?>