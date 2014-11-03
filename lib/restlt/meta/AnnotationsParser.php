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
 * Annotations parser helper
 *
 * @author Vo
 *
 */
class AnnotationsParser
{

    protected static $instance = null;

    protected function __construct()
    {}

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
        ;
    }

    /**
     * Retruns uri meta for given class
     *
     * @param string $className
     *            - FQN for the class
     * @return array @throw SystemException
     */
    public function getClassMeta($className)
    {
        try {
            $classRefl = new \ReflectionClass($className);
            $classDocComment = $this->parseDocComment($classRefl->getDocComment());
        } catch (\ReflectionException $e) {
            throw new \restlt\exceptions\SystemException($e->getMessage(), $e->getCode(), $e);
        }
        return $classDocComment;
    }

    /**
     *
     * @param string $className
     *            - FQCN
     * @param string $method
     * @return array @throw SystemException
     */
    public function getMethodMeta($className)
    {
        $ret = array();
        try {
            $classRefl = new \ReflectionClass($className);
            foreach ($classRefl->getMethods() as $methodRefl) {
                $res = $this->parseDocComment($methodRefl->getDocComment());
                if ($res) {
                    $res['comment'] = $this->getUserComment($methodRefl->getDocComment());
                    $res['function'] = $methodRefl->getName();
                    $ret[] = $res;
                }
            }
        } catch (\ReflectionException $e) {
            throw new \restlt\exceptions\SystemException($e->getMessage(), $e->getCode(), $e);
        }
        return $ret;
    }

    /**
     *
     * @param unknown_type $doccomment
     */
    public function getUserComment($docComment)
    {
        preg_match_all('#[*]+[ ]+[^@].*\n#', $docComment, $docCommentArr);
        $ret = array_map(function ($el)
        {
            return trim($el, '* ');
        }, $docCommentArr[0]);
        $ret = trim(implode(PHP_EOL, $ret));
        return $ret;
    }

    /**
     *
     * @param string $docComment
     * @param array $whitelist
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function parseDocComment($docComment)
    {
        if (! $docComment) {
            return array();
        }

        $res = preg_match_all('#[ *](@.*)\n#', $docComment, $docCommentArr);
        $res = $docCommentArr[1] ? $docCommentArr[1] : array();
        $res = preg_grep('#^@.*#', $res);
        $ret = array();
        if ($res) {
            foreach (array_values($res) as $value) {
                $line = explode(' ', $value);
                $docName = trim(preg_replace('#^@#', '', array_shift($line)));
                $ret[$docName] = trim(implode(' ', $line));
            }
        }
        return $ret;
    }
}
