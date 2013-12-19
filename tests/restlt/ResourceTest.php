<?php
/**
 * The MIT License (MIT)
 *
 * CCopyright (c) 2013 Ivo Mandalski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
/**
 * Resource test case.
 */
use restlt\Resource;
class ResourceTest extends PHPUnit_Framework_TestCase {
	protected $mockResource = null;
	protected $mockRequest = null;
	protected $mockResponse = null;
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		$this->mockResource = $this->getMockBuilder('\restlt\Resource')->disableOriginalConstructor()
		->setMethods(array('getRequest','getResponse'))->getMock();
		
	}

	public function testOn(){
		$expected = function(){};
		$this->mockResource->onError('methdOne',$expected);
		$cbs = $this->mockResource->getCallbacks();
		$this->assertEquals($expected, $cbs['methdOne'][Resource::ON_ERROR][0]);
		
		$this->mockResource->onAfter('methdOne',$expected);
		$cbs = $this->mockResource->getCallbacks();
		$this->assertEquals($expected, $cbs['methdOne'][Resource::ON_AFTER][0]);

		$this->mockResource->onBefore('methdOne',$expected);
		$cbs = $this->mockResource->getCallbacks();
		$this->assertEquals($expected, $cbs['methdOne'][Resource::ON_BEFORE][0]);
		
		return $this->mockResource;
	}
	
	/**
	 * @depends testOn
	 */
	public function testClearCallbacks($mockResource){
		$mockResource->clearCallbacks();
		$this->assertEmpty($mockResource->getCallbacks());
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
	
}
