<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ImageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController
{
    public function __construct(
        private readonly ImageRepository $repository,
    ) {}

    public function listCategories(Request $request, array $params = []): JsonResponse
    {
        return new JsonResponse([
            'categories' => $this->repository->getCategories(),
        ]);
    }

    public function listImages(Request $request, array $params = []): JsonResponse
    {
        $category = $params['category'];
        $page     = max(1, (int) $request->query->get('page', 1));
        $perPage  = min(100, max(1, (int) $request->query->get('per_page', 20)));

        $result = $this->repository->getImages($category, $page, $perPage);

        return new JsonResponse($result);
    }

    public function randomImage(Request $request, array $params = []): Response
    {
        $category = $params['category'];
        $url      = $this->repository->getRandomImage($category);

        return new RedirectResponse($url, Response::HTTP_FOUND);
    }
}
