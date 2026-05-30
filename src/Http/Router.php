<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Router
{
    private RouteCollection $routes;
    private RequestContext $context;

    public function __construct(RouteCollection $routes, RequestContext $context)
    {
        $this->routes = $routes;
        $this->context = $context;
    }

    public function match(string $pathInfo): array
    {
        $matcher = new UrlMatcher($this->routes, $this->context);

        return $matcher->match($pathInfo);
    }

    public function generate(string $name, array $parameters = []): string
    {
        $generator = new UrlGenerator($this->routes, $this->context);

        return $generator->generate($name, $parameters);
    }
}
