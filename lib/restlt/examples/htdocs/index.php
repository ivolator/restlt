<?php
date_default_timezone_set('America/New_York') ;
define('APP_ROOT' ,'/var/www/restlt');
use restlt\Server;
$al = require_once APP_ROOT . '/vendor/autoload.php';
$al->set ( 'restlt\\', APP_ROOT . '/restlt/lib' );

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
$zendCache = \Zend\Cache\StorageFactory::adapterFactory ( 'memcached', $options );
$memcached = new Memcached ();
$memcached->addServer ( 'localhost', 11211 );
$s = new Server ( '/' );
$s->setCacheAdapter ( new \restlt\utils\cache\RestltMemcachedAdapter( $memcached ) );
$s->getResponse()->addResponseOutputStrategies('serialize', '\restlt\utils\output\SerializeOutputStrategy');
// $s->setCacheAdapter ( new \restlt\utils\cache\ZFCacheAdapter ( $zendCache ) );
$s->registerResourceFolder ( APP_ROOT .  '/restlt/lib/restlt/examples/resources', 'restlt\examples\resources' );

$s->serve ();
exit;

