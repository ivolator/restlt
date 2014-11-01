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

use restlt\routing\Route;
use restlt\exceptions\ApplicationException;
use restlt\exceptions\ServerException;

/**
 * Un-factored code for php ninjas
 * For those that do not appreciate uncoupled code, I have merged some of
 * the methods from the router used in the general code.
 * This becomes Response / Router
 *
 * @author Vo
 *
 */
class PhpNinjaResponse extends Response implements \restlt\ResponseInterface
{

    protected static $routes = [];

    protected $request = null;

    protected $resources = null;

    protected $serverBaseUri = null;

    public function setResources(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \restlt\ResponseInterface::send()
     */
    public function send()
    {
        $data = null;
        $contentType = null;
        $conversionStrategy = null;
        $route = null;

        $method = $this->getRequest()->getMethod();
        $uri = $this->getRequest()->getUri();
        $ext = pathinfo($uri, PATHINFO_EXTENSION);
        $uri = preg_replace('#\.' . $ext . '$#', '', $uri);
        $resourceUri = str_replace($this->serverBaseUri, '/', $uri);
        $resourceUri = str_replace('//', '/', $resourceUri);
        try {
            if ($this->status === self::OK && $route = $this->getRoute($resourceUri, $method, $ext)) {
                $data = $this->getNinjaRoutedResponse($route);
            }

            $contentType = $this->getRequest()->getContentType();

            if ($this->forceResponseType) {
                $conversionStrategy = $this->getConversionStrategy($this->forceResponseType);
            }
        } catch (\restlt\exceptions\ServerException $e) {
            $this->setStatus($e->getCode());
            $this->displayError = $e;
        }

        if ($route && $route->getOutputTypeOverrideExt()) {
            $conversionStrategy = $this->getConversionStrategy($route->getOutputTypeOverrideExt());
        } elseif ($this->forceResponseType) {
            $conversionStrategy = $this->getConversionStrategy($this->forceResponseType);
        }

        if ($route) {
            $cType = $this->getContentTypeFromRoute($route);
            $contentType = $cType ? $cType : $contentType;
        }

        // everything failed - default to json
        if (! $conversionStrategy) {
            $contentType = self::APPLICATION_JSON;
            $conversionStrategy = $this->getConversionStrategy($contentType);
        }

        if ($contentType) {
            $this->addHeader('Content-Type', $contentType);
        }
        return $this->_send($data ? $data : null, $conversionStrategy);
    }

    /**
     * Un-factored RequestRouter.
     * Be a ninja, don't care!
     *
     * @param unknown $resourceUri
     * @param unknown $requestMethod
     * @param unknown $ext
     * @throws SystemException
     * @return boolean \restlt\routing\Route
     */
    protected function getRoute($resourceUri, $requestMethod, $ext)
    {
        if (isset(static::$routes[$this->getRequest()->getUri()])) {
            return static::$routes[$this->getRequest()->getUri()];
        }

        $filterMatchingMethods = function (&$el) use($resourceUri, $requestMethod)
        {
            $ret = false;
            $matches = false;
            if (empty($el['method']))
                return false;
            $methodMatch = strtolower($requestMethod) === strtolower($el['method']);
            $methodUri = rtrim($el['methodUri'], '/');
            $resourceUri = rtrim($resourceUri, '/');
            // try matching without regex first
            if ($el['methodUri'] === $resourceUri && $methodMatch) {
                $ret = true;
            }
            if (! $ret && $methodMatch) {
                $regex = '#^' . $methodUri . '$#';
                $pregres = preg_match($regex, $resourceUri, $matches);
                if (PREG_NO_ERROR !== preg_last_error()) {
                    throw new SystemException('Regex error when matching the method URIs');
                }
                if ($matches) {
                    $ret = true;
                }
                // all regex surounded by '()' will end up as params to the methods
                array_shift($matches);
                $el['params'] = $matches;
            }
            return $ret;
        };

        $filtered = array();
        foreach ($this->resources as $className => $resMeta) {
            $res = null;
            $res = array_filter($resMeta, $filterMatchingMethods);
            if ($res) {
                $filtered[$className] = $res;
                $class = $className;
            }
            if (count($filtered) > 1) {
                throw ServerException::duplicateRouteFound();
            }
        }

        $cnt = count($filtered);
        if ($cnt == 0) {
            throw ServerException::notFound();
        }

        $methodMeta = array_shift($filtered[$class]);
        $route = new Route();
        $route->setClassName($class);
        $route->setFunctionName($methodMeta['function']);
        $route->setUserAnnotations($methodMeta);
        $route->setOutputTypeOverrideExt($ext);
        if (! empty($methodMeta['params'])){
            $route->setParams($methodMeta['params']);
        }
        if (! empty($methodMeta['cacheControlMaxAge'])){
            $route->setCacheControlMaxAge($methodMeta['cacheControlMaxAge']);
        }

        if($route){
            static::$routes[$this->getRequest()->getUri ()] = $route;
        }
        return $route;
    }

    /**
     * Un-factored code.
     * Be a ninja, don't care!
     *
     * @param RequestRouter $route
     * @param Request $request
     * @param Result $returnValue
     */
    protected function getNinjaRoutedResponse(\restlt\routing\Route $route)
    {
        $ret = null;

        if ($route && $route->getClassName() && $route->getFunctionName()) {
            $class = $route->getClassName();
            $resourceObj = new $class($this->request, $this);
            if ($this->log) {
                $resourceObj->setLog($this->log);
            }
            $params = $route->getParams() ? $route->getParams() : array();
            if (is_callable(array(
                $resourceObj,
                $route->getFunctionName()
            ))) {
                try {
                    $cbs = $resourceObj->getCallbacks();
                    $resourceObj->setAnnotations($route);
                    // before the method was processed
                    $this->executeCallbacks(Resource::ON_BEFORE, $route->getFunctionName(), $cbs, array(
                        $this->request
                    ));
                    register_shutdown_function(array(
                        $this,
                        'shutdown'
                    ));
                    // routed call
                    $ret = call_user_func_array(array(
                        $resourceObj,
                        $route->getFunctionName()
                    ), $params);
                    // after method was processed
                    $this->executeCallbacks(Resource::ON_AFTER, $route->getFunctionName(), $cbs, array(
                        $this->request,
                        $this,
                        $ret
                    ));
                } catch (\Exception $e) {
                    $this->executeCallbacks(Resource::ON_ERROR, $route->getFunctionName(), $cbs, array(
                        $this->request,
                        $this,
                        $e
                    ));
                    $this->setStatus(Response::INTERNALSERVERERROR);
                    $this->executeCallbacks(Resource::ON_AFTER, $route->getFunctionName(), $cbs, array(
                        $this->request,
                        $this,
                        $ret
                    ));
                    $this->displayError = $e;
                }
                $resourceObj->clearCallbacks();
                $this->setUserErrors($resourceObj);
            }
        } else {
            $this->status = $this->status && Response::OK === $this->status ? self::NOTFOUND : $this->status;
        }

        if ($route && $route->getCacheControlMaxAge()) {
            $this->addHeader('Cache-Control', 'private, max-age=' . $route->getCacheControlMaxAge());
        } else {
            $this->addHeader("Cache-Control", "no-cache, must-revalidate");
        }
        return $ret;
    }

    /**
     * Send Response
     *
     * @param string $data
     */
    protected function _send($data = null, $conversionStrategy)
    {
        if (isset($_SERVER['HTTP_CONNECTION'])) {
            header('x-custom-rest-server: RestLt');
            header('Allow: POST, GET, PUT, DELETE, PATCH, HEAD');
            header('Connection: close');
            if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
                http_response_code($this->status);
            } else {
                header("HTTP/1.0 " . $this->status);
            }
            foreach ($this->headers as $header => $value) {
                $hStr = $header . ': ' . $value;
                header($hStr, true, $this->status);
            }
        }
        // prepare the payload if any
        $this->getResultObject()->setHttpStatus($this->status);
        if ($data && $this->request->getMethod() !== Request::HEAD) {
            $this->getResultObject()->setData($data);
            return $this->getResultObject()->toString($conversionStrategy);
        } elseif ($this->displayError) {
            $this->getResultObject()->addError($this->displayError->getMessage(), $this->displayError->getCode());
            return $this->getResultObject()->toString($conversionStrategy);
        }

        $this->getResultObject()->addError('Unknown server error', self::INTERNALSERVERERROR);
        return $this->getResultObject()->toString($conversionStrategy);
    }

    /**
     *
     * @return the unknown_type
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @param unknown_type $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getServerBaseUri()
    {
        return $this->serverBaseUri;
    }

    public function setServerBaseUri($serverBaseUri)
    {
        $this->serverBaseUri = $serverBaseUri;
        return $this;
    }
}
