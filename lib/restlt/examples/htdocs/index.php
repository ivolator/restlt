<?php
date_default_timezone_set ( 'America/New_York' );
define ( 'APP_ROOT', '/var/www/restlt' );

$al = require_once APP_ROOT . '/vendor/autoload.php';
/**
 * $options = array(
 * 'ttl' => 3600,
 * 'namespace' => 's',
 * 'readable' => true,
 * 'writable' => true,
 * 'servers' => array(
 * 'host' => 'localhost',
 * 'port' => 11211
 * )
 * );
 * $zendCache = \Zend\Cache\StorageFactory::adapterFactory ( 'memcached', $options );
 */
$memcached = new \Memcached ();
$memcached->addServer ( 'localhost', 11211 );
$s = new \restlt\Server ( '/app' );

//Using Zend Log with stream writer
$zl = new Logger ();
$zl->addWriter ( new \Zend\Log\Writer\Stream ( 'restlt.log' ) );
$s->setLoggerImplementation ( new \restlt\log\ZendLogger ( $zl ) );

//Using build in memcached adapter
$s->setCacheAdapter ( new \restlt\cache\RestltMemcachedAdapter ( $memcached ) );

/**
 * Or you can use the Zend Framework cache implementation instead
 * In a similar way you can use Doctrine's cache or implement your own cache and add it to RestLt
 * 
 * $s->setCacheAdapter ( new \restlt\cache\ZFCacheAdapter ( $zendCache ) );
 */

//Inserting custom output strategy
$s->getResponse ()->addResponseOutputStrategies ( 'sphp', '\restlt\output\SerializeOutputStrategy' );

//Adding a folder with resources
$s->registerResourceFolder ( APP_ROOT . '/vendor/restlt/restlt/lib/restlt/examples/resources', 'restlt\examples\resources' );

//Serve request
echo $s->serve ();
exit ();

