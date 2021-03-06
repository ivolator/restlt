<?php
/**
 * test case.
 */
class AnnotationsParserTest extends \PHPUnit_Framework_TestCase
{
    protected $ap = null;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        $this->ap = $this->getMockBuilder('\restlt\meta\AnnotationsParser')->getMock();
        $this->ap = new \restlt\meta\AnnotationsParser();
    }

    /**
     * @expectedException \restlt\exceptions\SystemException
     */
    public function testGetClassMetaThrowsException(){
        $this->ap->getClassMeta('\fixtureClass1');
    }

    public function testGetClassMeta(){
        $actual = $this->ap->getClassMeta('\fixtureClass');
        $expected = array(
            'resourceBaseUri' => '/myresource',
            'emptyValue' => '',
            'specialValue' => '@#$%^&*\'',
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \restlt\exceptions\SystemException
     */
    public function testGetMethodMetaThrowsException(){
        $this->ap->getClassMeta('\fixtureClass1');
    }

    public function testGetMethodMeta(){
        $actual = $this->ap->getMethodMeta('\fixtureClass');
        $expected =array( array(
            'function' => 'f1',
            'method' => 'GET',
            'methodUri' => '/([0-9]+)',
            'emptyValue' => '',
            'custom' => 'some custom value',
            'comment' => ''
        ));
        $this->assertEquals($expected, $actual);
    }

    public function testPaseDocComment(){
    	
    }
    /**
     * 
     */
    public function testGetUserComment(){
    	$docComment = <<<COMM
    /**
     * We should get this
     * 
     * @method GET
     * @methodUri /resource/uri
     */    	
COMM;
    	$actual = $this->ap->getUserComment($docComment);
    	$this->assertEquals('We should get this', $actual);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $this->ap = null;
        parent::tearDown();
    }
}

/**
 *
 * @resourceBaseUri /myresource
 * @emptyValue
 * @specialValue @#$%^&*'
 *
 */
class fixtureClass{
    /**
     *
     * @method GET
     * @methodUri /([0-9]+)
     * @custom some custom value
     * @emptyValue
     */
    public function f1(){return;}

    /**
     * #nothing should be back
     */
    public function f2(){return;}
}
