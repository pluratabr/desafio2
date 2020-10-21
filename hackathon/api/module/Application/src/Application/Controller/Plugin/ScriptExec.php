<?php
/**
 * 
 */
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

class ScriptExec extends AbstractPlugin
{
    private $pid;
    private $command;

	/**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    
    public function __construct($cl=false, $logFile = false){
    	if ($cl != false){
    		$this->command = $cl;
    		
    		$this->logFile = '/var/log/ellates.log';
    		
    		if($logFile)
    			$this->logFile = $logFile;
    		
    		$this->runCom();
    	}
    }
    
    private function runCom(){
    	$command = '' . $this->command.' > ' . $this->logFile . ' 2>&1 & echo $!';
    	exec($command, $op);
    	$this->pid = (int) $op[0];
    }
    
    public function setPid($pid){
    	$this->pid = $pid;
    }
    
    public function getPid(){
    	return $this->pid;
    }
    
    public function status(){
    	$command = 'ps -p '.$this->pid;
    	exec($command,$op);
    	if (!isset($op[1]))return false;
    	else return true;
    }
    
    public function start(){
    	if ($this->command != '')$this->runCom();
    	else return true;
    }
    
    public function stop(){
    	$command = 'kill -9'.$this->pid;
    	exec($command);
    	if ($this->status() == false)return true;
    	else return false;
    }
}
