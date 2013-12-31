<?php
/**
 * 
 * @author vo
 *
 */
use restlt\meta\AnnotationsParser;
class MetaDataBuilderTest extends RestLiteTest {
	protected $mockCache = null;
	protected $mockAnnotationsParser = null;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		$this->mockAnnotationsParser = $this->getMockBuilder ( 'restlt\meta\AnnotationsParser' )->disableOriginalConstructor ()->getMock ();
	}
	/**
	 * @dataProvider buildMetaDataProvider
	 */
	public function testBuildMeta($resources) {
		$mockMetaBuilder = $this->getMockBuilder ( 'restlt\meta\MetadataBuilder' )->setMethods ( array ('getResourcesMeta' ) )->disableOriginalConstructor ()->getMock ();
		$mockMetaBuilder->setResourceClasses ( $resources );
		$mockMetaBuilder->setAnnotationsParser ( new AnnotationsParser () );
		$actual = $mockMetaBuilder->buildMeta ();
		$needle = array ("method" => "GET", "methodUri" => "/resource1/([0-9]+)", "custom" => "SSSSS", "comment" => "", "function" => "getMe" );
		$this->assertContains ( $needle, $actual['fixtures\resources\Resource1'] );
		$needle = array ("method" => "POST", "methodUri" => "/resource1", "comment" => "", "function" => "postMe" );
		$this->assertContains ( $needle, $actual['fixtures\resources\Resource1'] );
		$needle = array ("method" => "PUT", "methodUri" => "/resource1/put", "comment" => "", "function" => "putMe" );
		$this->assertContains ( $needle, $actual['fixtures\resources\Resource1'] );
		$needle = array ("method" => "PATCH", "methodUri" => "/resource1/patch", "comment" => "", "function" => "patchMe" );
		$this->assertContains ( $needle, $actual['fixtures\resources\Resource1'] );
		$needle = array ("method" => "DELETE", "methodUri" => "/resource1/del", "comment" => "", "function" => "deleteMe" );
		$this->assertContains ( $needle, $actual['fixtures\resources\Resource1'] );
	
	}
	
	public function buildMetaDataProvider() {
		$res1 = array ('fixtures\resources\Resource1' => 'fixtures\resources\Resource1' );
		return array (array ($res1 ) );
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}

}

