<?php
use restlt\Result;
use restlt\utils\output\SerializerOutputStrategy;

/**
 * test case.
 */
class SerializerOutputStrategyTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
	}
	
	/**
	 * 
	 */
	public function testExecute(){
		$serializer = new SerializerOutputStrategy();
		$data = new stdClass();
		$data->prop1 = 1;
		$data->prop2 = array(1,2,3);
		$result = new Result();
		$result->setData($data);
		$expected = serialize($result);
		$actual = $serializer->execute($result);
		$this->assertEquals($expected, $actual);		
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated SerializerOutputStrategyTest::tearDown()
		

		parent::tearDown ();
	}

}

