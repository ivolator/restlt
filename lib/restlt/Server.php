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

use restlt\utils\cache\CacheAdapterInterface;
use restlt\utils\Cache;
use restlt\utils\MetadataBuilder;
use restlt\exceptions\ServerException;
use restlt\utils\di\ServiceContainer;

/**
 *
 * @author Vo
 *
 */
class Server {
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
	 * @var \restlt\utils\CacheAdaperInterface
	 */
	protected $cacheAdapter;

	/**
	 *
	 * @var \restlt\utils\MetadataBuilder
	 */
	protected $metadataBuilder = null;

	/**
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 *
	 * @var ServiceContainer
	 */
	protected $serviceContainer = null;

	/**
	 *
	 * @param string $baseUri
	 */
	public function __construct($baseUri = null, $name = 'RestLite', Request $request = null, Response $response = null) {
		if ($baseUri) {
			$this->baseUri = $baseUri;
		}
		$this->name = $name;
		if(!$request) $this->setRequest ( new Request () );
		if(!$response) $this->setResponse ( new Response () );
		register_shutdown_function ( array(	$this, 'shutdown') );
	}

	/**
	 * Main method
	 */
	public function serve() {
		try {
			$resources = $this->getMetadataBuilder ()->getResources ();
			$this->getRequestRouter ()->setResources ( $resources );
			$this->getResponse ()->setRequestRouter($this->getRequestRouter())->send();
			exit;
		} catch ( \restlt\exceptions\ServerException $e ) {
			$this->getResponse ()->setStatus ( $e->getCode () );
		} catch ( \Exception $e ) {
			$this->getResponse ()->setStatus ( Response::INTERNALSERVERERROR );
		}

		$this->getResponse ()->send ();
		exit;
	}

	/**
	 * FQCN
	 *
	 * @param string $className
	 *        	- FQCN
	 * @return \restlt\Server
	 */
	public function registerResourceClass($className) {
		if (class_exists ( $className, true )) {
			$this->resourceClasses [$className] = $className;
		} else {
			throw new \restlt\exceptions\FileNotFoundException ( 'Could not register class' . $className . '. File not found!' );
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
	 *        	- full path to folder where to find these resources
	 * @param string $namespace
	 *        	- the FQCN for the resources
	 * @return \restlt\Server
	 */
	public function registerResourceFolder($folderPath, $namespace = '\\') {
		if (is_readable ( $folderPath ) && is_dir ( $folderPath )) {
			foreach ( glob ( $folderPath . '/*.php' ) as $value ) {
				$pathinfo = pathinfo ( $value );
				$namespace = trim ( $namespace, '\\' );
				$fqnc = $namespace . '\\' . $pathinfo ['filename'];
				$this->resourceClasses [$fqnc] = $fqnc;
			}
		} else {
			throw new \restlt\exceptions\FileNotFoundException ( 'Folder not found' );
		}
		return $this;
	}

	/**
	 *
	 * @return \restlt\RouterInterface $requestRouter
	 */
	public function getRequestRouter() {
		if (! $this->requestRouter) {
			$this->requestRouter = new RequestRouter ( $this->request, $this->baseUri );
		}
		return $this->requestRouter;
	}

	/**
	 *
	 * @param field_type $requestRouter
	 * @return \restlt\Server
	 */
	public function setRequestRouter(RouterInterface $requestRouter) {
		$this->requestRouter = $requestRouter;
		return $this;
	}

	/**
	 *
	 * @return \restlt\Response $response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 *
	 * @param field_type $response
	 * @return \restlt\Server
	 */
	public function setResponse($response) {
		$this->response = $response;
		return $this;
	}

	/**
	 *
	 * @return the $request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 *
	 * @param field_type $request
	 * @return \restlt\Server
	 */
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}

	/**
	 *
	 * @return the $baseUri
	 */
	public function getBaseUri() {
		return $this->baseUri;
	}

	/**
	 *
	 * @param string $name
	 * @return \restlt\Server
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 *
	 * @param field_type $baseUri
	 * @return \restlt\Server
	 */
	public function setBaseUri($baseUri) {
		$this->baseUri = $baseUri;
		return $this;
	}

	/**
	 *
	 * @return \restlt\utils\MetadataBuilder $metadataBuilder
	 */
	public function getMetadataBuilder() {
		if (! $this->metadataBuilder) {
			$mdb = new MetadataBuilder ( $this, $this->getResourceClasses() );
			$mdb->setAnnotationsParser(new utils\AnnotationsParser ());
			if ($this->getCacheAdapter ()) {
				$cache = new Cache ( $this->getCacheAdapter () );
				$mdb->setCache ( $cache );
			}
			$this->metadataBuilder = $mdb;
		}
		return $this->metadataBuilder;
	}

	/**
	 *
	 * @param MetadataBuilder $metadataBuilder
	 */
	public function setMetadataBuilder($metadataBuilder) {
		$this->metadataBuilder = $metadataBuilder;
		return $this;
	}

	/**
	 *
	 * @return CacheAdaperInterface $cacheAdapter
	 */
	public function getCacheAdapter() {
		return $this->cacheAdapter;
	}

	/**
	 *
	 * @param CacheAdaperInterface $cacheAdapter
	 */
	public function setCacheAdapter(CacheAdapterInterface $cacheAdapter) {
		$this->cacheAdapter = $cacheAdapter;
	}

	/**
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	public function shutdown() {
		$error = error_get_last ();
		if (in_array ( $error ['type'], array(
				E_ERROR,
				E_USER_ERROR,
				E_WARNING,
				E_USER_WARNING,
				E_CORE_ERROR,
				E_CORE_WARNING,
				E_DEPRECATED,
				E_STRICT
		) )) {
			$msg = 'An error with message ' . $error ['message'] . ' occured at line ' . $error ['line'] . ' in ' . $error ['file'];
			throw new ServerException ( $msg, Response::INTERNALSERVERERROR );
		}
	}

	/**
	 *
	 * @return array
	 */
	public function getResourceClasses() {
		return $this->resourceClasses;
	}

	/**
	 * @return \restlt\utils\di\ServiceContainer $serviceContainer
	 */
	public function getServiceContainer() {
		return $this->serviceContainer;
	}

	/**
	 * @param \restlt\utils\di\ServiceContainer $serviceContainer
	 */
	public function setServiceContainer($serviceContainer) {
		$this->serviceContainer = $serviceContainer;
	}


}