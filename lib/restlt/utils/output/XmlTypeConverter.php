<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Ivo Mandalski
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace restlt\utils\output;

use restlt\Result;

class XmlTypeConverter implements TypeConversionStrategyInterface {

	/**
	 *
	 * @var \DOMDocument
	 */
	protected $dom = null;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \restlt\utils\output\TypeConversionStrategyInterface::execute()
	 */
	public function execute(Result $data) {
		$this->dom = new \DOMDocument ( '1.0', 'UTF-8' );
		$domEl = $this->fromMixed ( $data );
		$this->dom->appendChild ( $domEl );
		$this->dom->formatOutput = true;
		echo $this->dom->saveXML ();
	}

	/**
	 *
	 * @param array $mixed
	 * @param \DOMElement $domEl
	 * @return DOMElement
	 */
	function fromMixed($mixed,\DOMElement $domEl = null) {
		if (! $domEl) {
			$domEl = $this->dom->createElement ( 'result' );
		}

		foreach ( $mixed as $name => $value ) {
			if (is_numeric ( $name )) {
				$name = $domEl->tagName;
			}
			if (is_scalar ( $value )) {
				$el = $this->dom->createElement ( ( string ) $name, $value );
				$domEl->appendChild ( $el );
			} elseif(is_object($value)){
				$name = get_class($value);
				$el = $this->dom->createElement ( ( string ) $name );
				$el = $this->fromMixed ( $value, $el );
				$domEl->appendChild ( $el );
			} elseif(is_array($value)) {
				$el = $this->dom->createElement ( ( string ) $name );
				$el = $this->fromMixed ( $value, $el );
				$domEl->appendChild ( $el );
			}
		}

		return $domEl;
	}
}