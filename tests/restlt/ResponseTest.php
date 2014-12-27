<?php
use restlt\Request;
use restlt\output\HtmlOutputStrategy;
use restlt\output\XmlOtputStrategy;
use restlt\output\JsonOutputStrategy;
use restlt\Response;
use restlt\routing\Route;
use restlt\Result;

class ResponseTest extends RestLiteTest
{

    protected $mockResponse;

    protected $mockRequest;

    protected $mockRequestRouter;

    protected function setUp()
    {
        parent::setUp();
        $this->mockRequest = $this->getMockBuilder('\restlt\Request')
            ->setMethods(array(
            'getMethod',
            'getContentType'
        ))
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGet()
    {
        $_POST = array(
            'one' => 1,
            'two' => 2
        );
        $_GET = array(
            'three' => 3,
            'null' => null
        );
        $request = new Request();
        $actual = $request->get('one');
        $this->assertEquals(1, $actual);
        $actual = $request->get('two');
        $this->assertEquals(2, $actual);
        $actual = $request->get('three');
        $this->assertEquals(3, $actual);
        $actual = $request->get('null', 4);
        $this->assertEquals(4, $actual);
    }

    /**
     * @dataProvider getRoutes
     */
    public function testSend($route, $contentType, $expectedConversionStrategy, $data = null, $method)
    {
        $this->mockRequest->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue($contentType));
        $this->mockRequest->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));

        $this->mockRequestRouter = $this->getMockBuilder('\restlt\routing\RequestRouter')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'getRoute',
            'getRequest'
        ))
            ->getMock();
        $this->mockRequestRouter->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($this->mockRequest));
        $this->mockRequestRouter->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue($route));

        $this->mockResponse = $this->getMockBuilder('\restlt\Response')
            ->setMethods(array(
            'getRoutedResponse',
            'getRequestRouter',
            'getConversionStrategy',
            'getContentTypeFromRoute'
        ))
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockResponse->expects($this->any())
            ->method('getRequestRouter')
            ->will($this->returnValue($this->mockRequestRouter));
        $this->mockResponse->expects($this->any())
            ->method('getConversionStrategy')
            ->will($this->returnValue($expectedConversionStrategy));
        $this->mockResponse->expects($this->any())
            ->method('getContentTypeFromRoute')
            ->will($this->returnValue($contentType));

        $this->assertEmpty($this->mockResponse->getForceResponseType());
        $this->mockResponse->expects($this->any())
            ->method('getRoutedResponse')
            ->will($this->returnValue($data ? $data->getData() : null));
        $this->mockResponse->setStatus(Response::OK);

        $expected = $this->mockResponse->send();
        $this->assertEquals($expected, $expectedConversionStrategy && $data ? $expectedConversionStrategy->execute($data) : null);
    }

    public function getRoutes()
    {
        $route1 = new Route();
        $route1->setOutputTypeOverrideExt('xml');
        $route2 = new Route();
        $route2->setOutputTypeOverrideExt('json');
        $route3 = new Route();
        $route3->setOutputTypeOverrideExt('html');
        $route4 = new Route();
        $route4->setOutputTypeOverrideExt('sphp');

        $expectedJsonConversionstrategy = new JsonOutputStrategy();
        $expectedXmlConversionStrategy = new XmlOtputStrategy();
        $expectedHtmlConversionStrategy = new HtmlOutputStrategy();
        $data1 = new Result();
        $data1->setData(array(
            'test' => 1
        ));
        $data1->setHttpStatus(200);
        return array(
            array(
                $route1,
                Response::APPLICATION_JSON,
                $expectedJsonConversionstrategy,
                $data1,
                'POST'
            ),
            array(
                $route2,
                Response::APPLICATION_XML,
                $expectedXmlConversionStrategy,
                $data1,
                'GET'
            ),
            array(
                $route3,
                Response::TEXT_HTML,
                $expectedHtmlConversionStrategy,
                $data1,
                'PUT'
            ),
            array(
                $route4,
                Response::APPLICATION_XML,
                $expectedXmlConversionStrategy,
                $data1,
                'PATCH'
            ),
            array(
                null,
                Response::APPLICATION_JSON,
                $expectedHtmlConversionStrategy,
                null,
                'HEAD'
            )
        );
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
