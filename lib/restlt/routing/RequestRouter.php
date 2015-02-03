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
namespace restlt\routing;

use restlt\exceptions\ServerException;
use restlt\exceptions\SystemException;
use restlt\exceptions\ApplicationException;

/**
 * Simple routing mechanism
 * Routes the requests to the proper resource
 *
 * @author Ivo
 *
 */
class RequestRouter implements RouterInterface
{

    protected static $routes = array();

    /**
     *
     * @var array
     */
    protected $resources = array();

    /**
     *
     * @var \restlt\Request
     */
    protected $request = '/';

    /**
     *
     * @param string $serverBaseUri
     */
    public function __construct(\restlt\RequestInterface $request, $serverBaseUri = null)
    {
        if ($serverBaseUri) {
            $this->serverBaseUri = $serverBaseUri;
        } else {
            throw new ApplicationException('Base URI must be defined!');
        }
        $this->request = $request;
    }

    /**
     *
     * @param \restlt\Request $request
     * @return \restlt\Route
     * @throws ServerException
     */
    public function getRoute()
    {
        $request = $this->getRequest();

        $method = $request->getMethod();
        $requestUri = $request->getUri();

        if (isset(static::$routes[$requestUri])) {
            return static::$routes[$requestUri];
        }

        $ext = pathinfo($requestUri, PATHINFO_EXTENSION);
        $requestUriStripped = preg_replace('#\.' . $ext . '$#', '', $requestUri);

        $resourceUri = str_replace('//', '/', $requestUriStripped);

        if (\restlt\Request::HEAD === strtoupper($method))
            $method = \restlt\Request::GET;

        $route = $this->matchResource($resourceUri, $method);
        if ($route) {
            static::$routes[$requestUri] = $route;
        }
        if ($ext)
            $route->setOutputTypeOverrideExt($ext);

        return $route;
    }

    /**
     *
     * @param string $uri
     * @param string $method
     *            (POST | GET | PUT | DELETE | PATCH)
     * @return Route
     * @throws ServerException
     */
    public function matchResource($uri, $requestMethod)
    {
        $uri = '/' . trim($uri, '/');
        $serverBaseUri = $this->getServerBaseUri();
        $filterMatchingMethods = function (&$el) use($uri, $requestMethod, $serverBaseUri)
        {
            $ret = false;
            $matches = false;
            if (empty($el['method'])) {
                return false;
            }
            $methodMatch = strtolower($requestMethod) === strtolower($el['method']);
            $methodUri = $serverBaseUri . rtrim($el['methodUri'], '/');
            $methodUri = parse_url($methodUri,PHP_URL_PATH);
            if ($methodMatch) {
                $regex = '#^' . $methodUri . '$#i';
                $pregres = preg_match($regex, $uri, $matches);
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
                break;
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
        $route = $this->createRouteObject();
        $route->setClassName($class);
        $route->setFunctionName($methodMeta['function']);
        $route->setUserAnnotations($methodMeta);
        if (! empty($methodMeta['params'])) {
            $route->setParams($methodMeta['params']);
        }
        if (! empty($methodMeta['cacheControlMaxAge'])) {
            $route->setCacheControlMaxAge($methodMeta['cacheControlMaxAge']);
        }
        return $route;
    }

    /**
     *
     * @return the $resourceFiles
     */
    public function getResource()
    {
        return $this->resources;
    }

    /**
     *
     * @param array $resource
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
        return $this;
    }

    /**
     *
     * @return array $resources
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     *
     * @return string $serverBaseUri
     */
    public function getServerBaseUri()
    {
        return $this->serverBaseUri;
    }

    /**
     *
     * @param string $serverBaseUri
     */
    public function setServerBaseUri($serverBaseUri)
    {
        $this->serverBaseUri = $serverBaseUri;
        return $this;
    }

    /**
     *
     * @return \restlt\Request $request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @param \restlt\Request $request
     */
    public function setRequest(\restlt\RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     *
     * @return \restlt\routing\Route
     */
    public function createRouteObject(){
        return new Route();
    }
}
