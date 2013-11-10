<?php

namespace restlt\examples\resources;

/**
 * @resourceBaseUri /resource1
 */
class Resource1 extends \restlt\Resource {
	public function __construct(\restlt\RequestInterface $request, \restlt\ResponseInterface $response) {
		parent::__construct($request, $response);
		$f1 = function ($request) {
		};

		$f2 = function ($request, $response, $return) {
		};

		$f3 = function ($request, $response, $e) {
			mail('user@example.com', 'Error in resource', $e->getMessage());
		};

		$this->on ( self::ON_BEFORE, 'getMe', $f1 );
		$this->on ( self::ON_AFTER, 'getMe', $f2 );
		$this->on ( self::ON_ERROR, 'getMe', $f3 );
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