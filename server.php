<?php

require_once __DIR__ . '/vendor/autoload.php';

use Chaungoclong\SwooleHttpSample\Route;
use Chaungoclong\SwooleHttpSample\Test;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$server = new Server('127.0.0.1', 8081);

$server->on('request', static function (Request $request, Response $response) {
    $response->header('Content-Type', 'application/json');
    $route = new Route($request);
    $route->addRoute('GET', 'abc/xyz/{a}/{b:hello.*}/{c?}', function () {
        echo 'hello';
    })->name('abc');
    $route->addRoute('GET', '/greet/{name}', "Chaungoclong\SwooleHttpSample\Test::greet")->name('post.find');
//    var_dump(1, $route->handle(), $request->server);
    $route->handle();
    $response->end('abc');
});

$server->start();