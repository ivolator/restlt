<?php
/**
 * test case.
 */
class ServerTest extends RestLiteTest{

    protected $mockMetaDataBuilder = null;
    protected $mockServer = null;

    protected $mockRequesRouter = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp ();
        $this->mockMetaDataBuilder = $this->getMockBuilder('\restlt\utils\meta\MetadataBuilder')
            ->setMethods(array('getResourcesMeta'))->disableOriginalConstructor()->getMock();

        $this->mockRequesRouter = $this->getMockBuilder('\restlt\RequestRouter')->
            setMethods(array('setResources'))->disableOriginalConstructor()->getMock();

        $this->mockServer = $this->getMockBuilder('\restlt\Server')
            ->disableOriginalConstructor()
            ->setMethods(array('getMetadataBuilder','getRequestRouter'))->getMock();

        $this->mockRequest = $this->getMockBuilder('restlt\Request')->disableOriginalConstructor()
            ->setMethods(array('send'))->getMock();
        $this->mockResponse= $this->getMockBuilder('restlt\Response')->disableOriginalConstructor()
            ->getMock();

        $this->mockServer->expects($this->any())->method('getRequest')
            ->will($this->returnArgument($this->mockRequest));
        $this->mockServer->setResponse($this->mockResponse);
    }

    /**
     * @dataProvider testServerDataProvider
     */
    public function testServe($resources){
        $this->mockMetaDataBuilder->expects($this->once())->method('getResourcesMeta')
            ->will($this->returnValue($resources));
        $this->mockServer->expects($this->once())->method('getMetadataBuilder')
            ->will($this->returnValue($this->mockMetaDataBuilder));
        $this->mockServer->expects($this->any())->method('getRequestRouter')
            ->will($this->returnValue($this->mockRequesRouter));
//
        $this->mockServer->serve(false);
        $this->assertTrue(TRUE);

    }

    public function testServerDataProvider(){
        $resources = array(
            '\some\class\Path' => array(
                array(
                    'method' => 'GET',
                    'function' => 'stuff',
                    'methodUri' => '/path/to/resource',
                    'comment' => 'Some comment'
                ),

             )
        );
        return array(
            array($resources),
        );
    }

    public function testRegisterResourceClass() {
        $ret = $this->mockServer->registerResourceClass ( 'fixtureRegisterClass' );

        $expected = array (
            'fixtureRegisterClass' => 'fixtureRegisterClass'
        );

        $this->assertEquals ( $expected, $this->mockServer->getResourceClasses () );
    }

    /**
     * @expectedException \restlt\exceptions\FileNotFoundException
     * @expectedEcxeptionMessage Folder not found
     */
    public function testRegisterResourceClassThrowsFileNotFound() {
        $ret = $this->mockServer->registerResourceClass ( '\restlt\tests\InvalidClass' );
    }

    /**
     */
    public function testRegisterResourceFolder() {
        $this->mockServer->registerResourceFolder ( __DIR__ . '/../fixtures/resources', '\fixtures\resources' );
        $actual = $this->mockServer->getResourceClasses ();
        $expected = array (
                'fixtures\resources\Resource1' => 'fixtures\resources\Resource1'
        );
        $this->assertEquals ( $expected, $actual );
    }

    /**
     * @expectedException \restlt\exceptions\FileNotFoundException
     * @expectedEcxeptionMessage Folder not found
     */
    public function testRegisterResourceFolderThrowsFileNotFound() {
        $this->mockServer->registerResourceFolder ( __DIR__ . '/wrongname', '\fixtures\resources' );
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() {
        // TODO Auto-generated ServerTest::tearDown()
        parent::tearDown ();
    }
}

class fixtureRegisterClass {

}
