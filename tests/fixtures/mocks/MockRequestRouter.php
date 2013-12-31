<?php
use restlt\routing\RouterInterface;
class MockRequestRouter implements RouterInterface {
	protected $request = null;
	protected $resources = null;
	
	/* (non-PHPdoc)
	 * @see restlt\routing.RouterInterface::getRoute()
	 */
	public function getRoute() {
	}
	
	/* (non-PHPdoc)
	 * @see restlt\routing.RouterInterface::getRequest()
	 */
	public function getRequest() {
		return $this->request;
	}
	
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}
	
	public function setResources($res) {
		$this->resources = $res;
		return $this;
	}
	
	public function getResources() {
		return $this->resources;
	}
}