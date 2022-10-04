<?php

require_once __DIR__ . '/vendor/autoload.php';

use Chaungoclong\SwooleHttpSample\Route;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

$server = new Server('127.0.0.1', 8080);
$route  = new Route(new Request());
$route->addRoute('GET', 'abc/xyz/{a}/{b:hello.*}/[abc]+', function () {
    echo 'hello';
})->name('abc');

$server->on('request', static function (Request $request, Response $response) use ($route) {
    $response->header('Content-Type', 'application/json');
    $res = json_encode($route->getRoutes());
    $response->end($res);
});

$server->start();