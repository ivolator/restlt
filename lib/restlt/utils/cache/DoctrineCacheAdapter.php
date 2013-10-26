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
namespace restlt\utils\cache;

use Doctrine\Common\Cache\CacheProvider;
use restlt\exceptions\ApplicationException;
/**
 *
 * @author Vo
 *
 */
class DoctrineCacheAdapter implements CacheAdapterInterface {
	/**
	 *
	 * @var CacheProvider
	 */
	protected $doctrineCache = null;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \restlt\utils\cache\CacheAdapterInterface::__construct()
	 */
	public function __construct($cacheInstance = null) {
		if($cacheInstance && $cacheInstance instanceof CacheProvider){
			$this->doctrineCache = $cacheInstance;
		} else {
			throw ApplicationException::cacheException('This cache provider is not part of Doctrine');
		}

	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \restlt\utils\cache\CacheAdapterInterface::get()
	 */
	public function get($key) {
		return $this->doctrineCache->fetch($key);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \restlt\utils\cache\CacheAdapterInterface::set()
	 */
	public function set($key, $item) {
		return $this->doctrineCache->save($key, $item);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \restlt\utils\cache\CacheAdapterInterface::test()
	 */
	public function test($key) {
		return $this->doctrineCache->delete($key);
	}
}