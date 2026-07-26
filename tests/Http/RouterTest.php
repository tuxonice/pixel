<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\Http\Router;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterTest extends TestCase
{
    private RouteCollection $routes;
    private RequestContext $context;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->routes->add('test_route', new Route(
            '/test/{id}',
            ['_controller' => 'TestController', '_action' => 'show'],
            ['id' => '\d+'],
            [],
            '',
            [],
            ['GET']
        ));

        $this->context = new RequestContext();
        $this->context->setMethod('GET');
    }

    public function testMatchReturnsRouteParameters(): void
    {
        $router = new Router($this->routes, $this->context);

        $params = $router->match('/test/42');

        $this->assertSame('TestController', $params['_controller']);
        $this->assertSame('show', $params['_action']);
        $this->assertSame('42', $params['id']);
    }

    public function testMatchThrowsResourceNotFoundForUnknownPath(): void
    {
        $router = new Router($this->routes, $this->context);

        $this->expectException(ResourceNotFoundException::class);

        $router->match('/unknown');
    }

    public function testMatchThrowsMethodNotAllowedForWrongMethod(): void
    {
        $this->context->setMethod('POST');
        $router = new Router($this->routes, $this->context);

        $this->expectException(MethodNotAllowedException::class);

        $router->match('/test/42');
    }

    public function testGenerateProducesUrl(): void
    {
        $router = new Router($this->routes, $this->context);

        $url = $router->generate('test_route', ['id' => '99']);

        $this->assertSame('/test/99', $url);
    }
}
