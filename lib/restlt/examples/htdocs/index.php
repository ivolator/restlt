<?php
date_default_timezone_set('America/New_York') ;
define('APP_ROOT' ,'/var/www/restlt');
use restlt\Server;
$al = require_once APP_ROOT . '/vendor/autoload.php';
/*
$options = array(
		'ttl' => 3600,
		'namespace' => 's',
		'readable' => true,
		'writable' => true,
		'servers' => array(
				'host' => 'localhost',
				'port' => 11211
		)
);
*/
//$zendCache = \Zend\Cache\StorageFactory::adapterFactory ( 'memcached', $options );
$memcached = new \Memcached ();
$memcached->addServer ( 'localhost', 11211 );
$s = new Server ( '/app' );
$s->setCacheAdapter ( new \restlt\cache\RestltMemcachedAdapter( $memcached ) );
//$s->setCacheAdapter ( new \restlt\cache\ZFCacheAdapter ( $zendCache ) );
$s->getResponse()->addResponseOutputStrategies('sphp', '\restlt\output\SerializeOutputStrategy');
$s->registerResourceFolder ( APP_ROOT .  '/vendor/restlt/restlt/lib/restlt/examples/resources', 'restlt\examples\resources' );

echo $s->serve ();
exit;

