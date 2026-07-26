<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ImageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class IndexController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ImageRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function index(Request $request, array $params = []): Response
    {
        $categories = $this->repository->getCategories();
        $firstCategory = $categories[0] ?? 'nature';

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
                        'url'    => '/api/v2/' . $firstCategory . '/images',
                        'desc'   => 'List all images in a category',
                        'params' => [
                            ['name' => 'page',     'hint' => 'default: 1'],
                            ['name' => 'per_page', 'hint' => 'default: 20 · max: 100'],
                        ],
                    ],
                    [
                        'method' => 'GET',
                        'path'   => '/api/v2/<em>{category}</em>/random',
                        'url'    => '/api/v2/' . $firstCategory . '/random',
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
                        'url'    => '/api/v1/' . $firstCategory,
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
                        'url'    => '/json/' . $firstCategory,
                        'desc'   => 'List all images in a category',
                    ],
                ],
            ],
        ];
        $rateLimitMax = (int) ($_ENV['RATE_LIMIT_MAX'] ?? 60);
        $rateLimitWindow = (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60);
        $baseUrl = rtrim((string) ($_ENV['APP_BASE_URL'] ?? ''), '/');

        $categoryCounts = [];
        foreach ($categories as $category) {
            $images = $this->repository->getImages($category, 1, 1);
            $categoryCounts[$category] = $images['total'];
        }

        $html = $this->twig->render('index.html.twig', [
            'groups'           => $groups,
            'categories'       => $categories,
            'categoryCounts'   => $categoryCounts,
            'rateLimitMax'     => $rateLimitMax,
            'rateLimitWindow'  => $rateLimitWindow,
            'baseUrl'          => $baseUrl,
            'firstCategory'    => $firstCategory,
        ]);

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
