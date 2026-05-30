<?php

declare(strict_types=1);

use App\Controller\ImageController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('categories', new Route(
    '/api/categories',
    ['_controller' => ImageController::class, '_action' => 'listCategories'],
    [],
    [],
    '',
    [],
    ['GET']
));

$routes->add('images_list', new Route(
    '/api/{category}/images',
    ['_controller' => ImageController::class, '_action' => 'listImages'],
    ['category' => '[a-zA-Z0-9_-]+'],
    [],
    '',
    [],
    ['GET']
));

$routes->add('image_random', new Route(
    '/api/{category}/random',
    ['_controller' => ImageController::class, '_action' => 'randomImage'],
    ['category' => '[a-zA-Z0-9_-]+'],
    [],
    '',
    [],
    ['GET']
));

return $routes;
