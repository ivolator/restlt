##A "thin" REST-full server implementation in PHP 
RestLite is a flexible small library that will allow you to build RESTfull service.
Most of the sub-components are extensible or replaceable. This would let you modify the server's behavior.
The current version is still in alpha. Any feedback is appreciated!

###Basic usage (server end point setup)
```php
//Get instance of the server by passing the server base url
$s = new Server ( '/' );
//Tell the server where to find the resources
$s->registerResourceFolder ( SOME_APPLICATION_ROOT .  '/resources', 'name\space\resources' );
$s->registerResourceClass('example\name\space\Resource');
$s->serve ();
``` 
In your base server directory add an .htaccess file with the follwing content
```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php?_url=/$1 [QSA,L]
</IfModule>
```
Now you have bootstraped RestLite.

###Creating a resource
 
 A resource in Rest Lite is a class that extends the \restlt\Resource class.
 Each resource can contain methods that respond to multiple GETs, POSTs, PUTs, DELETEs and PATCHs.
 It is important to mention that when you are registering resources with the server you are either registering a resource folder and prviding a namespace or a single resource by providing the FQNS to resource class. Providing a FQNS (fully qualified name space) is necessary.
 
 To configure the URI for the resource, you need to setup couple of things.
 All the metadata is configurable via the PHP doc blocks. Examples are shown later.
 
 1. Add the @resourceBaseUri in the class doc block.This will set up the base URI for all methods that will be contained in this resource
    '@resourceBaseUri /user' - note the forward slash at the begining
 2. Add @method to the doc block of the class method. This will tell the server what HTTP method this function will be responding to. For example, `@method POST` or `@method GET` will tell the server that the HTTP method is GET or POST
 3. Add @methodUri value for the method URI. 
    * `@methodUri /` - you can either just add the "/" or omit the annotation 
    * `@methodUri /list` - hard coded example
    * `@methodUri /user/([0-9]+)` - regex example - always add the '()' around the regex
    Note that the full URI for your resource is a combination of the server base URI, that was set during \restlt\Server initialization and the addition of @resourceBaseUri + @methodUri. In general consider the URI as of a regular expression. This is how it is evaluated and followint the preg_match() rules for binding its third parameter, whatever you surround with "()" ends up as a parameter of your method.

For example, if your complete URI ends up to be /account/([0-9]+)/contact/([a-z]+), your method should be accepting 2 parameters. The first of which will be a sequence of digits and the second one letters.
 
A resource can have multiple methods that respond to GET, POST, etc.
 
###A simple resource example
```php
namespace restlt\examples\resources;
/**
 * @resourceBaseUri /resource1
 */
class Resource1 extends \restlt\Resource {
    public function __construct(\restlt\Request $request, \restlt\Response $response) {
        parent::__construct($request,$resource);
        $f1 = function ($r) {
        };
        $f2 = function ($req,$res,$ret) {
        };
        $this->on ( 'before', 'getMe', $f1 );
        $this->on ( 'after', 'getMe', $f2 );
    }
    /**
     * Note that the regex in "()" gets converted to a parameter of the method
     * @method GET
     * @methodUri /([0-9]+)
     */
    public function getMe($id = '') {
        $obj = new \stdClass ();
        $obj->a = array (9,8,7);
        //obtain the "someParam" - from POST or GET
        $this->request->get ( 'someParam',$defaultValueIfParamIsMissing );
        return $boj;
    }
    /**
     *
     * @method PUT
     * @methodUri /save
     */
    public function putMe() {
        //fetch the parameters from the query string
        $params = $this->request->getQueryParams ();
        $params = $this->request->getPostParams ();
        
        //get the raw data
        $postPayload= $this->request->getRawPost ();
            ......
        //return whatever you want
        return $res;
    }
}
```
 For the just coded resource we have created two methods, `Resource1::getMe()` and `Resource1::putMe()`. There is no naming convention for the methods. The names are chosen for better clarity. The method Resource1::get() we will be responding to the 'GET' http method and the URI that will access it is /resource1/123 or any number as per the regex. The latter URI will be prepended with the Server base URI. You set the Server base URI when instantiating the \restlt\Server.

Similar to the 'GET' we have built the 'PUT' method  `Resource1::putMy()`. When the server receives a 'PUT'  request and the URI is /resource1/save the  `Resource1::putMe()` method will respond with whatever you decide to return.
The methods must return data in order for you to receive it as a XML or JSON formatted string at the client.
The JSON or XML conversion happens automatically. More on adding your own twist to the output will be discussed in the advanced section.
 
## Some advanced usage
### Event Hooks
Let's say you need to add some twist to the execution flow or put some checks, logging or whatever comes to mind. There are three event hooks that provide a way to do that. The callbacks provided to these event hooks need to be Callable. The method or function signatures are provided bellow.

### On Before event - * before the flow enters your resource method. *

```php
class Resource1 extends \restlt\Resource {
    public function __construct() {
        $f1 = function ($r) {
             //do something
        };
        $this->on ( Resource::ON_BEFORE, 'myFunctionName', $f1 );
    }
```
or instead of a closure, pass a Callable
```php
    $this->on ( Resource::ON_BEFORE, 'myFunctionName', array($obj,$method) );
    }
```
The callback function provided for this event has the following signature
```php
    /**
     * @param \restlt\Request $r 
     * @return void
     * /
    $f = function (\restlt\Request $r){};
```
### On After event - * after the resource function returns *
```php
    $this->on ( Resource::ON_AFTER, 'myFunctionName', array($obj,$method) );
    ```
    * The callback function provided for this event has the following signature *
    ```php
    /**
     * @param \restlt\Request $request 
     * @param \restlt\Response $response
     * @param mixed $return the result of your resource method
     * @return void
     * /
    $f = function (\restlt\Request $request, \restlt\Response $response, $return){};
```
### On error - * when an error occurs inside the method *
If you throw an exception within a method it will get eventually caught in the top layer and you'll get 500. This event actually is triggered when E_ERROR,E_USER_ERROR,E_WARNING,E_USER_WARNING,E_CORE_ERROR,E_CORE_WARNING,E_DEPRECATED,E_STRICT are thrown.
```php
    $this->on ( Resource::ON_ERROR, 'myFunctionName', array($obj,$method) );
```
Follows the callback function signature
```php
/**
* @param \restlt\Request $r 
* @param \restlt\Response $r
* @return \Exception $exception - the result of your resource method
* @return void
* /
$f = function (\restlt\Request $request, \restlt\Response $response,\Exception $exception){};
```
## register event examples: 
### Register ON_BEFORE event hook for a specific resource method
```php
        $f1 = function ($r) {
        //do something
        };
        //the context here is the Resource, hence $this
        $this->on (\restlt\Resource::ON_BEFORE, 'getMe', $f1 );
```
### Register ON_BEFORE event hook for ANY resource method
```php
        $f1 = function ($r) {
        //do something
        };
        //the context here is the Resource, hence $this
        $this->on (\restlt\Resource::ON_BEFORE, NULL, $f1 );
```
## Adding some cache
Since the addition of a great amound of resources could cost us in performance, RestLite uses some caching to aleviate this issue.
In it's most basic implementation the server supports natively Memcached extention. However there are ways to add third party caching systems that are already supporting multitude of backend cache adapters.
The cache is stores the metadata used to resolve the resource routes.
### Using built in Memcached implementation
```php
    $memcached = new Memcached ();
    $memcached->addServer ( 'localhost', 11211 );
    $s = new Server ( '/' );
    $s->setCacheAdapter ( new \restlt\utils\cache\RestltMemcachedAdapter( $memcached ) );
    $s->serve ();
```
### Using Zend Cache component - [Zend Cache] http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html)
If you are already using ZF2 caching component there is an easy way to add it to RestLite.
Here assuming that you know how to use `Zend\Cache\StorageFactory::adapterFactory` you need to obtain a
StorageAdapter. 
```php
    $zendCache = Zend\Cache\StorageFactory::adapterFactory('apc',$options);
    $s->setCacheAdapter ( new \restlt\utils\cache\ZFCacheAdapter ( $zendCache ) );
```

### Using Doctrine's cache implementation - [Doctrine Cache](http://docs.doctrine-project.org/en/latest/reference/caching.html)
    Here you should know how to obtain a Doctrine CacheProvider.
```php
    $memcache = new Memcache();
    $memcache->connect('memcache_host', 11211);
    $doctrineCacheProvider = new \Doctrine\Common\Cache\MemcacheCache();
    $doctrineCacheProvider->setMemcache($memcache);
    $s->setCacheAdapter ( new \restlt\utils\cache\DoctrineCacheAdapter($doctrineCacheProvider) );
```
### Addig your favorite cache implementation
In order for us to be able to use a third party Cache library we need to create a class that implements `restlt\utils\cache\CacheAdapterInterface`
```php
class OtherframeworkCacheAdapter implements CacheAdapterInterface {
        public function __construct($cacheInstance = null) {
                // 
        }
        public function test($key) {
                //
        }

        public function set($key, $item) {
                //
        }

        public function get($key) {
                //
        }
}
```
Now you have to tell the server to use it in your bootsrap routine.
```php
$s->setCacheAdapter ( new \restlt\utils\cache\OtherfameworkCacheAdapter($otherframeworkInstance) );
```

##Adding your own annotations to the Resource methods or the Resource classes
If you need to lock some data needed for processing during request execution you can add a custom annotation to your methods.
Here is an example how to you could use that feature.
```php
/**
 * @method POST
 * @baseUri /save
 * @allowedRoles admin, mega-admin
 */
 public function saveUser(){
     $roles = $this->annotations->get('allowedRoles');
     //do something here with the data. $roles now has the string 'admin, mega-admin'
     return $something;
 }
```
## In need for custom output?
Out of the box RestLite comse with json and xml output strategies. 
Let's say you need to provide some home grown obfuscated or even encrypted reponse. 
For whatever the reason is, you might want to do that one day.
It could be that you want to communicate with the client via some specific protocol and want to wrap the data in it. 
Or may be want to change the current ones. Here is how.
###Adding a custom response of your own.
First we need to create a class that implements the `\restlt\utils\output\TypeConversionStrategyInterface`. Let's start.
```php
namespace my\name\space;
class SerializeOutputStrategy implements \restlt\utils\output\TypeConversionStrategyInterface {
    /**
	 * @see \restlt\utils\output\TypeConversionStrategyInterface::execute()
	 */
	public function execute(\restlt\Result $data) {
		return serialize($data);
	}
}
```

That's it. We have implemented a serializer sutput strategy for our RestLite server.
Next on the list to make this work is to tell the server about it.
```php
$s->getResponse()->addResponseOutputStrategies('sphp', '\my\name\space\SerializeOutputStrategy');
```
What we did here is the follwoing. We let the server know that if we encounter '.sphp' extension in our URL we will respond with the associated output, in this case `SerializeOutputStrategy`.
Now all of your request ( GET, POST, PUT, PATCH ...) with URLs such like:
 * `http://example.com/my/path/123.sphp` 
 * `http://example.com/my/path.sphp?q=stuff` 
 
etc. will respond with serialized data accoring to your new output strategy.

###Extending the currently available `\restlt\utils\output\XmlTypeConverter` and `\restlt\utils\output\JsonTypeConverter`.
To modify the behaviour of the current JSON or XMl converters, you will need to extend them. There is not much to remember here. Extending the class is nothing different than what you do with any other class you do extend.
However you need to register your new class with the server and associate it with the XML or JSON types.
```php
$s->getResponse()->addResponseOutputStrategies('xml', '\my\name\space\MyXmlStrategy');
```
Now all requests of the kind `http://example.com/my/path.xml?q=stuff` will be processed by your new class.
Also,client requests that are made with 'Content-type:application/xml' will be processed by it too. 
the same goes if you decide to extend the json converter.


##Doc block Meta reference

|Annotation          |   Where    |  Required | Values |
|--------------------|:----------:|----------:|--------|
|  @resourceBaseUri  | CLASS      | YES       | string |
|  @baseUri          | METHDO     | NO        | string/ default '/' |
| @cacheControlMaxAge| METHOD     | NO | 
| @method            | METHOD     | YES|
 

### Resource class doc block
    @resourceBaseUri -> specifies the resource base URI relative to the Server base URI
### Resource method doc block
    @method -> specifies what HTTP method this resource method respnds to. POST, GET, PUT, PATCH, DELETE are allowed
    @methodBaseUri -> URI relative to the resource base uri value specified in the @resourceBaseUri
    @cacheControlMaxAge -> this value directly affects the 'Cache-Control max-age' HTTP header value and has nothing to do with the local caching feature    
## Misc. usage tricks    
### Forcing the server to respond always with spcified reponse type regardles of the request `Content-type`
The default behavior is specified by the Accept header. If the 'Accept' is plain/text or anything that does not refer to SML of JSON the
default response will be returned in JSON.
1. Force response type for all Resources registered with the server to respond with XML or JSON
```php
$s = new \restlt\Server('base/uri');
$s->getResponse()->setForceResponseType(Response::APPLICATION_JSON);
```

2. Forcing a method to always respond with XML or JSON.
```php
/**
 * @resourceBaseUri /entity
 */
class myResource extends \restlt\Resource
/**
 * @methodUri /submit
 * @method POST
 */
public function myMethod(){
    $this->getResponse()->setForceResponseType(\restlt\Response::APPLICATION_XML);
    #some code here ...
    return $result;
}
```
3. Non-coding trick to force desired response
    When creating the URL for the client simply append to the end of the URI (not the  query part) .json or .xml
    * 'GET' request - http://example.com/user/list.json?p=1&q=sam
    * 'POST' request - http://example.com/user.json
    * 'GET' request - http://example.com/user/103.xml - get user resource with XML. not te integer 103 will be preserved as part of the URI and will be passed along to your method if it was defined as a regex in the meta for the resource method