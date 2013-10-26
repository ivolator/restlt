<?php
namespace restlt;

use restlt\Response;
use restlt\Request;
/**
 * 
 * @author Vo
 *
 */
class AbstractResource {
	
	/**
	 * 
	 * @var Request
	 */
	protected $request = null;
	
	/**
	 * 
	 * @var Response
	 */
	protected $response = null;
	
	/**
	 * @return the $request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @param Request $request
	 */
	public function setRequest($request) {
		$this->request = $request;
	}

	/**
	 * @return the $response
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * @param Response $response
	 */
	public function setResponse($response) {
		$this->response = $response;
	}


}