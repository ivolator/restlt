<?php
use restlt\output\JsonOutputStrategy;
use restlt\Result;

class JsonOutputStrategyTest extends RestLiteTest {

	public function testExecute(){
		$jsonStartegy = new JsonOutputStrategy();
		$arr = array('abc' => 22,'xyz' => 33);
		$expected = new stdClass();
		$expected->data = $arr;
		$expected->errors = array();
		$expected->httpStatus = 200;
		$expected = json_encode($expected);
		$data = new Result();
		$data->setData($arr);
		$data->setHttpStatus(200);
		$actual = $jsonStartegy->execute($data);
		$this->assertEquals($expected, $actual);
	}

}
