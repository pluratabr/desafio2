<?php
/**
 * 
 */
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use Zend\Authentication\AuthenticationService;
use Application\Controller\Plugin\Authentication;
use Application\Model\FileModel;
use Application\Model\PageModel;
use Zend\Session\Container;
use Application\Model\UserModel;
use Zend\View\ViewEvent;
use Zend\View\Renderer\PhpRenderer;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions;
use Application\Controller\Plugin\NotificationPlugin;
use Application\Model\DefaultModel;
use Application\Controller\Plugin\TimeElapsed;
use Application\Authentication\Event;
use Application\Controller\Plugin\ScriptExec;
use Application\View\Helper\ImgLattesHelper;
use Application\Controller\Plugin\LogPlugin;

use SlmQueue\Worker\WorkerEvent;
use SlmQueue\Worker\WorkerEventInterface;

class Module
{
    protected $runCount;

    public function onBootstrap(MvcEvent $e)
    {
        
        $app = $e->getApplication();
        $sm  = $app->getServiceManager();
        $eventManager  = $app->getEventManager();
        $defaultLocale = null;
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    	    $defaultLocale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    	    $defaultLocale = strpos($defaultLocale,'_') !== false ? $defaultLocale : str_replace('-', '_', $defaultLocale);
            $session = new Container('locale');
            if (!$session->offsetExists('lang')) {
                $session->offsetSet('lang', $defaultLocale);
            }
            
            $translator = $sm->get('translator');
            $translator->setLocale($session->offsetGet('lang'))
            ->setFallbackLocale($defaultLocale);
            
            $locale = $translator->getLocale();    
            /**
             * @TODO Remover esse hack logo após resolver o sistema de tradução.
             * 
            */
            $locale = 'pt_BR';
            //var_dump($locale); exit;

            $moduleRouteListener = new ModuleRouteListener();
            $moduleRouteListener->attach($eventManager);
            
        }
    }  

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getControllerPluginConfig()
    {
        return array(
        	'invokables' => array(
        		
        	),
            'factories' => array(
                'ScriptExecPlugin' => function ($sm) {
		            $sePlugin = new ScriptExec();
		            $sePlugin->setServiceLocator($sm->getServiceLocator());
		            return $sePlugin;
	            }
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'defaultModel' => 'Application\Model\DefaultModel',
            ),
            'factories' => array(
                
                'Application\Model\DefaultModel' => function ($sm) {
               	 	$dm = new DefaultModel();
               	 	$dm->setEntityManager($sm->get('Doctrine\ORM\EntityManager'));
                	return $dm;
                },
                
            ),
        );
    }
    
    public function getViewHelperConfig()
    {
    	return array(
    			'invokables' => array(
    					'current_request' => new \Application\View\Helper\CurrentRequestHelper(),
    			)
        );
        
    }
}

