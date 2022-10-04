<?php

namespace Chaungoclong\SwooleHttpSample;

use Swoole\Http\Request;

class Route
{
    /**
     * @var array routes
     *    $routes = [
     *      [
     *      'method'  => 'GET | POST',
     *      'pattern' => '/pattern/',
     *      'action'  => 'Callable'
     *      ],
     *      'name' => [
     *      'method'  => 'GET | POST',
     *      'pattern' => '/pattern/',
     *      'action'  => 'Callable'
     *      ]
     *    ];
     */
    private array   $routes;
    private Request $request;
    private         $recentRouteIndex;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string      $method
     * @param string      $uri
     * @param             $action
     * @param string|null $name
     *
     * @return $this
     */
    public function addRoute(string $method, string $uri, $action, string $name = null): Route
    {
        // Parse pattern from uri
        $uri     = filter_var($uri, FILTER_SANITIZE_URL);
        $uri     = trim($uri, '/');
        $uri     = preg_replace('/\//', '\\/', $uri);
        $uri     = preg_replace('/\{(\w+)\}/', '(?<$1>.+)', $uri);
        $uri     = preg_replace('/\{(\w+)\:(.+)\}/', '(?<$1>$2)', $uri);
        $pattern = "/^$uri$/i";

        if (!(is_string($action) || is_array($action) || is_object($action))) {
            throw new \RuntimeException("Invalid action");
        }

        $route = [
            'method'  => $method,
            'pattern' => $pattern,
            'action'  => $action,
        ];

        if ($name !== null) {
            $this->routes[$name]    = $route;
            $this->recentRouteIndex = $name;
        } else {
            $this->routes[]         = $route;
            $this->recentRouteIndex = key(array_slice($this->routes, -1, 1, true));
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function name(string $name): void
    {
        $this->routes[$name] = $this->routes[$this->recentRouteIndex];
        unset($this->routes[$this->recentRouteIndex]);
    }

    protected function parseUri(): string
    {
        $uri = filter_var($this->request->server['request_uri'], FILTER_SANITIZE_URL);

        return trim($uri, '/');
    }

    public function createUrlFromRoute(string $routeName, array $params = []): string
    {
        $route = $this->getRoute($routeName);

    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function getRoute(string $routeName): ?array
    {
        return $this->routes[$routeName] ?? null;
    }
}