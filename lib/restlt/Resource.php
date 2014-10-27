<?php
namespace restlt;
/**
 * The MIT License (MIT)
 *
 * CCopyright (c) 2013 Ivo Mandalski
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

    private $callbacks = array();

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

    /**
     *
     * @var \restlt\Server
     */
    protected $server = null;

    /**
     * User Errors
     * Use these when no exception is thrown and you need to return 200 with some error feeddback
     * @var \SplStack
     */
    protected $errors = array();

    /**
     * @var \restlt\log\Log
     */
    protected $log = null;

    public function __construct(RequestInterface $request, ResponseInterface $response){
        $this->setRequest($request);
        $this->setResponse($response);
        $this->errors = new \SplStack();
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
    public function setRequest(RequestInterface $request) {
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
    public function setResponse(ResponseInterface $response) {
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
         $this->callbacks = array();
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

    /**
     *
     * @param string $errorMessage
     * @param integer $errorCode
     */
    public function addError($errorMessage,$errorCode = null){
        $this->errors->push(array($errorMessage,$errorCode));
    }

    /**
     * @return the $errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(\SplStack $errors) {
        $this->errors = $errors;
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
	}
}
