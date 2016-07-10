Routers implemented as a PSR-7 middleware.

This package provides 3 routers:

- a "classic" router for web applications
- a REST router for building REST APIs
- a "prefix" router that routes to sub-middlewares based on URL prefixes

## Everything is a middleware

Each router is a middleware, which means it can be used as many times as needed in a middleware stack. That also means that if a router doesn't match the request to a route, it's not an error: it will simply call the `$next` middleware.

Additionally, *controllers (aka route handlers) are also middlewares*. Routers are simply routing the flow of the request to a sub-branch of middlewares. That allows to remove the specific concept of "route middlewares".

Here is an example of using several routers in one application, as well as using middlewares in routes:

```php
use Stratify\Http\Middleware\MiddlewarePipe;
use Stratify\Router\PrefixRouter;
use Stratify\Router\Router;

$app = new MiddlewarePipe([
    new ErrorHandlerMiddleware(), // a middleware that applies to the whole stack
    
    new PrefixRouter([
    
        // The blog has its own router
        '/blog/' => new Router([
            '/blog/' => new HomeController(),
            '/blog/{article}' => new ArticleController(),
        ]),
    
        // The API has its own router with its own middlewares (authentication for example)
        '/api/' => new MiddlewarePipe([
            new HttpBasicAuthMiddleware(), // authentication only for the API
            new Router([
                '/api/articles' => new ArticleController(),
                '/api/users' => new UserController(),
            ]),
        ]),
        
    ]),
]);
```

Please keep in mind this example is over-simplified. It's perfectly possible to benefit from dependency injection, lazy instantiation of controllers/middlewares as well as a simpler API for configuring the application; that's what the [Stratify framework](https://github.com/stratifyphp/stratify) brings on top of the router.

## Installation

```
composer require stratify/router
```

## Classic router

This "classic" router is very similar to other PHP routers. It is built on top of [Aura.Router](https://github.com/auraphp/Aura.Router).

The router takes a map of URLs to callables, for example:

```php
$router = new Router([
    '/' => function (...) {
        ...
    },
]);
```

By default only the HTTP `GET` method will be allowed.

Placeholders can be used in route paths and fetched from request attributes or injected as parameters:

```php
    '/article/{id}' => function ($id) {
        ...
    },
    '/category/{id}' => function (ServerRequestInterface $request) {
        $id = $request->getAttribute('id');
        ...
    },
```

Routes can be configured in more details using the `route()` helper.

- placeholder formats (using a regex):

    ```php
    use function \Stratify\Router\route;
    
    $router = new Router([
        '/{id}' => route(function () { … })
            ->pattern('id', '\d+') // the placeholder must be a number
    ]);
    ```

- optional placeholders:

    ```php
        '/export.{format}' => route(/* callable */)
            ->optional('format', 'json'),
    ```

- accepted HTTP methods:

    ```php
        '/subscribe' => route(/* callable */)
            ->method('POST'),
    ```

### HTTP resources

You can define separate handlers for each HTTP method using the `resource()` helper:

```php
use function \Stratify\Router\resource;

$router = new Router([
    '/' => resource([
        'get' => function () { … },
        'post' => function () { … },
    ]),
]);
```

However if you plan on using all HTTP methods you might want to use the `RestRouter` instead.

### Controllers

Controllers, aka "route handlers", can be any [PHP callables](http://php.net/manual/en/language.types.callable.php) (closures, object methods, invokable objects, …).

The callable can decide which parameters it will take. The router will detect what to provide based on the callable's parameters. Parameters can be:

- the PSR-7 request: `ServerRequestInterface $request`; in that case the parameter *must be named `$request`*
- the PSR-7 response: `ResponseInterface $response`; in that case the parameter *must be named `$response`*
- any request attribute, this includes:
    - route placeholders: `/order/{orderId}` => you can have a `$orderId` parameter (parameter names must be identical to the route placeholder name)
    - or any attribute defined by a previous middleware (e.g. if an authentication middleware defines a `user` attribute, you can have a `$user` parametre)
- the next callable to call (`$next` parameter), see below

Since a controller/route handler *is* a middleware, it can also have the middleware signature:

```php
function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
}
```

#### Controller responses

Controllers, like middlewares, are expected to return PSR-7 response objects.

However, in order to ease development, they can also return strings: those will be automatically turned into `200` HTTP responses.

```php
function () {
    return 'Hello world!';
}

// Same as
function (ResponseInterface $response) {
    $response->getBody()->write('Hello world!');
    return $response;
}
```

## REST router

The REST router behaves exactly like the classic router (the same rules applies for controllers) except that it allows to register handlers more easily for HTTP resources.

```php
$router = new RestRouter([
    '/articles' => new ArticleController,
]);
```

This is equivalent to this mapping with the classic router:

```php
$router = new RestRouter([
    '/articles' => resource([
        'get' => [new ArticleController, 'index'],
        'post' => [new ArticleController, 'post'],
    ]),
    '/articles/{id}' => resource([
        'get' => [new ArticleController, 'get'],
        'put' => [new ArticleController, 'put'],
        'delete' => [new ArticleController, 'delete'],
    ]),
]);
```

Here is an example of a REST controller:

```php
class ArticleController
{
    public function index()
    {
        return new HtmlResponse('Index');
    }

    public function post()
    {
        return new HtmlResponse('POST');
    }

    public function get(ServerRequestInterface $request)
    {
        return new HtmlResponse('GET '.$request->getAttribute('id'));
    }

    public function put(ServerRequestInterface $request)
    {
        return new HtmlResponse('PUT '.$request->getAttribute('id'));
    }

    public function delete(ServerRequestInterface $request)
    {
        return new HtmlResponse('DELETE '.$request->getAttribute('id'));
    }
}
```

## Prefix router

The `PrefixRouter` is a very simple and very fast router that routes based on URL prefixes.

```php
$router = new PrefixRouter([
    '/api/' => /* API middleware stack */,
    '/admin/' => /* Admin middleware stack */,
    '/' => /* Public website middleware stack */,
]);
```

The first prefix to match is used. Each route handler must be a middleware, i.e. a callable whose signature matches:

```php
function (ServerRequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface {
}
```

The prefix router is useful to separate several parts of a large application that do not need the same middlewares, for example:

- authentication required for all the "admin" part, but no authentication on the public website
- cache for the public website but no caching in the backend
- content negotiation and token authentication for an API, but not for the rest of the application
- etc.
