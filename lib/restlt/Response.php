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

use restlt\routing\Route;
use restlt\exceptions\ApplicationException;
use restlt\exceptions\SystemException;

/**
 *
 * @author Vo
 *
 */
class Response implements \restlt\ResponseInterface {
	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NONAUTHORATIVEINFORMATION = 203;
	const NOCONTENT = 204;
	const RESETCONTENT = 205;
	const PARTIALCONTENT = 206;
	const MULTIPLECHOICES = 300;
	const MOVEDPERMANENTLY = 301;
	const FOUND = 302;
	const SEEOTHER = 303;
	const NOTMODIFIED = 304;
	const USEPROXY = 305;
	const TEMPORARYREDIRECT = 307;
	const BADREQUEST = 400;
	const UNAUTHORIZED = 401;
	const PAYMENTREQUIRED = 402;
	const FORBIDDEN = 403;
	const NOTFOUND = 404;
	const METHODNOTALLOWED = 405;
	const NOTACCEPTABLE = 406;
	const PROXYAUTHENTICATIONREQUIRED = 407;
	const REQUESTTIMEOUT = 408;
	const CONFLICT = 409;
	const GONE = 410;
	const LENGTHREQUIRED = 411;
	const PRECONDITIONFAILED = 412;
	const REQUESTENTITYTOOLARGE = 413;
	const REQUESTURITOOLONG = 414;
	const UNSUPPORTEDMEDIATYPE = 415;
	const REQUESTEDRANGENOTSATISFIABLE = 416;
	const EXPECTATIONFAILED = 417;
	const IMATEAPOT = 418;
	const INTERNALSERVERERROR = 500;
	const NOTIMPLEMENTED = 501;
	const BADGATEWAY = 502;
	const SERVICEUNAVAILABLE = 503;
	const GATEWAYTIMEOUT = 504;
	const HTTPVERSIONNOTSUPPORTED = 505;
	const APPLICATION_JSON = 'application/json';
	const APPLICATION_XML = 'application/xml';
	const TEXT_HTML = 'text/html';
	const TEXT_PLAIN = 'text/plain';
	protected $headers = array ();
	protected $responseOutputStrategies = array (
			'xml' => '\restlt\output\XmlOtputStrategy',
			'json' => '\restlt\output\JsonOutputStrategy',
			'sphp' => '\restlt\output\SerializerOutputStrategy',
			'html' => '\restlt\output\HtmlOutputStrategy' 
	);
	
	/**
	 * HTTP status code
	 *
	 * @var integer
	 */
	protected $status = 200;
	
	/**
	 *
	 * @var ResultInterface
	 */
	protected $resultObject = null;
	
	/**
	 * Force the response style - self::APPLICATION_JSON, self::APPLICATION_XML
	 *
	 * @var string
	 */
	protected $forceResponseType = null;
	
	/**
	 *
	 * @var \restlt\routing\RouterInterface
	 */
	protected $requestRouter = null;
	
	/**
	 *
	 * @var \Exception
	 */
	protected $displayError = false;
    
    /**
     * @var \restlt\log\Log
     */
    protected $log = null;
    	
	/**
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addHeader($name, $value) {
		$this->headers [$name] = $value;
	}
	
	/**
	 *
	 * @param RequestRouter $route
	 * @param Request $request
	 * @param Result $returnValue
	 */
	protected function getRoutedResponse(\restlt\routing\RouterInterface $router) {
		$ret = null;
		
		$route = $router->getRoute ();
		
		if ($route && $route->getClassName () && $route->getFunctionName ()) {
			$class = $route->getClassName ();
			$resourceObj = new $class ( $router->getRequest (), $this );
			$resourceObj->setLog($this->log);
			$params = $route->getParams () ? $route->getParams () : array ();
			if (is_callable ( array ($resourceObj, $route->getFunctionName () ) )) {
				try {
					$cbs = $resourceObj->getCallbacks ();
					$resourceObj->setAnnotations ( $route );
					// before the method was processed
					$this->executeCallbacks ( Resource::ON_BEFORE, $route->getFunctionName (), $cbs, array ($router->getRequest () ) );
					register_shutdown_function ( array ($this, 'shutdown' ) );
					// routed call
					$ret = call_user_func_array ( array ($resourceObj, $route->getFunctionName () ), $params );
					// after method was processed
					$this->executeCallbacks ( Resource::ON_AFTER, $route->getFunctionName (), $cbs, array ($router->getRequest (), $this, $ret ) );
				} catch (\Exception $e ) {
					$this->executeCallbacks ( Resource::ON_ERROR, $route->getFunctionName (), $cbs, array ($router->getRequest (), $this, $e ) );
					$this->setStatus ( Response::INTERNALSERVERERROR );
					$this->executeCallbacks ( Resource::ON_AFTER, $route->getFunctionName (), $cbs, array ($router->getRequest (), $this, $ret ) );
					$this->displayError = $e;
				}
				$resourceObj->clearCallbacks ();
				$this->setUserErrors ( $resourceObj );
			}
		} else {
			$this->status = $this->status && Response::OK === $this->status ? self::NOTFOUND : $this->status;
		}
		
		if ($route && $route->getCacheControlMaxAge ()) {
			$this->addHeader ( 'Cache-Control', 'private, max-age=' . $route->getCacheControlMaxAge () );
		} else {
			$this->addHeader ( "Cache-Control", "no-cache, must-revalidate" );
		}
		return $ret;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \restlt\ResponseInterface::send()
	 */
	public function send() {
		$data = null;
		if ($this->status === self::OK && $this->getRequestRouter () && $this->getRequestRouter ()->getRoute ()) {
			$data = $this->getRoutedResponse ( $this->getRequestRouter () );
		}
		$contentType = $this->getRequestRouter ()->getRequest ()->getContentType ();
		
		$conversionStrategy = $this->getConversionStrategy ( $contentType );
		if ($this->forceResponseType) {
			$conversionStrategy = $this->getConversionStrategy ( $this->forceResponseType );
		}
		
		$route = null;
		try {
			$route = $this->getRequestRouter ()->getRoute ();
		} catch ( \restlt\exceptions\ServerException $e ) {
			$this->setStatus ( $e->getCode () );
		}
		
		if ($route && $route->getOutputTypeOverrideExt ()) {
			$conversionStrategy = $this->getConversionStrategy ( $route->getOutputTypeOverrideExt () );
		} elseif ($this->forceResponseType) {
			$conversionStrategy = $this->getConversionStrategy ( $this->forceResponseType );
		}
		
		if ($route) {
			$cType = $this->getContentTypeFromRoute ( $route );
			$contentType = $cType ? $cType : $contentType;
		}
		if ($contentType) {
			$this->addHeader ( 'Content-Type', $contentType );
		}
		return $this->_send ( $data ? $data : null, $conversionStrategy );
	}
	
	/**
	 * 
	 * @param Route $route
	 * @return string
	 */
	protected function getContentTypeFromRoute(Route $route = null) {
		$contentType = self::APPLICATION_JSON;
		if ($route && in_array ( 'application/' . $route->getOutputTypeOverrideExt (), array (self::APPLICATION_JSON, self::APPLICATION_XML ) )) {
			$contentType = 'application/' . $route->getOutputTypeOverrideExt ();
		} elseif ($route && in_array ( 'text/' . $route->getOutputTypeOverrideExt (), array (self::TEXT_HTML, self::TEXT_PLAIN ) )) {
			$contentType = 'text/' . $route->getOutputTypeOverrideExt ();
		}
		return $contentType;
	}
	/**
	 * Send Response
	 *
	 * @param string $data
	 */
	protected function _send($data = null, $conversionStrategy) {
		if (isset ( $_SERVER ['HTTP_CONNECTION'] )) {
			header ( 'x-custom-rest-server: RestLt' );
			header ( 'Allow: POST, GET, PUT, DELETE, PATCH, HEAD' );
			header ( 'Connection: close' );
			if (version_compare ( PHP_VERSION, '5.4.0', '>=' )) {
				http_response_code ( $this->status );
			} else {
				header ( "HTTP/1.0 " . $this->status );
			}
			foreach ( $this->headers as $header => $value ) {
				$hStr = $header . ': ' . $value;
				header ( $hStr, true, $this->status );
			}
		}
		// prepare the payload if any
		if ($data && $this->getRequestRouter ()->getRequest ()->getMethod () !== Request::HEAD) {
			$this->getResultObject ()->setData ( $data );
			return $this->getResultObject ()->toString ( $conversionStrategy );
		} elseif ($this->displayError) {
			$this->getResultObject ()->addError ( $this->displayError->getMessage (), $this->displayError->getCode () );
			return $this->getResultObject ()->toString ( $conversionStrategy );
		}
	}
	
	/**
	 *
	 * @param string $contentType
	 * @return TypeConversionStrategyInterface
	 */
	protected function getConversionStrategy($contentType) {
		
		$class = null;
		// check for user registered output strategies
		if (false != ($strategies = preg_grep ( '#' . $contentType . '#', array_keys ( $this->responseOutputStrategies ) ))) {
			if (count ( $strategies ) > 1) {
				trigger_error ( 'There were more than one output strategies. This might be a problem. Did you register a custom output strategy?', E_USER_NOTICE );
			}
			$class = $this->responseOutputStrategies [array_pop ( $strategies )];
		}
		
		preg_match_all ( '#xml|json|html#i', $contentType, $matches );
		if (! $class && isset ( $matches [0] [0] ) && strtolower ( $matches [0] [0] )) {
			$class = $this->responseOutputStrategies [$matches [0] [0]];
		}
		
		if ($class && ! class_exists ( $class, true )) {
			throw new ApplicationException ( 'Conversion strategy not found' );
		}
		
		// fallback to JSON
		if (! $class) {
			$class = $this->responseOutputStrategies ['json'];
		}
		
		return new $class ();
	}
	
	/**
	 *
	 * @return the $headers
	 */
	public function getHeaders() {
		return $this->headers;
	}
	
	/**
	 *
	 * @param field_type $headers
	 * @return Response
	 */
	public function setHeaders($headers) {
		$this->headers = $headers;
		return $this;
	}
	
	/**
	 *
	 * @return ResultInterface $reultObject
	 */
	public function getResultObject() {
		if (! $this->resultObject) {
			$this->resultObject = new Result ();
		}
		return $this->resultObject;
	}
	
	/**
	 *
	 * @param ResultInterface $reultObject
	 * @return Response
	 */
	public function setResultObject(ResultInterface $reultObject) {
		$this->resultObject = $reultObject;
		return $this;
	}
	
	/**
	 *
	 * @return the $status
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 *
	 * @param int $status
	 * @return Response
	 */
	public function setStatus($status) {
		$this->status = $status;
		return $this;
	}
	
	/**
	 *
	 * @return the $responseOutputStrategies
	 */
	public function getResponseOutputStrategies() {
		return $this->responseOutputStrategies;
	}
	
	/**
	 *
	 * @param multitype:string $responseOutputStrategies
	 * @return Response
	 */
	public function setResponseOutputStrategies($responseOutputStrategies) {
		$this->responseOutputStrategies = $responseOutputStrategies;
		return $this;
	}
	
	/**
	 *
	 * @param array $responseOutputStrategies
	 * @param string $strategyClassName
	 * - FQCN
	 * @return Response
	 */
	public function addResponseOutputStrategies($outputType, $strategyClassName) {
		$this->responseOutputStrategies [$outputType] = $strategyClassName;
		return $this;
	}
	
	/**
	 *
	 * @return the $forceResponseType
	 */
	public function getForceResponseType() {
		return $this->forceResponseType;
	}
	
	/**
	 * Use this to force overall server responses.
	 * However this option will be with lower priority if the request URI requests reposne via adding a '.json' or '.xml'
	 * at the end of the URI path.
	 * Adding a '.somethinelse' is dictated by the association with a custom output type converter (TypeConversionStrategyInterface)
	 *
	 * @param string $forceResponseType
	 */
	public function setForceResponseType($forceResponseType) {
		if ($forceResponseType && in_array ( $forceResponseType, array (self::APPLICATION_JSON, self::APPLICATION_XML, self::TEXT_HTML, self::TEXT_PLAIN ) )) {
			$this->forceResponseType = $forceResponseType;
		} else {
			throw new \InvalidArgumentException ( 'Invalid response type. Must be one of Response::APPLICATION_JSON,Response::APPLICATION_XML,Response::TEXT_PLAIN' );
		}
		return $this;
	}
	
	/**
	 *
	 * @param string $event
	 * @param string $method
	 * @param array $cbs
	 * @param array $param_arr
	 */
	protected function executeCallbacks($event, $method, $cbs = array(), $param_arr = array()) {
		if ($cbs && is_array ( $cbs )) {
			
			$_cbs = array ();
			if (isset ( $cbs [$event] )) {
				$_cbs = $cbs [$event];
			}
			if (isset ( $cbs [$method] [$event] )) {
				$_cbs = array_merge ( $_cbs, $cbs [$method] [$event] );
			}
			if ($_cbs)
				foreach ( $_cbs as $cb ) {
					if (is_callable ( $cb )) {
						$ret = call_user_func_array ( $cb, $param_arr );
					}
				}
		}
	}
	
	public function shutdown() {
		$error = error_get_last ();
		if (in_array ( $error ['type'], array (E_ERROR, E_USER_ERROR, E_WARNING, E_USER_WARNING, E_CORE_ERROR, E_CORE_WARNING, E_DEPRECATED, E_STRICT ) )) {
			$msg = 'An error with message ' . $error ['message'] . ' occured at line ' . $error ['line'] . ' in ' . $error ['file'];
			throw new \Exception ( $msg, Response::INTERNALSERVERERROR );
		}
	}
	/**
	 *
	 * @return \restlt\RouterInterface $requestRouter
	 */
	public function getRequestRouter() {
		return $this->requestRouter;
	}
	
	/**
	 *
	 * @param \restlt\RequestRouter $requestRouter
	 */
	public function setRequestRouter(\restlt\routing\RouterInterface $requestRouter) {
		$this->requestRouter = $requestRouter;
		return $this;
	}
	
	/**
	 *
	 * @return \restlt\Route $routeAnnotationMeta
	 */
	public function getAnnotation() {
		return $this->annotations;
	}
	
	/**
	 * Add errors to the response added by some interaction in the resource methods
	 * @param ResourceInterface $resource
	 */
	protected function setUserErrors(ResourceInterface $resource) {
		$errorStack = $resource->getErrors ();
		$errorStack->rewind ();
		while ( $errorStack->valid () ) {
			$error = $errorStack->pop ();
			$this->getResultObject ()->addError ( $error [0], isset ( $error [1] ) ? $error [1] : null );
			$errorStack->next ();
		}
	}

	/**
	 * @return \restlt\log\Log $logger
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * @param \restlt\log\Log $logger
	 */
	public function setLog(\restlt\log\Log $logger) {
		$this->log = $logger;
		return $this; 
	}
	
}
