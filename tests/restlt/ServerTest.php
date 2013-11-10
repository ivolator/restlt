<?php

namespace restlt\tests;

/**
 * test case.
 */
class ServerTest extends RestLiteTest {

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
	}

	public function testServe(){

	}

	public function testRegisterResourceClass() {
		$ret = $this->server->registerResourceClass ( '\restlt\tests\fixtureRegisterClass' );

		$expected = array (
				'\restlt\tests\fixtureRegisterClass' => '\restlt\tests\fixtureRegisterClass'
		);

		$this->assertEquals ( $expected, $this->server->getResourceClasses () );
	}

	/**
	 * @expectedException \restlt\exceptions\FileNotFoundException
	 * @expectedEcxeptionMessage Folder not found
	 */
	public function testRegisterResourceClassThrowsFileNotFound() {
		$ret = $this->server->registerResourceClass ( '\restlt\tests\InvalidClass' );
	}

	/**
	 */
	public function testRegisterResourceFolder() {
		$this->server->registerResourceFolder ( __DIR__ . '/../fixtures/resources', '\fixtures\resources' );
		$actual = $this->server->getresourceClasses ();
		$expected = array (
				'fixtures\resources\Resource1' => 'fixtures\resources\Resource1'
		);
		$this->assertEquals ( $expected, $actual );
	}

	/**
	 * @expectedException \restlt\exceptions\FileNotFoundException
	 * @expectedEcxeptionMessage Folder not found
	 */
	public function testRegisterResourceFolderThrowsFileNotFound() {
		$this->server->registerResourceFolder ( __DIR__ . '/wrongname', '\fixtures\resources' );
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated ServerTest::tearDown()
		parent::tearDown ();
	}
}

class fixtureRegisterClass {

}