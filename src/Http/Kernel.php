<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\CategoryNotFoundException;
use App\Exception\RateLimitExceededException;
use App\Service\RateLimiter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

class Kernel
{
    private ContainerBuilder $container;

    public function __construct()
    {
        $this->container = $this->buildContainer();
    }

    public function handle(Request $request): Response
    {
        try {
            $rateLimiter = $this->container->get(RateLimiter::class);
            $rateLimiter->check($request->getClientIp() ?? '0.0.0.0');

            $routes = require dirname(__DIR__, 2) . '/config/routes.php';

            $context = new RequestContext();
            $context->fromRequest($request);

            $router = new Router($routes, $context);
            $params = $router->match($request->getPathInfo());

            $controllerClass = $params['_controller'];
            $action = $params['_action'];

            $controller = $this->container->get($controllerClass);

            return $controller->$action($request, $params);
        } catch (RateLimitExceededException $e) {
            return new JsonResponse(
                ['error' => 'Too Many Requests', 'message' => $e->getMessage()],
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Retry-After' => (string) $e->getRetryAfter()]
            );
        } catch (CategoryNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Not Found', 'message' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (ResourceNotFoundException) {
            return new JsonResponse(
                ['error' => 'Not Found', 'message' => 'Route not found.'],
                Response::HTTP_NOT_FOUND
            );
        } catch (MethodNotAllowedException) {
            return new JsonResponse(
                ['error' => 'Method Not Allowed'],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        } catch (\Throwable $e) {
            return new JsonResponse(
                ['error' => 'Internal Server Error', 'message' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function buildContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $configure = require dirname(__DIR__, 2) . '/config/services.php';
        $configure($container);

        $container->compile();

        return $container;
    }
}
