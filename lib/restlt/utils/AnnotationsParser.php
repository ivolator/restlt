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
namespace restlt\utils;

use restlt\exceptions\SystemException;

/**
 * Annotations parser helper
 *
 * @author Vo
 *
 */
class AnnotationsParser {

	protected $validMethodAnnnotations = array('method', 'methodUri','cacheControlMaxAge');
	protected $validClassAnnotations = array('resourceBaseUri' );

	/**
	 *
	 * @param array $resourceFiles
	 */
	public function __construct($resourceFiles = array()) {
		if ($resourceFiles) {
			$this->resourceFiles = $resourceFiles;
		}
	}

	/**
	 * Retruns uri meta for given class
	 *
	 * @param string $className - FQN for the class
	 * @return array
	 * @throw SystemException
	 */
	public function getClassMeta($className) {
		try {
			$classRefl = new \ReflectionClass ( $className );
			$classDocComment = $this->parseDocComment ( $classRefl->getDocComment ()/* , $this->validClassAnnotations */ );
		} catch (\ReflectionException $e ) {
			throw new SystemException ( $e->getMessage (), $e->getCode (), $e );
		}
		return $classDocComment;
	}

	/**
	 *
	 * @param string $className - FQCN
	 * @param string $method
	 * @return array
	 * @throw SystemException
	 */
	public function getMethodMeta($className) {
		$ret = array();
		try {
			$classRefl = new \ReflectionClass ( $className );
			foreach ( $classRefl->getMethods () as $methodRefl ) {
				$res = $this->parseDocComment ( $methodRefl->getDocComment ()/* , $this->validMethodAnnnotations */ );
				if($res){
					$res['function'] = $methodRefl->getName();
					$ret[] = $res;
				}
			}
		} catch (\ReflectionException $e ) {
			throw new SystemException ( $e->getMessage (), $e->getCode (), $e );
		}
		return $ret;
	}

	/**
	 *
	 * @param string $docComment
	 * @param array $whitelist
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	protected function parseDocComment($docComment, array $whitelist = array()) {
		if (! $docComment) {
			return array();
		}
		$res = preg_match_all('#@.*#', $docComment, $docCommentArr);
		$res = preg_grep ( '#^@.*#', $docCommentArr[0] );
		$ret = array ();
		if ($res) {
			foreach ( array_values ( $res ) as $value ) {
				$line = explode ( ' ', $value );
				$docName = preg_replace ( '#^@#', '', array_shift ( $line ) );
				if (! $whitelist || ($whitelist && in_array ( $docName, $whitelist ))) {
					$ret [$docName] = trim ( implode ( ' ', $line ) );
				}
			}
		}
		return $ret;
	}

	/**
	 * @return the $validClassAnnotations
	 */
	public function getValidClassAnnotations() {
		return $this->validClassAnnotations;
	}

	/**
	 * @param multitype:string  $validClassAnnotations
	 */
	public function addClassAnnotation($classAnnotation) {
		$this->validClassAnnotations[] = $classAnnotation;
	}

	/**
	 * @return the $validMethodAnnnotations
	 */
	public function getValidMethodAnnnotations() {
		return $this->validMethodAnnnotations;
	}

	/**
	 * @param multitype:string  $validClassAnnotations
	 */
	public function addMethodAnnotation($methodAnnotation) {
		$this->validMethodAnnnotations[] = $methodAnnotation;
	}


}
