<?php
use restlt\output\XmlOtputStrategy;
use restlt\Result;

class XmlOutputStrategyTest extends RestLiteTest {
	
	public function testExecute() {
		$xmlStartegy = new XmlOtputStrategy ();
		$arr = array ('abc' => 22, 'xyz' => 33 );
		$expectedXml = <<<XML
		<result>
		  <data>
		    <abc>22</abc>
		    <xyz>33</xyz>
		  </data>
		  <errors/>
		</result>
XML;
		$data = new Result ();
		$data->setData ( $arr );
		$actualXml = $xmlStartegy->execute ( $data );
		$this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);;
	}

}
