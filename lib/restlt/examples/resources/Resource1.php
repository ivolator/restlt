<?php

namespace restlt\examples\resources;

/**
 * @resourceBaseUri /resource1
 */
class Resource1 extends \restlt\Resource implements \restlt\ResourceInterface{
	public function __construct(\restlt\RequestInterface $request, \restlt\ResponseInterface $response) {
		parent::__construct($request, $response);
		$f1 = function ($r) {
		};
		$f2 = function ($r) {
		};
		$this->on ( self::ON_BEFORE, 'getMe', $f2 );
		$this->on ( self::ON_AFTER, 'getMe', $f1 );
	}
	/**
	 *
	 * @method GET
	 * @methodUri /([0-9]+)
	 * @custom SSSSS
	 */
	public function getMe($id = '') {
		$obj = new \stdClass ();
		$custom = $this->annotations->get('custom');
		$obj->a = array (
				9,
				8,
				7
		);
		$ret = array (
				$obj,
				2,
				4,
				array (
						'a',
						'b',
						'c'
				),
				array (
						'a',
						'b',
						'c'
				),
				$custom
		);
		return $ret;
	}

	/**
	 *
	 * @method POST
	 */
	public function postMe() {
		$obj = new \stdClass ();
		$obj->a = array (
				9,
				8,
				7
		);
		$ret = array (
				1,
				2,
				4,
				array (
						'a',
						'b',
						'c'
				),
				$obj
		);
		return $obj;
	}

	/**
	 *
	 * @method PUT
	 * @methodUri /put
	 */
	public function putMe() {
		$params = $this->getRequest()->getQueryParams ();
		return $this->request->get ( 'someParam' );
	}

	/**
	 *
	 * @method PATCH
	 * @methodUri /patch
	 */
	public function patchMe() {
		return __METHOD__;
	}

	/**
	 *
	 * @method DELETE
	 * @methodUri /del
	 */
	public function deleteMe() {
		return __METHOD__;
	}
}