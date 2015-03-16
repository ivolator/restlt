<?php

class RequestRouterTest extends RestLiteTest
{

    protected $mockRequest = null;

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider getrouteDataProvider
     *
     * @param
     *            $resource
     */
    public function testGetRoute($resources, $method, $uri)
    {
        $mockRequest = $this->getMockBuilder('\restlt\routing\RequestRouter')
            ->setMethods(array(
            'getUri',
            'getMethod'
        ))
            ->disableOriginalConstructor()
            ->getmock();

        $mockRequest->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $mockRequest->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($uri));

        $mockRouter = $this->getMockBuilder('\restlt\routing\RequestRouter')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'getRequest',
            'getMetadataBuilder'
        ))
            ->getMock();

        $mockRouter->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $mockRouter->setServerBaseUri('/baseuri');
        $bldr = new MyMetadataBldr();
        $bldr->meta = $resources;
        $mockRouter->expects($this->any())
            ->method('getMetadataBuilder')
            ->will($this->returnValue($bldr));

        $ret = $mockRouter->getRoute();

        $arr = array_keys($resources);
        $resource = array_shift($arr);
        $this->assertEquals($ret->getClassName(), $resource);
        $this->assertEquals($ret->get('method'), $method);
    }

    /**
     * @dataProvider getRouteExceptionDataProvider
     * @expectedException \restlt\exceptions\ServerException
     */
    public function testGetRouteThrowsException($resources, $method, $uri)
    {
        $mockRequest = $this->getMockBuilder('\restlt\routing\RequestRouter')
            ->setMethods(array(
            'getUri',
            'getMethod'
        ))
            ->disableOriginalConstructor()
            ->getmock();

        $mockRequest->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $mockRequest->expects($this->once())
            ->method('getUri')
            ->will($this->returnValue($uri));

        $mockRouter = $this->getMockBuilder('\restlt\routing\RequestRouter')
            ->disableOriginalConstructor()
            ->setMethods(array(
            'getRequest',
            'getMetadataBuilder'
        ))
            ->getMock();

        $mockRouter->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $mockRouter->setServerBaseUri('/baseuri');
        $bldr = new MyMetadataBldr();
        $bldr->meta = $resources;
        $mockRouter->expects($this->any())
            ->method('getMetadataBuilder')
            ->will($this->returnValue($bldr));

        $mockRouter->getRoute();
    }

    public function getRouteExceptionDataProvider()
    {
        // test incorrect uri match
        $resources1 = array(
            '\example\Resource1' => array(
                array(
                    'method' => 'GET',
                    'methodUri' => '/baseuri/example/resource2',
                    'function' => 'getMe'
                )
            )
        );
        // test incorrect method match
        $resources2 = array(
            '\example\Resource1' => array(
                array(
                    'method' => 'POST',
                    'methodUri' => '/baseuri/example/resource2',
                    'function' => 'postMe'
                )
            )
        );

        return array(
            array(
                $resources1,
                'GET',
                '/wrong/uri'
            ),
            array(
                $resources2,
                'GET',
                '/baseuri/example/resource2'
            )
        );
    }

    public function getRouteDataProvider()
    {
        $resources = array(
            '\fixtures\Resource2' => array(
                array(
                    'method' => 'GET',
                    'methodUri' => '/resource2/([0-9]+)',
                    'function' => 'getMe'
                ),
                array(
                    'method' => 'POST',
                    'methodUri' => '/resource2',
                    'function' => 'postMe'
                ),
                array(
                    'method' => 'PUT',
                    'methodUri' => '/resource2/put',
                    'function' => 'putMe'
                ),
                array(
                    'method' => 'PATCH',
                    'methodUri' => '/resource2/patch',
                    'function' => 'patchMe'
                ),
                array(
                    'method' => 'DELETE',
                    'methodUri' => '/resource2/del',
                    'function' => 'deleteMe'
                )
            )
        );
        return array(
            array(
                $resources,
                'GET',
                '/baseuri/resource2/123'
            ),
            array(
                $resources,
                'POST',
                '/baseuri/resource2'
            ),
            array(
                $resources,
                'PUT',
                '/baseuri/resource2/put'
            ),
            array(
                $resources,
                'PATCH',
                '/baseuri/resource2/patch'
            ),
            array(
                $resources,
                'DELETE',
                '/baseuri/resource2/del'
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

class MyMetadataBldr
{

    public $meta = [];

    public function getResourcesMeta()
    {
        return $this->meta;
    }
}