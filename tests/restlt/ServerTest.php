<?php
/**
 * test case.
 */
use restlt\log\NullLogger;
class ServerTest extends RestLiteTest {

	protected $mockMetaDataBuilder = null;
	protected $mockServer = null;
	protected $mockResponse = null;
	protected $mockRequest = null;
	protected $mockRequesRouter = null;
	protected $mockCache = null;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		$this->mockMetaDataBuilder = $this->getMockBuilder ( '\restlt\utils\meta\MetadataBuilder' )->setMethods ( array ('getResourcesMeta' ) )->disableOriginalConstructor ()->getMock ();

		$this->mockRequesRouter = $this->getMockBuilder ( '\restlt\RouterInterface' )->setMethods ( array ('setResources' ) )->disableOriginalConstructor ()->getMock ();

		$this->mockServer = $this->getMockBuilder ( '\restlt\Server' )->disableOriginalConstructor ()->setMethods ( array ('getCacheInstance','getRequest', 'getMetadataBuilder', 'getRequestRouter', 'getResponse', 'getLog' ) )->getMock ();

		$this->mockRequest = $this->getMockBuilder ( 'restlt\Request' )->disableOriginalConstructor ()->setMethods(['getUri','getRawPost'])->getMock ();
		$this->mockResponse = $this->getMockBuilder ( 'restlt\Response' )->setMethods ( array ('setRequestRouter', 'send' ) )->disableOriginalConstructor ()->getMock ();
		$this->mockCache = $this->getMockBuilder('restlt\Cache')->disableOriginalConstructor()->setMethods(['set','get','test'])->getMock();
		$this->mockServer->expects ( $this->any () )->method ( 'getCacheInstance' )->willReturn( $this->mockCache );
	}

	/**
	 * @dataProvider testServerDataProvider
	 */
	public function testServe($resources) {
		require_once __DIR__ . '/../fixtures/mocks/MockRequestRouter.php';
		$ret = 'responsestring';
		$this->mockResponse->expects ( $this->any () )->method ( 'setRequestRouter' )->will ( $this->returnSelf () );
		$this->mockResponse->expects ( $this->any () )->method ( 'send' )->will ( $this->returnValue ( $ret ) );

		$this->mockMetaDataBuilder->expects ( $this->any () )->method ( 'getResourcesMeta' )->will ( $this->returnValue ( $resources ) );
		$this->mockServer->expects ( $this->any () )->method ( 'getMetadataBuilder' )->will ( $this->returnValue ( $this->mockMetaDataBuilder ) );
		$this->mockServer->expects ( $this->any () )->method ( 'getRequestRouter' )->will ( $this->returnValue ( new MockRequestRouter () ) );
		$this->mockServer->expects ( $this->any () )->method ( 'getResponse' )->will ( $this->returnValue ( $this->mockResponse ) );
		$this->mockServer->expects ( $this->any () )->method ( 'getRequest' )->will ( $this->returnValue ( $this->mockRequest ) );
		$this->mockServer->expects ( $this->any () )->method ( 'getCacheInstance' )->will ( $this->returnValue ( $this->mockCache ) );


		$this->mockServer->expects ( $this->any() )->method ( 'getLog' )->will ( $this->returnValue ( new \restlt\log\Log(new NullLogger(), 'critical') ) );
		$_SERVER['REQUEST_METHOD'] = 'GET';
		echo $this->mockServer->serve ( false );
		$this->expectOutputString ( 'responsestring' );
	}

	public function testServerDataProvider() {
		$resources = array ('\some\class\Path' => array (array ('method' => 'GET', 'function' => 'stuff', 'methodUri' => '/path/to/resource', 'comment' => 'Some comment' ) )

		 );
		return array (array ($resources ) );
	}

	public function testRegisterResourceClass() {

		$expected = array ('fixtureRegisterClass' => 'fixtureRegisterClass' );
		$this->mockCache->expects($this->once())->method('get')->willReturn($expected);
		$this->mockCache->expects($this->any())->method('test')->willReturn(true);

	    $this->mockServer->expects ( $this->any () )->method ( 'getCacheInstance' )->willReturn( $this->mockCache );


	    $ret = $this->mockServer->registerResourceClass ( 'fixtureRegisterClass' );

	    $this->assertEquals ( $expected, $this->mockServer->getResourceClasses () );
	}

	/**
	 * @expectedException \restlt\exceptions\FileNotFoundException
	 * @expectedEcxeptionMessage Folder not found
	 */
	public function testRegisterResourceClassThrowsFileNotFound() {
		$this->mockCache->expects($this->once())->method('test')->willReturn(false);
	    $this->mockServer->expects ( $this->any () )->method ( 'getCacheInstance' )->willReturn( $this->mockCache );

	    $ret = $this->mockServer->registerResourceClass ( '\restlt\tests\InvalidClass' );
	}

	/**
	 */
	public function testRegisterResourceFolder() {
		$this->mockServer->registerResourceFolder ( __DIR__ . '/../fixtures/resources', '\fixtures\resources' );
		$actual = $this->mockServer->getResourceClasses ();

		$expected = array ('fixtures\resources\Resource1' => 'fixtures\resources\Resource1'
         		           ,'fixtures\resources\Resource2' => 'fixtures\resources\Resource2' );
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
