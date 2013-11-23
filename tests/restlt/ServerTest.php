<?php
/**
 * test case.
 */
class ServerTest extends RestLiteTest{

    protected $mockMetaDataBuilder = null;
    protected $mockServer = null;
    protected $mockResponse = null;
    protected $mockRequest = null;
    protected $mockRequesRouter = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() {
        parent::setUp ();
        $this->mockMetaDataBuilder = $this->getMockBuilder('\restlt\utils\meta\MetadataBuilder')
            ->setMethods(array('getResourcesMeta'))->disableOriginalConstructor()->getMock();

        $this->mockRequesRouter = $this->getMockBuilder('\restlt\RouterInterface')->
            setMethods(array('setResources'))->disableOriginalConstructor()->getMock();

        $this->mockServer = $this->getMockBuilder('\restlt\Server')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest','getMetadataBuilder','getRequestRouter','getResponse'))->getMock();

        $this->mockRequest = $this->getMockBuilder('restlt\Request')->disableOriginalConstructor()->getMock();
        $this->mockResponse= $this->getMockBuilder('restlt\Response')
        ->setMethods(array('setRequestRouter','send'))->disableOriginalConstructor()
            ->getMock();

        $this->mockServer->expects($this->any())->method('getRequest')
            ->will($this->returnArgument($this->mockRequest));
    }

    /**
     * @dataProvider testServerDataProvider
     */
    public function testServe($resources){
    	require_once __DIR__ . '/../fixtures/mocks/MockRequestRouter.php';
        $ret = 'responsestring';
        $this->mockResponse->expects($this->any())->method('setRequestRouter')->will($this->returnSelf());    
        $this->mockResponse->expects($this->any())->method('send')->will($this->returnValue($ret));    
        
        $this->mockMetaDataBuilder->expects($this->any())->method('getResourcesMeta')
            ->will($this->returnValue($resources));
        $this->mockServer->expects($this->any())->method('getMetadataBuilder')
            ->will($this->returnValue($this->mockMetaDataBuilder));
        $this->mockServer->expects($this->any())->method('getRequestRouter')
            ->will($this->returnValue(new MockRequestRouter()));
        $this->mockServer->expects($this->any())->method('getResponse')
            ->will($this->returnValue($this->mockResponse));

        echo $this->mockServer->serve(false);
        $this->expectOutputString('responsestring');
        
        $this->mockServer->serve(true);
        $this->assertEquals($this->mockServer->getRequestRouter()->getResources(), array_merge($resources,$this->mockServer->getApiResourceInfo()));
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
	    $this->mockMetaDataBuilder = null;
	    $this->mockServer = null;
	    $this->mockResponse = null;
	    $this->mockRequest = null;
	    $this->mockRequesRouter = null;
	    	
        parent::tearDown ();
    }
}

class fixtureRegisterClass {

}
