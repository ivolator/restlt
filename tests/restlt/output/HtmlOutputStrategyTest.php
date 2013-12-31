<?php
use restlt\output\HtmlOutputStrategy;
use restlt\Result;

class HtmlOutputStrategyTest extends RestLiteTest {
	
	public function testExecuteWithNonStringData(){
		$htmlStartegy = new HtmlOutputStrategy();
		$arr = array('abc' => 22,'xyz' => 33);
		$obj = new stdClass();
		$obj->data = $arr;
		$obj->errors = array();
		
		$data = new Result();
		$data->setData($obj);
		
		$expected = print_r($data,true);
		
		$actual = $htmlStartegy->execute($data);
		$this->assertEquals($expected, $actual);
	}
	
	public function testExecuteWithStringData(){
		$htmlStartegy = new HtmlOutputStrategy();
		
		$expected = 'This is some HTML or plain string';

		$data = new Result();
		$data->setData($expected);
		$actual = $htmlStartegy->execute($data);
		$this->assertEquals($expected, $actual);
	}

}
