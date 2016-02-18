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
namespace restlt\meta;

/**
 *
 * @author Ivo Manadlski
 *
 */
class MetadataBuilder implements MetadataBuilderInterface{

	/**
	 *
	 * @var string
	 */
	const META_CACHE_KEY = 'metakey';

	/**
	 *
	 * @var AnnotationsParser
	 */
	protected $annotationsParser = null;

	/**
	 *
	 * @var restlt\utils\Cache
	 */
	protected $cache = null;

	/**
	 *
	 * @var array
	 */
	protected $resourceClasses = array ();

	/**
	 *
	 * @var unknown
	 */
	protected $cacheKeySalt = '';

	/**
	 *
	 * @var \restlt\Server
	 */
	protected $server = NULL;

	/**
	 *
	 * @param array $resourceFiles
	 */
	public function __construct(\restlt\Server $server, $resourceFiles = array()) {
		$this->resourceClasses = $resourceFiles;
		$this->cacheKeySalt = $server->getName ();
		$this->server = $server;
	}

	/**
	 * Returns an array of the sort:
	 * ["name\space\to\Resource1" => "
	 *  [
	 *  	[
	 * 			["method"]=> "PUT",
     * 			["methodUri"]=>  "/resource1/put",
     * 			["function"]=> "save",
     *  	]
     *  ],
	 * "name\space\to\Resource2" => "
	 *  [
	 *  	[
	 * 			["method"]=> "POST",
     * 			["methodUri"]=>  "/resource2",
     * 			["function"]=> "update",
     * 			["cacheControlMaxAge"]=> 1200,
     *  	]
     *  ]
     *  ]
     *
	 * @return string|multitype:multitype:
	 */
	public function buildMeta() {
		$ret = array();

		if ($this->resourceClasses) {
			foreach ( $this->resourceClasses as $class ) {
				$classMeta = $this->getAnnotationsParser()->getClassMeta ( $class );
				$methodsMeta = $this->getAnnotationsParser()->getMethodMeta ( $class );
				if (! $methodsMeta )
					continue;
                $resourceBaseUri = isset($classMeta['resourceBaseUri']) ? $classMeta['resourceBaseUri'] : '';
				$methodsMeta = array_map ( function (&$el) use($resourceBaseUri) {
					$methodUri = isset ( $el ['methodUri'] ) ? trim ( $el ['methodUri'], '/' ) : '/';
					$el ['methodUri'] = rtrim(rtrim ( $resourceBaseUri, ' /' ) . '/' . $methodUri,'/ ');
					return $el;
				}, $methodsMeta );

				$methodsMeta = array_filter($methodsMeta,function($el){
					return isset($el['method']);
				});
				$ret [$class] = $methodsMeta;
				yield $class => $methodsMeta;
			}

		} else {
		    yield null;
		}
	    if ($this->server->getSelfAutoDocsMeta()) {
            // add selfdocumenting
            $docs = $this->server->getSelfAutoDocsMeta();
            $keys = array_keys($docs);
            $vals = array_values($docs);
    		yield $keys[0] => $vals[0];
        }
	}

	/**
	 */
	public function getResourcesMeta() {
		return $this->buildMeta ();
	}

	/**
	 *
	 * @return the $resourceFiles
	 */
	public function getResourceClasses() {
		return $this->resourceClasses;
	}

	/**
	 *
	 * @param array $resourceFiles
	 */
	public function setResourceClasses($resourceFiles) {
		$this->resourceClasses = $resourceFiles;
		return $this;
	}

	/**
	 *
	 * @return AnnotationsParser $annotationsParser
	 */
	public function getAnnotationsParser() {
		return $this->annotationsParser;
	}

	/**
	 *
	 * @param AnnotationsParser $annotationsParser
	 */
	public function setAnnotationsParser(AnnotationsParser $annotationsParser) {
		$this->annotationsParser = $annotationsParser;
	}

	/**
	 *
	 * @return the $cache
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 *
	 * @param CacheAwareTrait $cache
	 */
	public function setCache($cache) {
		$this->cache = $cache;
	}

	/**
	 *
	 * @return string
	 */
	public function getCacheKey() {
		return $this->cacheKeySalt . self::META_CACHE_KEY;
	}

}
