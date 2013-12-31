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
namespace restlt\output;

use restlt\Result;
/**
 * Help to convert data to XML and JSON
 * By implementing a conversion strategy you can add a twist to your output
 * @author Vo
 *
 */
class TypeConverter implements TypeConverterInterface{
	protected $conversionStrategy = null;

	public function __construct(TypeConversionStrategyInterface $conversionStrategy){
		$this->setConversionStrategy($conversionStrategy);
	}
	
	/**
	 *
	 * @param Result $data
	 */
	public function convert(Result $data){
		return $this->conversionStrategy->execute($data);
	}

	/**
	 * @return TypeConversionStrategyInterface$conversionStrategy
	 */
	public function getConversionStrategy() {
		return $this->conversionStrategy;
	}

	/**
	 * @param TypeConversionStrategyInterface $conversionStrategy
	 */
	public function setConversionStrategy(TypeConversionStrategyInterface $conversionStrategy) {
		$this->conversionStrategy = $conversionStrategy;
	}

}