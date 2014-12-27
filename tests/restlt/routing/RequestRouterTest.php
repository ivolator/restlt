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

		$mockRequest = $this->getMockBuilder('\restlt\routing\RequestRouter')
		->setMethods(array('getUri','getMethod'))->disableOriginalConstructor()->getmock();

		$mockRequest->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));
		$mockRequest->expects($this->any())->method('getUri')->will($this->returnValue('/example/resource2'));

		$mockRouter = $this->getMockBuilder('\restlt\routing\RequestRouter')->disableOriginalConstructor()
		->setMethods(array('getRequest'))->getMock();

		$mockRouter->expects($this->once())->method('getRequest')
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

		$mockRequest = $this->getMockBuilder('\restlt\routing\RequestRouter')
		->setMethods(array('getUri','getMethod'))->disableOriginalConstructor()->getmock();

		$mockRequest->expects($this->once())->method('getMethod')->will($this->returnValue($method));
		$mockRequest->expects($this->once())->method('getUri')->will($this->returnValue($uri));

		$mockRouter = $this->getMockBuilder('\restlt\routing\RequestRouter')->disableOriginalConstructor()
		->setMethods(array('getRequest'))->getMock();

		$mockRouter->expects($this->once())->method('getRequest')
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
					'methodUri' => '/baseuri//example/resource2',
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
					'methodUri' => '/baseuri/example/resource2',
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
					'methodUri' => '/baseuri/example/resource2',
					'function' => 'getResource1PostMethod2'
				),
			),
			'\example\Resource2' =>
			array(
				array(
					'method' => 'POST',
					'methodUri' => '/baseuri/example/resource2',
					'function' => 'getResource1PostMethodr'
				),
			),
		);
		return array(
			array($resources1,'GET','/wrong/uri'),
			array($resources2,'GET','/baseuri/example/resource2'),
			array($resources3,'POST','/baseuri/example/resource2')
		);
	}


	public function getRouteDataProvider(){
		$resources = array(
			'\fixtures\Resource2' =>
			array(
				array(
					'method' => 'GET',
					'methodUri' => '/baseuri/example/resource2',
					'function' => 'getMe'
				),
				array(
					'method' => 'POST',
					'methodUri' => '/baseuri/example/resource2',
					'function' => 'postMe'
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