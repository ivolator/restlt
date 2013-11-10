<?php

namespace restlt\tests;

use restlt\Server;
use restlt\Response;
use restlt\Request;

class RestLiteTest extends \PHPUnit_Framework_TestCase {
	protected $server = null;
	protected $request = null;
	protected $response = null;

	protected function setUp() {
		parent::setUp ();
		$this->request = new Request ();
		$this->response = new Response ();
		$this->server = new Server ( '/', null, $this->request, $this->response );

	}

	public function testSkip(){
		$this->markTestSkipped();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		$this->request = null;
		$this->response = null;
		$this->server = null;
		parent::tearDown ();
	}
}