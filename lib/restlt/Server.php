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
namespace restlt;

use Psr\Log\LoggerInterface;
use restlt\log\NullLogger;
use restlt\log\Log;
use restlt\cache\CacheAdapterInterface;
use restlt\Cache;
use restlt\meta\MetadataBuilder;
use restlt\meta\MetadataBuilderInterface;
use restlt\meta\AnnotationsParser;
use Psr\Log\LogLevel;

/**
 *
 * @author Vo
 *
 */
class Server
{

    /**
     * The path after the domain that will precede all resource URIS
     *
     * @var string
     */
    protected $baseUri = '/';

    /**
     *
     * @var RequestRouter
     */
    protected $requestRouter = null;

    /**
     *
     * @var Response
     */
    protected $response = null;

    /**
     *
     * @var Request
     */
    protected $request = null;

    /**
     *
     * @var array
     */
    protected $resourceClasses;

    /**
     *
     * @var \restlt\CacheAdaperInterface
     */
    protected $cacheAdapter;

    /**
     *
     * @var \restlt\cache\MetadataBuilder
     */
    protected $metadataBuilder = null;

    /**
     *
     * @var string
     */
    protected $name = null;

    /**
     *
     * @var \restlt\log\Log
     */
    protected $log = null;

    /**
     *
     * @var boolean
     */
    protected $autoDocs = true;

    /**
     *
     * @param string $baseUri
     */
    public function __construct($baseUri = null, $name = 'RestLt', RequestInterface $request = null, ResponseInterface $response = null)
    {
        if ($baseUri) {
            $this->baseUri = $baseUri;
        }
        $this->name = $name;
        register_shutdown_function(array(
            $this,
            'shutdown'
        ));
        if (! $request)
            $this->setRequest(new Request());
        if (! $response)
            $this->setResponse(new Response());
    }

    /**
     * Main method
     */
    public function serve()
    {
        $start = microtime(true);
        try {
            $docMeta = array();
            if (true === $this->autoDocs) {
                $docMeta = $this->getSelfAutoDocsMeta();
            }
            $this->getLog()->log('RestLt: REQUEST' . PHP_EOL . $this->getRequest());
            $resources = array_merge($this->getMetadataBuilder()->getResourcesMeta(), $docMeta);
            $this->getRequestRouter()->setResources($resources);
            $this->getResponse()->setRequestRouter($this->getRequestRouter());
            $this->getResponse()->setLog($this->getLog());

            $ret = $this->getResponse()->send();
        } catch (\Exception $e) {
            $this->getResponse()->setStatus(Response::INTERNALSERVERERROR);
            $this->getResponse()->setRequestRouter($this->getRequestRouter());
            $ret = $this->getResponse()->send();
            $this->getLog()->log('RestLt: Exception Message' . PHP_EOL . $e->getMessage());
            $this->getLog()->log('RestLt: Exception Code' . PHP_EOL . $e->getCode());
            $this->getLog()->log('RestLt: Exception Trace' . PHP_EOL . $e->getTraceAsString());
        }
        $this->getLog()->log('RestLt: RESPONSE' . PHP_EOL . $ret, LogLevel::INFO);
        $end = microtime(true);
        $this->getLog()->log('Total framework + resource time  :  ' . ($end - $start), LogLevel::INFO);
        return $ret;
    }

    /**
     * FQCN
     *
     * @param string $className
     *            - FQCN
     * @param
     *            string - full path to file
     * @return \restlt\Server
     */
    public function registerResourceClass($className, $fileName = null)
    {
        if (class_exists($className, true)) {
            if (! empty($fileName)) {
                $this->resourceClasses[$fileName] = $className;
            } else {
                $className = ltrim($className, '\\');
                $this->resourceClasses[$className] = $className;
            }
        } else {
            throw new \restlt\exceptions\FileNotFoundException('Could not register class' . $className . '. File not found!');
        }
        return $this;
    }

    /**
     * Not recursive.
     * If needed the user dev should implement his own.
     * The resources will be loaded if they are PSR-0 compliant.
     * Make sure they can be autoloaded using the latter standard.
     *
     * @param string $folderPath
     *            - full path to folder where to find these resources
     * @param string $namespace
     *            - the FQCN for the resources
     * @return \restlt\Server
     */
    public function registerResourceFolder($folderPath, $namespace = '\\')
    {
        if (is_readable($folderPath) && is_dir($folderPath)) {
            foreach (glob($folderPath . '/*.php') as $value) {
                $pathinfo = pathinfo($value);
                $namespace = trim($namespace, '\\');
                $fqnc = $namespace . '\\' . $pathinfo['filename'];
                $this->resourceClasses[$fqnc] = $fqnc;
            }
        } else {
            throw new \restlt\exceptions\FileNotFoundException('Folder not found');
        }
        return $this;
    }

    /**
     *
     * @return \restlt\RouterInterface $requestRouter
     */
    public function getRequestRouter()
    {
        if (! $this->requestRouter) {
            $this->requestRouter = new \restlt\routing\RequestRouter($this->request, $this->baseUri);
        }
        return $this->requestRouter;
    }

    /**
     *
     * @param field_type $requestRouter
     * @return \restlt\Server
     */
    public function setRequestRouter(\restlt\routing\RouterInterface $requestRouter)
    {
        $this->requestRouter = $requestRouter;
        return $this;
    }

    /**
     *
     * @return \restlt\Response $response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     *
     * @param field_type $response
     * @return \restlt\Server
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     *
     * @return the $request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @param field_type $request
     * @return \restlt\Server
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     *
     * @return the $baseUri
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     *
     * @param string $name
     * @return \restlt\Server
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @param field_type $baseUri
     * @return \restlt\Server
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    /**
     *
     * @return \restlt\cache\MetadataBuilderInterface $metadataBuilder
     */
    public function getMetadataBuilder()
    {
        if (! $this->metadataBuilder) {
            $mdb = new MetadataBuilder($this, $this->getResourceClasses());
            $mdb->setAnnotationsParser(new AnnotationsParser());
            if ($this->getCacheAdapter()) {
                $cache = new Cache($this->getCacheAdapter());
                $mdb->setCache($cache);
            }
            $this->metadataBuilder = $mdb;
        }
        return $this->metadataBuilder;
    }

    /**
     *
     * @param \restlt\meta\MetadataBuilderInterface $metadataBuilder
     */
    public function setMetadataBuilder(MetadataBuilderInterface $metadataBuilder)
    {
        $this->metadataBuilder = $metadataBuilder;
        return $this;
    }

    /**
     *
     * @return CacheAdaperInterface $cacheAdapter
     */
    public function getCacheAdapter()
    {
        return $this->cacheAdapter;
    }

    /**
     *
     * @param CacheAdaperInterface $cacheAdapter
     */
    public function setCacheAdapter(CacheAdapterInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    public function shutdown()
    {
        $error = error_get_last();
        if (in_array($error['type'], array(
            E_ERROR,
            E_USER_ERROR,
            E_CORE_ERROR, 
            E_COMPILE_ERROR
        ))) {
            $msg = 'An error with message ' . $error['message'] . ' occured at line ' . $error['line'] . ' in ' . $error['file'];
            $this->getLog()->log($msg, $this->getLog()
                ->getLogLevel());
        }
    }

    /**
     *
     * @return array
     */
    public function getResourceClasses()
    {
        return $this->resourceClasses;
    }

    /**
     *
     * @param string $outputType
     * @param string $strategyClassName
     */
    public function addOuputStrategy($outputType, $strategyClassName)
    {
        $this->getResponse()->addResponseOutputStrategies($outputType, $strategyClassName);
    }

    /**
     *
     * @return \restlt\log\LogInterface $logger
     */
    public function getLog()
    {
        if (! $this->log) {
            $this->log = new Log(new NullLogger(), 'critical');
        }
        return $this->log;
    }

    /**
     *
     * @param LoggerInterface $logger
     * @param $logLevel -
     *            set default logging PSR-3 levels for all calls made through the framework's logger
     * @return \restlt\Server
     */
    public function setLoggerImplementation(LoggerInterface $logger)
    {
        $this->log = new Log($logger);
        return $this;
    }

    /**
     *
     * @param boolean $autoDocs
     * @return \restlt\Server
     */
    public function setAutoDocs($autoDocs)
    {
        $this->autoDocs = (boolean) $autoDocs;
        return $this;
    }

    /**
     *
     * @return array number
     */
    public function getSelfAutoDocsMeta()
    {
        return array(
            '\restlt\Resource' => array(
                array(
                    "method" => "GET",
                    "methodUri" => '/introspect',
                    "function" => "getApiInfo",
                    "cacheControlMaxAge" => 1200,
                    'forceContentType' => Response::TEXT_HTML
                )
            )
        );
    }
}
