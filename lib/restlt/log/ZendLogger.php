<?php
use Psr\Log\AbstractLogger;
class ZendLogger extends AbstractLogger{
	/**
	 * 
	 * @var \Zend\Log\Logger
	 */
	protected $zLog = null;
	public function __construct(\Zend\Log\Logger $logger){
		$this->zLog = $logger;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Psr\Log.LoggerInterface::log()
	 */
	public function log($level, $message){	
		$this->zLog->log($level, $message);
	}
}