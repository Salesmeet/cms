<?php
/**
 * Created by IntelliJ IDEA.
 * User: vds
 * Date: 11/07/2016
 * Time: 09.31
 */

namespace Ssslim\Libraries\Router {

    use Ssslim\Controllers\Admin;
    use Ssslim\Controllers\Auth;
    use Ssslim\Controllers\PublicSite;

    class Implementation
    {
        private $router;
        private $load;
        /** @var  Route */
        private $route;

        public function __construct(\CI_Loader $load)
        {
            $this->load = $load;
        }

        private function standardMethodInvoke($controllerInstance, $args)
        {
            $segs = explode('/', trim($args, '/'));

            if (!method_exists($controllerInstance, $segs[0])) throw(new RouterException());
            else return call_user_func_array([$controllerInstance, $segs[0]], array_slice($segs, 1));
        }

        public function doRoute()
        {
            $this->router = new Router();
            $this->router->basePath("/salesmeet_cms/");

            $this->router->bind("/", function ($route) {
                $c = new PublicSite($this->load->getAppCore(), $this->load->getLeadsManager(), $this->load->getUserFactory(), $this->load->getForms(), $this->load, $this->load->getMailManager(), $this->load->getPagination());
                $c->home();
            });

            $this->router->bind("clear-cookie", function ($route) {
                $c = new PublicSite($this->load->getAppCore(), $this->load->getLeadsManager(), $this->load->getUserFactory(), $this->load->getForms(), $this->load, $this->load->getMailManager(), $this->load->getPagination());
                $c->clearCookie();
            });

            $this->router->bind("privacy", function ($route) {
                $c = new PublicSite($this->load->getAppCore(), $this->load->getLeadsManager(), $this->load->getUserFactory(), $this->load->getForms(), $this->load, $this->load->getMailManager(), $this->load->getPagination());
                $c->privacy();
            });

//            $this->router->bind("/dashboard[/{args:.*}]", function ($route) {
//                $c = new Admin($this->load->getAppCore(), $this->load->getLeadsManager(), $this->load->getUserFactory(), $this->load->getForms(), $this->load, $this->load->getMailManager(), $this->load->getPagination());
//                $this->standardMethodInvoke($c, $route->params['args'] ?: 'dashboard');
//            });
            
            $this->router->bind("/auth[/{args:.*}]", function ($route) {
                $c = new Auth($this->load->getAppCore(), $this->load->getLeadsManager(), $this->load->getUserFactory(), $this->load->getToken(), $this->load->getForms(), $this->load, $this->load->getMailManager(), $this->load->getPagination());
                $this->standardMethodInvoke($c, $route->params['args'] ?: 'dashboard');
            });



            // command line execution
            if (isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])) {
                $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; // CLI, fake server address
                $args = $_SERVER['argv'];
                $this->route = $this->router->route('/'.implode('/', array_slice($args, 1)), 'GET');
            }
            else $this->route = $this->router->route($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']); // web server execution

            try {
                $this->route->dispatch();
            } catch (\Exception $e) {
                show_404();
            }
        }
    }
}

namespace {
    // HELPER FUNCTIONS

    function site_url($uri = '')
    {
        return load_class('Loader')->getConfig()->site_url($uri);
    }

// ------------------------------------------------------------------------

    function base_url()
    {
        return load_class('Loader')->getConfig()->slash_item('base_url');
    }
}
