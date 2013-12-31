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
namespace restlt\cache;

use restlt\exceptions\ApplicationException;

/**
 * RestLt native cache adapter.
 *
 * @author Vo
 *
 */
class RestltMemcachedAdapter implements CacheAdapterInterface {
	/**
	 *
	 * @var \Memcached
	 */
	protected $mc = null;

	/**
	 * Exp in seconds
	 * Default - does not expire
	 *
	 * @var integer
	 */
	protected $exparation = 0;

	/*
	 * (non-PHPdoc) @see \restlt\cache\CacheAdapterInterface::__construct()
	 */
	public function __construct($cacheInstance = null) {
		if (! $cacheInstance instanceof \Memcached) {
			throw ApplicationException::cacheException ( 'The cache adapter must be of \Memcached type' );
		}
		$this->mc = $cacheInstance;
	}

	/*
	 * (non-PHPdoc) @see \restlt\cache\CacheAdapterInterface::test()
	 */
	public function test($key) {
		$res = $this->mc->get ( $key );
		if ($this->mc->getResultCode () === \Memcached::RES_NOTFOUND) {
			$ret = false;
		}

		if ($res) {
			$ret = true;
		}
		return $ret;
	}

	/*
	 * (non-PHPdoc) @see \restlt\cache\CacheAdapterInterface::set()
	 */
	public function set($key, $item) {
		$this->mc->set ( $key, $item, $this->exparation );
		if ($this->mc->getResultCode () === \Memcached::RES_NOTFOUND) {
			$ret = false;
		}
		return true;
	}

	/*
	 * (non-PHPdoc) @see \restlt\cache\CacheAdapterInterface::get()
	 */
	public function get($key) {
		$ret = $this->mc->get ( $key );
		if ($this->mc->getResultCode () !== \Memcached::RES_SUCCESS) {
			$ret = null;
		}
		return $ret;
	}
}

