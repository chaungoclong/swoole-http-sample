<?php

namespace Chaungoclong\SwooleHttpSample;

use Chaungoclong\SwooleHttpSample\Exceptions\RouteNotFoundException;
use InvalidArgumentException;
use RuntimeException;
use Swoole\Http\Request;

class Route
{
    private const REQUEST_METHOD = ['GET', 'POST'];

    /**
     * @var array routes
     */
    private array $routes;

    /**
     * @var \Swoole\Http\Request $request
     */
    private Request $request;

    /**
     * @var string|int $routeIndexJustAdded
     */
    private $routeIndexJustAdded;

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
        // Check request method
        $method = trim($method);
        if (!in_array($method, self::REQUEST_METHOD)) {
            $message = 'Request Method Not Supported. Request method must be in [' . implode(', ', self::REQUEST_METHOD) . ']';
            throw new InvalidArgumentException($message);
        }
        // Sanitize the uri
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        $uri = trim($uri, '/');
        // Parse pattern from uri
        $pattern = $this->parsePattern($uri);
        // Action only accept type string, array, and object
        if (!is_string($action) && !is_array($action) && !is_object($action)) {
            throw new InvalidArgumentException('Action accept only string, array, and object types');
        }
        // If action is array then it must have two elements
        if (is_array($action) && count($action) !== 2) {
            throw new InvalidArgumentException('Action array must have two elements');
        }
        // Add new route
        $route                     = [
            'name'    => $name,
            'method'  => $method,
            'uri'     => $uri,
            'pattern' => $pattern,
            'action'  => $action,
        ];
        $this->routes[]            = $route;
        $this->routeIndexJustAdded = key(array_slice($this->routes, -1, 1, true));

        return $this;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    protected function parsePattern(string $uri): string
    {
        $pattern = preg_replace('/\//', '\\/', $uri);
        $pattern = preg_replace_callback('/\{\s*(\w+\??)\s*\}/', static function ($match) {
            if (substr($match[1], -1) === '?') {
                $groupName = rtrim($match[1], '?');
                $pattern   = '.?';
            } else {
                $groupName = $match[1];
                $pattern   = '.+';
            }

            return "(?<$groupName>$pattern)";
        }, $pattern);
        $pattern = preg_replace_callback('/\{\s*(\w+)\:([^\}]+)\s*\}/', function ($match) {
            [1 => $groupName, 2 => $pattern] = $match;

            return "(?<$groupName>$pattern)";
        }, $pattern);

        return "/^$pattern$/i";
    }


    public function handle(): void
    {
        $uri = $this->parseUri();
        echo "uri:" . $uri . PHP_EOL;
        if ($uri === 'favicon.ico') {
            return;
        }

        $method = $this->request->getMethod();
        if (!in_array($method, self::REQUEST_METHOD)) {
            throw new RuntimeException("Method '$method' not supported");
        }

        // Match
        foreach ($this->routes as $route) {
            if (!$route['method'] === $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $match)) {
                $params = array_slice(array_unique($match), 1);
                var_dump($params);
                // Get action
                $action = $route['action'];
                switch (true) {
                    case (is_string($action)):
                        $actionSegments = explode('::', $action);
                        $className      = $actionSegments[0];
                        $method         = $actionSegments[1] ?? '__invoke';
                        if (!class_exists($className)) {
                            throw new \RuntimeException('Not found class "' . $className . '"');
                        }
                        $object = new $className();
                        if (!method_exists($object, $method)) {
                            throw new \RuntimeException('Not found method "' . $method . '"');
                        }
                        if ($method === '__invoke') {
                            call_user_func_array($object, $params);
                        } else {
                            call_user_func_array([$object, $method], $params);
                        }
                        break;
                    case (is_array($action)):
                        echo 2;
                        break;
                    case (is_object($action)):
                        break;
                    default:
                        throw new \InvalidArgumentException('Action must be string or array or object');
                }
                return;
            }
        }
        throw new \RuntimeException("Not Found Route");
    }

    /**
     * @return string
     */
    protected function parseUri(): string
    {
        $uri = filter_var($this->request->server['request_uri'], FILTER_SANITIZE_URL);

        return trim($uri, '/');
    }


    /**
     * @param string $name
     *
     * @return void
     */
    public function name(string $name): void
    {
        $this->routes[$this->routeIndexJustAdded]['name'] = $name;
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param string $routeName
     *
     * @return array|null
     */
    public function getRoute(string $routeName): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $routeName) {
                return $route;
            }
        }

        return null;
    }
}