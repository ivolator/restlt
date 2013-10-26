<?php
namespace restlt\examples\resources;
use restlt\Resource;

/**
 * @resourceBaseUri /resource2
 */
class Resource2 extends Resource {
	public function __construct(\restlt\RequestInterface $request, \restlt\ResponseInterface $response) {
		parent::__construct($request, $response);
		$f1 = function ($r) {
		};
		$f2 = function ($r) {
		};
		$this->on ( self::ON_ERROR, 'getYou', $f1 );
		$this->on ( self::ON_BEFORE, 'postYou', $f2 );
	}
	/**
	 * @method  GET
	 * @methodUri   /([0-9]+)
	 */
	public function getYou($id = '') {
		return $id;
	}

	/**
	 * @method POST
	 * @methodUri /
	 */
	public function postYou() {
	}

	/**
	 * @method PUT
	 * @methodUri /
	 */
	public function putYou() {
	}

	/**
	 * @method PATCH
	 * @methodUri /
	 */
	public function patchYou() {
	}

	/**
	 * @method DELETE
	 * @methodUri /
	 */
	public function deleteYou() {
	}
}