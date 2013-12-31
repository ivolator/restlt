<?php
namespace restlt\log;

use Psr\Log\AbstractLogger;
/**
 * 
 * Merely copied the Psr NullLogger
 * 
 * @author vo
 *
 */
class NullLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
    }
}
