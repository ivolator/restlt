<?php
class RestLiteTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
		parent::setUp ();
	}

	public function testSkip(){
		$this->markTestSkipped();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
}