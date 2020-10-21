<?php
/**
 *     
 */
namespace Application\Controller;

use Zend\View\Model\JsonModel;
use Application\Controller\Plugin\ScriptExec;
use Zend\Json\Json;


class ApiController extends ApplicationController
{
	
	public function indexAction()
    {

		return new JsonModel(
    		array(
				'Hackathon CNJ'
    		)
    	);
	} 
	public function clusterAction()
    {

		$path = getcwd() . '/data/analyses/';
		$scriptPath = getcwd(). '/data/R_script';

		$clusterFile = $path . '/cluster.json';
		$endogramaFile = $path . '/cluster.json';
		$endograma = Json::encode($endogramaFile);

		$orgão = 'TRE';

		//inicia o script da analise
		$command = "/usr/local/bin/Rscript {$scriptPath}/App.R -'{$orgão}'";
        		 
		//	$command = 'nohup '.$command.' > /dev/null 2> &1 & echo $!';
		
		//escreve arquivo de log para a análise
		$logFile = $scriptPath . '/cluster.log';
		file_put_contents($logFile, '');
		chmod($logFile, 0777);
		 
		$plugin = new ScriptExec($command, $logFile);
		 
		$pid = $plugin->getPid();

		return new JsonModel(
    		array(
				'PID DA EXECUÇÃO DO CLUSTER' => $plugin->getPid()
    		)
    	);
	} 

	public function endogramaAction()
    {

		return new JsonModel(
    		array(
				'endograma'
    		)
    	);
    } 
}
