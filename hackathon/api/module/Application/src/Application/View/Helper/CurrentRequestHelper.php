<?php
/**
 * 
 */
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CurrentRequestHelper extends AbstractHelper
{
    /**
     * Current Request parameters
     *
     * @access protected
     * @var array
     */
    protected $params;

    /**
     * Current module name.
     *
     * @access protected
     * @var string
     */
    protected $moduleName;

    /**
     * Current controller name.
     *
     * @access protected
     * @var string
     */
    protected $controllerName;

    /**
     * Current action name.
     *
     * @access protected
     * @var string
     */
    protected $actionName;

    /**
     * Current route name.
     *
     * @access protected
     * @var string
     */
    protected $routeName;

    /**
     * Parse request and substitute values in corresponding properties.
     */
    public function __invoke()
    {
        $this->params = $this->initialize();
        return $this;
    }

    /**
     * Initialize and extract parameters from current request.
     *
     * @access protected
     * @return $params array
     */
    protected function initialize()
    {
        $sm = $this->getView()->getHelperPluginManager()->getServiceLocator();
        $router = $sm->get('router');
        $request = $sm->get('request');
        $matchedRoute = $router->match($request);
        $params = $matchedRoute->getParams();
        /**
         * Controller are defined in two patterns.
         * 1. With Namespace
         * 2. Without Namespace.
         * Concatenate Namespace for controller without it.
         */
        $this->controllerName = strpos($params['controller'], '\\') ?
            $params['__NAMESPACE__'].'\\'.$params['controller'] :
            $params['controller'];
        $this->actionName = $params['action'];
        /**
         * Extract Module name from current controller name.
         * First camel cased character are assumed to be module name.
         */
        $this->moduleName = substr($this->controllerName, 0, strpos($this->controllerName, '\\'));
        $this->routeName = $matchedRoute->getMatchedRouteName();
        return $params;
    }

    /**
     * Return module, controller, action or route name.
     *
     * @access public
     * @return $result string.
     */
    public function get($type)
    {
        $type = strtolower($type);
        $result = false;
        switch ($type) {
            case 'module':
                    $result = $this->moduleName;
                break;
            case 'controller':
                    $result = $this->controllerName;
                break;
            case 'action':
                    $result = $this->actionName;
                break;
            case 'route':
                    $result = $this->routeName;
                break;
                
           	case 'params':
            	$result = $this->params;
            	break;
        }
        return $result;
    }
}
