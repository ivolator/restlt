<?php
use restlt\utils\output\HtmlOutputStrategy;
use restlt\utils\output\XmlOtputStrategy;
use restlt\utils\output\JsonOutputStrategy;
use restlt\Response;
use restlt\utils\routing\Route;
use restlt\Result;

class ResponseTest extends RestLiteTest {
	protected $mockResponse;
	protected $mockRequest;
	protected $mockRequestRouter;
	
	protected function setUp() {
		parent::setUp ();
	}
	
	/**
	 * @dataProvider getRoutes
	 */
	public function testSend($route, $contentType, $expectedConversionStrategy, $data = null, $method) {
		
		$this->mockRequest = $this->getMockBuilder ( '\restlt\Request' )->setMethods ( array ('getMethod','getContentType' ) )->disableOriginalConstructor ()->getMock ();
		$this->mockRequest->expects ( $this->once () )->method ( 'getContentType' )->will ( $this->returnValue ( $contentType ) );
		$this->mockRequest->expects ( $this->any())->method ( 'getMethod' )->will ( $this->returnValue ( $method ) );
		
		$this->mockRequestRouter = $this->getMockBuilder ( '\restlt\utils\routing\RequestRouter' )->disableOriginalConstructor ()
		->setMethods ( array ('getRoute', 'getRequest' ) )->getMock ();
		$this->mockRequestRouter->expects ( $this->atLeastOnce() )->method ( 'getRequest' )->will ( $this->returnValue ( $this->mockRequest )  );
		$this->mockRequestRouter->expects ( $this->exactly ( 2 ) )->method ( 'getRoute' )->will (  $this->returnValue ( $route )  );
		
		$this->mockResponse = $this->getMockBuilder ( '\restlt\Response' )->setMethods ( array ('getRoutedResponse', 'getRequestRouter', 'getConversionStrategy', 'getContentTypeFromRoute' ) )->disableOriginalConstructor ()->getMock ();
		$this->mockResponse->expects ( $this->any () )->method ( 'getRequestRouter' )->will ( $this->returnValue ( $this->mockRequestRouter ) );
		$this->mockResponse->expects ( $this->any () )->method ( 'getConversionStrategy' )->will ( $this->returnValue ( $expectedConversionStrategy ) );
		$this->mockResponse->expects ( $this->any() )->method ( 'getContentTypeFromRoute' )->will ( $this->returnValue ( $contentType ) );
		
		$this->assertEmpty ( $this->mockResponse->getForceResponseType () );
		$this->mockResponse->expects ( $this->any () )->method ( 'getRoutedResponse' )->will ( $this->returnValue ( $data ? $data->getData() : null ) );
		$this->mockResponse->setStatus ( Response::OK );
		
		$expected = $this->mockResponse->send ( );
		$this->assertEquals($expected, $expectedConversionStrategy && $data?$expectedConversionStrategy->execute($data):null);
	}
	
	public function getRoutes() {
		$route1 = new Route ();
		$route1->setOutputTypeOverrideExt('xml');
		$route2 = new Route ();
		$route2->setOutputTypeOverrideExt('json');
		$route3 = new Route ();
		$route3->setOutputTypeOverrideExt('html');
		

		$expectedJsonConversionstrategy = new JsonOutputStrategy ();
		$expectedXmlConversionStrategy = new XmlOtputStrategy ();
		$expectedHtmlConversionStrategy = new HtmlOutputStrategy ();
		$data1 = new Result ();
		$data1->setData ( array ('test' => 1 ) );
		return array (
			array ($route1, Response::APPLICATION_JSON, $expectedJsonConversionstrategy, $data1,'POST' ), 
//			array($route2,Response::APPLICATION_XML,$expectedXmlConversionStrategy, $data,'GET'),
			array($route3,Response::TEXT_HTML,$expectedHtmlConversionStrategy, $data1,'PUT'),
			array(null, Response::APPLICATION_JSON,$expectedHtmlConversionStrategy, null,'HEAD'),
			)
		;
	}
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
}
