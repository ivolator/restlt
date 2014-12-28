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
	public function testGetRoute($resources, $method, $uri){

		$mockRequest = $this->getMockBuilder('\restlt\routing\RequestRouter')
		->setMethods(array('getUri','getMethod'))->disableOriginalConstructor()->getmock();

		$mockRequest->expects($this->once())->method('getMethod')->will($this->returnValue($method));
		$mockRequest->expects($this->any())->method('getUri')->will($this->returnValue($uri));

		$mockRouter = $this->getMockBuilder('\restlt\routing\RequestRouter')->disableOriginalConstructor()
		->setMethods(array('getRequest'))->getMock();

		$mockRouter->expects($this->once())->method('getRequest')
		->will($this->returnValue($mockRequest));

		$mockRouter->setServerBaseUri('/baseuri');

		$mockRouter->setResources($resources);
		$ret = $mockRouter->getRoute();

		$arr = array_keys($resources);
		$resource = array_shift($arr);
		$this->assertEquals($ret->getClassName(),$resource);
		$this->assertEquals($ret->get('method'),$method);
		$this->assertEquals($ret->get('methodUri'),'/baseuri/'.$uri);
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
					'methodUri' => '/baseuri/example/resource2',
					'function' => 'getMe'
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
					'function' => 'postMe'
				),
			)
		);

		return array(
			array($resources1,'GET','/wrong/uri'),
			array($resources2,'GET','/baseuri/example/resource2'),
		);
	}


	public function getRouteDataProvider(){
		$resources = array(
			'\fixtures\Resource2' =>
			array(
				array(
					'method' => 'GET',
					'methodUri' => '/baseuri/resource2/([0-9]+)',
					'function' => 'getMe'
				),
				array(
					'method' => 'POST',
					'methodUri' => '/baseuri/resource2',
					'function' => 'postMe'
				),
				array(
					'method' => 'PUT',
					'methodUri' => '/baseuri/resource2/put',
					'function' => 'putMe'
				),
				array(
					'method' => 'PATCH',
					'methodUri' => '/baseuri/resource2/patch',
					'function' => 'patchMe'
				),
				array(
					'method' => 'DELETE',
					'methodUri' => '/baseuri/resource2/del',
					'function' => 'deleteMe'
				),
			)
		);
		return array(
			array($resources,'GET','resource2/([0-9]+)'),
			array($resources,'POST','resource2'),
			array($resources,'PUT','resource2/put'),
			array($resources,'PATCH','resource2/patch'),
			array($resources,'DELETE','resource2/del')
		);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
}