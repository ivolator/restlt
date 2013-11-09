<?php
namespace restlt;

use restlt\exceptions\ApplicationException;

/**
 *
 * @author Vo
 *
 */
class Resource implements ResourceInterface{

	const ON_BEFORE = 'before';
	const ON_AFTER = 'after';
	const ON_ERROR = 'error';

	protected $callbacks = array();

	/**
	 *
	 * @var \restlt\Request
	 */
	protected $request = null;

	/**
	 *
	 * @var Response
	 */
	protected $response = null;

	/**
	 *
	 * @var \restlt\Route
	 */
	protected $annotations = null;

	public function __construct(\restlt\RequestInterface $request, \restlt\ResponseInterface $response){
		$this->setRequest($request);
		$this->setResponse($response);
	}

	/**
	 *
	 * @return  \restlt\Request $request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 *
	 * @param \restlt\Request $request
	 */
	public function setRequest(\restlt\Request $request) {
		$this->request = $request;
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
	 * @param \restlt\Response $response
	 */
	public function setResponse($response) {
		$this->response = $response;
		return $this;
	}

	/**
	 * These callbacks are executed before the resource method is called
	 * $cb = function(\restlt\Request $r){}
	 *
	 * @param Callable $callback
	 */
	public function onBefore($methodName, $callback) {
		return $this->on ( self::ON_BEFORE, $methodName, $callback );
	}

	/**
	 * These callbacks are executed after the resource method is called
	 * $cb = function(\restlt\Request $request, \restlt\Response $response, $result){}
	 *
	 * @param Callable $callback
	 */
	public function onAfter($methodName, $callback) {
		return $this->on ( self::ON_AFTER, $methodName, $callback );
	}

	/**
	 * These callbacks are executed if during the method call there was a thrown exception
	 * $cb = function(\restlt\Request $request, \restlt\Response $response,Exception $e){}
	 *
	 * @param Callable $callback
	 */
	public function onError($methodName, $callback) {
		return $this->on ( self::ON_ERROR, $methodName, $callback );
	}

	/**
	 *
	 * @param string $event
	 * @param string $methodName
	 * @param Callable $callback
	 * @return \restlt\Resource
	 */
	protected function on($event, $methodName = '', $callback) {
		if (! $callback || ($callback && ! is_callable ( $callback ))) {
			throw new ApplicationException ( 'Event not provided' );
		}
		if($methodName){
			$this->callbacks [$methodName] [$event] [] = $callback;
		} else {
			$this->callbacks [$event] [] = $callback;
		}
		return $this;
	}
	/**
	 *
	 * @return array $callbacks
	 */
	public function getCallbacks() {
		return $this->callbacks;
	}

	/**
	 *
	 * @param string $type
	 */
	public function clearCallbacks() {
		unset ( $this->callbacks );
	}
	/**
	 * @return the $annotations
	 */
	public function getAnnotations() {
		return $this->annotations;
	}

	/**
	 * @param \restlt\Route $annotations
	 */
	public function setAnnotations($annotations) {
		$this->annotations = $annotations;
	}

}