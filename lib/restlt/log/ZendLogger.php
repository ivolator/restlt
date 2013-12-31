<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Ivo Mandalski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace restlt\log;
use Zend\Log\Logger;

use Psr\Log\AbstractLogger;
class ZendLogger extends AbstractLogger {
	
	protected $logLevelMap = array (
		Logger::EMERG => \Psr\Log\LogLevel::EMERGENCY, 
		Logger::ALERT => \Psr\Log\LogLevel::ALERT, 
		Logger::CRIT => \Psr\Log\LogLevel::CRITICAL, 
		Logger::ERR => \Psr\Log\LogLevel::ERROR, 
		Logger::WARN => \Psr\Log\LogLevel::WARNING, 
		Logger::NOTICE => \Psr\Log\LogLevel::NOTICE, 
		Logger::INFO => \Psr\Log\LogLevel::INFO, 
		Logger::DEBUG => \Psr\Log\LogLevel::DEBUG );
	/**
	 * 
	 * @var \Zend\Log\Logger
	 */
	protected $zLog = null;
	public function __construct(\Zend\Log\Logger $logger) {
		$this->zLog = $logger;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Psr\Log.LoggerInterface::log()
	 */
	public function log($level, $message, array $context = array()) {
		$map = array_flip($this->logLevelMap);
		$this->zLog->log ( $map[$level] ? $map[$level] : Logger::CRIT, $message );
	}
}