<?php

namespace fixtures\resources;

/**
 * @resourceBaseUri /resource1
 */
class Resource1 extends \restlt\Resource implements \restlt\ResourceInterface{


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
		throw new \Exception('My Error',1000);
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