<?php
namespace restlt\log;
/**
 * 
 * @author vo
 *
 */
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * 
 * @author vo
 *
 */
class Log {
	/**
	 * 
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $psrLogger = null;
	/**
	 * 
	 * @var integer
	 */
	protected $logLevel = null;
	
	public function __construct(LoggerInterface $psrLogger, $level='critical'){
		$this->logLevel = $level;
		$this->psrLogger = $psrLogger;
	}
	
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($messsage){
    	$this->getLogger()->log($this->logLevel, $messsage);
    }
	
    /**
	 * @return \Psr\Log\LoggerInterface $logger
	 */
	public function getLogger() {
		return $this->psrLogger;
	}

	/**
     * (non-PHPdoc)
     * @see Psr\Log.LoggerAwareInterface::setLogger()
     */
	public function setLogger(LoggerInterface $logger) {
		$this->psrLogger = $logger;
		return $this;
	}

}