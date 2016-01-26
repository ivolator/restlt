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

/**
 *
 * @author vo
 *
 */
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 *
 * @author vo
 *
 */
class Log
{

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $psrLogger = null;

    /**
     *
     * @var string
     */
    protected $logLevel = null;

    public function __construct(LoggerInterface $psrLogger, $level = LogLevel::ERROR)
    {
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
    public function log($messsage, $logLevel = NULL, $context = array('RestLt'))
    {
        $logLevel = $logLevel ? $logLevel : $this->logLevel;
        $this->getLogger()->log($logLevel, $messsage, $context);
    }

    /**
     *
     * @return \Psr\Log\LoggerInterface $logger
     */
    public function getLogger()
    {
        return $this->psrLogger;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Psr\Log.LoggerAwareInterface::setLogger()
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->psrLogger = $logger;
        return $this;
    }

    /**
     *
     * @return the $logLevel
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     *
     * @param string $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }
}