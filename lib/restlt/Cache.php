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
namespace restlt;

/**
 * This is meant for internal use only and not any userland caching
 *
 * @todo - convert to trait
 * @author Vo
 *
 */
class Cache
{

    /**
     *
     * @var \restlt\cache\CacheAdapterInterface
     */
    protected $cacheAdapter = null;

    public function __construct(\restlt\cache\CacheAdapterInterface $cacheAdapter = null)
    {
        if ($cacheAdapter) {
            $this->cacheAdapter = $cacheAdapter;
        }
    }

    /**
     *
     * @return boolean
     */
    public function test($key)
    {
        return $this->cacheAdapter->test($key);
    }

    /**
     *
     * @param string $key
     * @param mixed $item
     * @return boolean
     */
    public function set($key, $item, $ttl = 0)
    {
        $data = serialize($item);
        return $this->cacheAdapter->set($key, $data, $ttl);
    }

    /**
     *
     * @return mixed
     */
    public function get($key)
    {
        $ret = $this->cacheAdapter->get($key);
        $ret = unserialize($ret);
        return $ret;
    }

    /**
     *
     * @return restlt\cache\CacheAdapterInterface $cacheAdapter
     */
    public function getCacheAdapter()
    {
        return $this->cacheAdapter;
    }

    /**
     *
     * @param restlt\cache\CacheAdapterInterface $cacheAdapter
     */
    public function setCacheAdapter($cacheAdapter)
    {
        return $this->cacheAdapter = $cacheAdapter;
    }
}