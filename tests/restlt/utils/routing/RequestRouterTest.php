<?php
class RequestRouterTest extends RestLiteTest
{
	protected $mockRequest = null;
	
	protected function setUp() {
		parent::setUp ();
	}

	/**
	 * @dataProvider getrouteDataProvider
	 * @param $resource
	 */
	public function testGetRoute($resources){
		
		$mockRequest = $this->getMockBuilder('\restlt\utils\routing\RequestRouter')
		->setMethods(array('getUri','getMethod'))->disableOriginalConstructor()->getmock();

		$mockRequest->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));
		$mockRequest->expects($this->once())->method('getUri')->will($this->returnValue('/example/resource1'));
		
		$mockRouter = $this->getMockBuilder('\restlt\utils\routing\RequestRouter')->disableOriginalConstructor()
		->setMethods(array('getRequest'))->getMock();
		
		$mockRouter->expects($this->exactly(2))->method('getRequest')
		->will($this->returnValue($mockRequest));
		
		$mockRouter->setServerBaseUri('/baseuri');
		
		$mockRouter->setResources($resources);
		$mockRouter->getRoute();
		
	}
	/**
	 * @dataProvider getRouteExceptionDataProvider
	 * @expectedException \restlt\exceptions\ServerException
	 */
	public function testGetRouteThrowsException($resources,$method,$uri){
		
		$mockRequest = $this->getMockBuilder('\restlt\utils\routing\RequestRouter')
		->setMethods(array('getUri','getMethod'))->disableOriginalConstructor()->getmock();

		$mockRequest->expects($this->once())->method('getMethod')->will($this->returnValue($method));
		$mockRequest->expects($this->once())->method('getUri')->will($this->returnValue($uri));
		
		$mockRouter = $this->getMockBuilder('\restlt\utils\routing\RequestRouter')->disableOriginalConstructor()
		->setMethods(array('getRequest'))->getMock();
		
		$mockRouter->expects($this->exactly(2))->method('getRequest')
		->will($this->returnValue($mockRequest));
		
		$mockRouter->setServerBaseUri('/baseuri');
		
		$mockRouter->setResources($resources);
		$mockRouter->getRoute();
		
	}

	public function getRouteExceptionDataProvider(){
		//test incorrect uri match
		$resources1 = array(
			'\example\Resource1' => 
			array(
				array(
					'method' => 'GET',
					'methodUri' => '/example/resource1',
					'function' => 'getResource1GetMethod'
				),
			)
		);
		//test incorrect method match
		$resources2 = array(
			'\example\Resource1' => 
			array(
				array(
					'method' => 'POST',
					'methodUri' => '/example/resource1',
					'function' => 'getResource1PostMethod'
				),
			)
		);
		//test dupe
		$resources3 = array(
			'\example\Resource1' => 
			array(
				array(
					'method' => 'POST',
					'methodUri' => '/example/resource1',
					'function' => 'getResource1PostMethod2'
				),
			),
			'\example\Resource2' => 
			array(
				array(
					'method' => 'POST',
					'methodUri' => '/example/resource1',
					'function' => 'getResource1PostMethodr'
				),
			),
		);
		return array(
			array($resources1,'GET','/wrong/uri'),
			array($resources2,'GET','/example/resource1'),
			array($resources3,'POST','/example/resource1')
		);
	}
	

	public function getRouteDataProvider(){
		$resources = array(
			'\example\Resource1' => 
			array(
				array(
					'method' => 'GET',
					'methodUri' => '/example/resource1',
					'function' => 'getResource1GetMethod'
				),
				array(
					'method' => 'POST',
					'methodUri' => '/example/resource1',
					'function' => 'getResource1PostMethod'
				),
			)
		);
		return array(
			array($resources)
		);
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
}