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

use restlt\exceptions\ApplicationException;
use restlt\exceptions\SystemException;

/**
 *
 * @todo
 *
 *
 *
 *
 * @author Vo
 *
 */
class ZFCacheAdapter implements CacheAdapterInterface {

	/**
	 *
	 * @var StorageInterface
	 */
	protected $zendCache = null;

	/**
	 *
	 * @param string $cacheInstance
	 */
	public function __construct($cacheInstance = null) {
		$cacheInstance && ($ifaces = class_implements ( $cacheInstance, true ));
		if ($cacheInstance && in_array ( 'Zend\Cache\Storage\StorageInterface', $ifaces )) {
			$this->zendCache = $cacheInstance;
		} else {
			throw ApplicationException::cacheException ( 'You need to pass an instance implementin StorageInterface' );
		}
	}

	/**
	 *
	 * @return boolean
	 */
	public function test($key) {
		try {
			return $this->zendCache->hasItem ( $key );
		} catch ( \Exception $e ) {
			throw ApplicationException::cacheException( $e->getMessage (), $e->getCode (), $e );
		}
	}

	/**
	 *
	 * @param string $key
	 * @param mixed $item
	 * @return boolean
	 */
	public function set($key, $value) {
		try {
			return $this->zendCache->setItem ( $key, $value );
		} catch ( \Exception $e ) {
			throw ApplicationException::cacheException ( $e->getMessage (), $e->getCode (), $e );
		}
	}

	/**
	 *
	 * @return mixed
	 */
	public function get($key) {
		try {
			return $this->zendCache->getItem ( $key );
		} catch ( \Exception $e ) {
			throw ApplicationException::cacheException  ( $e->getMessage (), $e->getCode (), $e );
		}
	}
}
