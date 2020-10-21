<?php
/**
 * This file is part of Agora_analitics.
 *
 * (c) Bruno Santos Ferreira <brunosfweb@gmail.com>
 *     
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;


class ApplicationController extends AbstractActionController
{
	/**
	 * @var Application\Model\DefaultModel
	 */
	private $model;
	
	protected function getModel()
	{
		if (!$this->model) {
			$this->model = $this->getServiceLocator()->get('Application\Model\DefaultModel');
		}

		return $this->model;
	}
	
    public function localeAction()
    {
    	$request = $this->getRequest();
    	if ($request->isPost()) {
            $lang = $request->getPost('lang');
            $session = new Container('locale');
        	$session->offsetSet('lang', $lang);
        	return new JsonModel(
	            array(
	                'data' => substr($lang, strpos($lang, '_') + 1, strlen($lang))
	            )
        	);
    	}
    }
}
