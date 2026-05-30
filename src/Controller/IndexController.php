<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class IndexController
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function index(Request $request, array $params = []): Response
    {
        $groups = [
            [
                'label'     => 'v2',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path'   => '/api/v2/categories',
                        'url'    => '/api/v2/categories',
                        'desc'   => 'List all available categories',
                    ],
                    [
                        'method' => 'GET',
                        'path'   => '/api/v2/<em>{category}</em>/images',
                        'desc'   => 'List all images in a category',
                        'params' => [
                            ['name' => 'page',     'hint' => 'default: 1'],
                            ['name' => 'per_page', 'hint' => 'default: 20 · max: 100'],
                        ],
                    ],
                    [
                        'method' => 'GET',
                        'path'   => '/api/v2/<em>{category}</em>/random',
                        'desc'   => 'Get a random image from a specific category',
                    ],
                ],
            ],
            [
                'label'     => 'v1 — legacy',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path'   => '/api/v1/<em>{category}</em>',
                        'desc'   => 'Get a random image from a specific category',
                    ],
                    [
                        'method' => 'GET',
                        'path'   => '/api/v1/',
                        'url'    => '/api/v1/',
                        'desc'   => 'Get a random image from any category',
                    ],
                ],
            ],
            [
                'label'     => 'JSON — legacy',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path'   => '/json',
                        'url'    => '/json',
                        'desc'   => 'List all available categories',
                    ],
                    [
                        'method' => 'GET',
                        'path'   => '/json/<em>{category}</em>',
                        'desc'   => 'List all images in a category',
                    ],
                ],
            ],
        ];

        $html = $this->twig->render('index.html.twig', ['groups' => $groups]);

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
