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
/**
 *
 * @author Vo
 *
 */

class Request implements \restlt\RequestInterface{

	const POST = 'POST';
	const GET = 'GET';
	const PUT = 'PUT';
	const DELETE = 'DELETE';
	const PATCH = 'PATCH';
	const HEAD = 'HEAD';

	protected $supportedMethods = array (
				'POST',
				'GET',
				'PUT',
				'DELETE',
				'PATCH',
				'HEAD'
		);
	/**
	 *
	 * @var array
	 */
	protected $queryParams = array ();

	/**
	 *
	 * @var array
	 */
	protected $postParams = array ();

	/**
	 *
	 * @var string
	 */
	protected $rawPost = null;

	/**
	 *
	 * @var string
	 */
	protected $uri;

	/**
	 *
	 * @var string
	 */
	protected $method = null;

	/**
	 *
	 * @var array
	 */
	protected $headers = array ();

	/**
	 *
	 * @var string XML | JSON | TEXT
	 */
	protected $contentType = null;

	/**
	 */
	public function __construct() {
		$this->rawPost = file_get_contents ( "php://input" );
		$this->postParams = $_POST;
		$this->queryParams = $_GET;
		$_POST = null;
		$_GET = null;
		$this->headers = $this->buildHeadersList ( !empty($_SERVER) ? $_SERVER : array() );
	}

	/**
	 *
	 * @param string $paramName
	 *        	- the POST or GET parameter name
	 * @param string $returnDefault
	 *        	- return this if there is nothing in $paramName
	 * @return Ambigous <>|string
	 */
	public function get($paramName, $returnDefault = null) {
		$params = array_merge ( $this->postParams, $this->queryParams );
		if (isset ( $params [$paramName] ))
			return $params [$paramName];
		return $returnDefault;
	}

	/**
	 *
	 * @return the $queryStringParams
	 */
	public function getQueryParams() {
		return $this->queryParams;
	}

	/**
	 *
	 * @param array $queryStringParams
	 */
	public function setQueryParams($queryStringParams) {
		$this->queryParams = $queryStringParams;
	}

	/**
	 *
	 * @return the $postParams
	 */
	public function getPostParams() {
		return $this->postParams;
	}

	/**
	 *
	 * @param array $postParams
	 */
	public function setPostParams($postParams) {
		$this->postParams = $postParams;
	}

	/**
	 *
	 * @return the $rawPost
	 */
	public function getRawPost() {
		return $this->rawPost;
	}

	/**
	 *
	 * @param string $rawPost
	 */
	public function setRawPost($rawPost) {
		$this->rawPost = $rawPost;
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
	 * @param array $headers
	 */
	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	/**
	 *
	 * @param array $SERVER
	 * @return array
	 */
	public function buildHeadersList(array $SERVER = array()) {
		$ret = array ();
		if($SERVER) {
			foreach ( $SERVER as $k => $v ) {
				if (stristr ( $k, 'http_' )) {
					$res = str_ireplace ( 'http_', '', $k );
					$ret [$res] = $v;
				}
			}
		}
		return $ret;
	}

	/**
	 *
	 * @return the $url
	 */
	public function getUri() {
		if(!$this->uri){
			$res = parse_url ( $_SERVER ['REQUEST_URI'] );
			$this->uri = $res ['path'] ? $res ['path'] : '/';
		}
		return $this->uri;
	}

	/**
	 *
	 * @return the $method
	 */
	public function getMethod() {
		if(!$this->method){
			$this->method = ! empty ( $_SERVER ['X-HTTP-METHOD-OVERRIDE'] ) ? $_SERVER ['X-HTTP-METHOD-OVERRIDE'] : $_SERVER ['REQUEST_METHOD'];
		}
		return strtoupper($this->method);
	}

	/**
	 *
	 * @return the $contentType
	 */
	public function getContentType() {
		$this->contentType = Response::TEXT_PLAIN;
		if (stripos ( $this->headers ['ACCEPT'], 'json' )) {
			$this->contentType = Response::APPLICATION_JSON;
		} elseif (stripos ( $this->headers ['ACCEPT'], 'xml' ) || stripos ( $this->headers ['ACCEPT'], 'html' )) {
			$this->contentType = Response::APPLICATION_XML;
		}
		return $this->contentType;
	}
}