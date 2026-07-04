<?php

declare(strict_types=1);

use App\Controller\ImageController;
use App\Controller\IndexController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('index', new Route(
    '/',
    ['_controller' => IndexController::class, '_action' => 'index'],
    [],
    [],
    '',
    [],
    ['GET']
));

$routes->add('v2_categories', new Route(
    '/api/v2/categories',
    ['_controller' => ImageController::class, '_action' => 'listCategories'],
    [],
    [],
    '',
    [],
    ['GET']
));

$routes->add('v2_images_list', new Route(
    '/api/v2/{category}/images',
    ['_controller' => ImageController::class, '_action' => 'listImages'],
    ['category' => '[a-zA-Z0-9_-]+'],
    [],
    '',
    [],
    ['GET']
));

$routes->add('v2_image_random', new Route(
    '/api/v2/{category}/random',
    ['_controller' => ImageController::class, '_action' => 'randomImage'],
    ['category' => '[a-zA-Z0-9_-]+'],
    [],
    '',
    [],
    ['GET']
));

$routes->add('v1_image_random_any', new Route(
    '/api/v1/',
    ['_controller' => ImageController::class, '_action' => 'randomImageAny'],
    [],
    [],
    '',
    [],
    ['GET']
));

$routes->add('v1_image_random_by_category', new Route(
    '/api/v1/{category}',
    ['_controller' => ImageController::class, '_action' => 'randomImage'],
    ['category' => '[a-zA-Z0-9_-]+'],
    [],
    '',
    [],
    ['GET']
));

$routes->add('legacy_categories', new Route(
    '/json',
    ['_controller' => ImageController::class, '_action' => 'listCategories'],
    [],
    [],
    '',
    [],
    ['GET']
));

$routes->add('legacy_images_list', new Route(
    '/json/{category}',
    ['_controller' => ImageController::class, '_action' => 'listAllImages'],
    ['category' => '[a-zA-Z0-9_-]+'],
    [],
    '',
    [],
    ['GET']
));

return $routes;
