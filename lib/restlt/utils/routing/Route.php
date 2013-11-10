<?php
/**
* The MIT License (MIT)
*
* Copyright (c) 2013 ivolator
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
namespace restlt\utils\routing;

class Route implements RouteInterface{

	protected $className = '';
	protected $functionName = '';
	protected $params = array();
	protected $cacheControlMaxAge = array();
	protected $outputTypeOverrideExt = null;

	/**
	 *
	 * @var array
	 */
	protected $userAnnotations = array();

	/**
	 * @return the $cacheControlMaxAge
	 */
	public function getCacheControlMaxAge() {
		return (int) $this->cacheControlMaxAge;
	}

	/**
	 * @param multitype: $cacheControlMaxAge
	 */
	public function setCacheControlMaxAge($cacheControlMaxAge) {
		$this->cacheControlMaxAge = (int) $cacheControlMaxAge;
	}

	/**
	 * @return the $className
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @param field_type $className
	 */
	public function setClassName($className) {
		$this->className = $className;
	}

	/**
	 * @return the $functionName
	 */
	public function getFunctionName() {
		return $this->functionName;
	}

	/**
	 * @param field_type $functionName
	 */
	public function setFunctionName($functionName) {
		$this->functionName = $functionName;
	}

	/**
	 * @return the $params
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * @param field_type $params
	 */
	public function setParams($params) {
		$this->params = $params;
	}

	/**
	 * @return the $outputType
	 */
	public function getOutputTypeOverrideExt() {
		return $this->outputTypeOverrideExt;
	}

	/**
	 * @param field_type $outputType
	 */
	public function setOutputTypeOverrideExt($outputType) {
		$this->outputTypeOverrideExt = $outputType;
	}
	/**
	 * @return the $userAnnotations
	 */
	public function getUserAnnotations() {
		return $this->userAnnotations;
	}

	/**
	 * @param multitype: $userAnnotations
	 */
	public function setUserAnnotations($userAnnotations) {
		$this->userAnnotations = $userAnnotations;
	}

	/**
	 * Get user annotation
	 *
	 * @param string $name
	 * @return Ambigous <NULL, multitype:>
	 */
	public function get($name){
		$ret = null;
		if(isset($this->userAnnotations[$name]))
		{
			$ret = $this->userAnnotations[$name];
		}
		return $ret;
	}

}

