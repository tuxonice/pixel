<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ImageController;
use App\Exception\CategoryNotFoundException;
use App\Service\ImageRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImageControllerTest extends TestCase
{
    private string $imagesRoot;
    private ImageRepository $repository;
    private ImageController $controller;

    protected function setUp(): void
    {
        $this->imagesRoot = sys_get_temp_dir() . '/pixel_ctrl_' . uniqid();
        mkdir($this->imagesRoot . '/cats', 0755, true);
        file_put_contents($this->imagesRoot . '/cats/a.jpg', 'fake');
        file_put_contents($this->imagesRoot . '/cats/b.png', 'fake');
        file_put_contents($this->imagesRoot . '/cats/c.gif', 'fake');

        $this->repository = new ImageRepository($this->imagesRoot, 'http://localhost');
        $this->controller = new ImageController($this->repository);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->imagesRoot);
    }

    private function removeDir(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = $path . '/' . $entry;
            is_dir($full) ? $this->removeDir($full) : unlink($full);
        }

        rmdir($path);
    }

    public function testListCategoriesReturnsJsonWithCategories(): void
    {
        $request = Request::create('/api/v2/categories', 'GET');

        $response = $this->controller->listCategories($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertContains('cats', $data['categories']);
    }

    public function testListImagesReturnsDefaultPagination(): void
    {
        $request = Request::create('/api/v2/cats/images', 'GET');

        $response = $this->controller->listImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(1, $data['page']);
        $this->assertSame(20, $data['per_page']);
        $this->assertSame(3, $data['total']);
    }

    public function testListImagesClampPerPageToMax100(): void
    {
        $request = Request::create('/api/v2/cats/images?per_page=500', 'GET');

        $response = $this->controller->listImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(100, $data['per_page']);
    }

    public function testListImagesClampPerPageToMin1(): void
    {
        $request = Request::create('/api/v2/cats/images?per_page=0', 'GET');

        $response = $this->controller->listImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(1, $data['per_page']);
    }

    public function testListImagesClampNegativePerPageToMin1(): void
    {
        $request = Request::create('/api/v2/cats/images?per_page=-5', 'GET');

        $response = $this->controller->listImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(1, $data['per_page']);
    }

    public function testListImagesClampPageToMin1(): void
    {
        $request = Request::create('/api/v2/cats/images?page=0', 'GET');

        $response = $this->controller->listImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(1, $data['page']);
    }

    public function testListImagesCustomPagination(): void
    {
        $request = Request::create('/api/v2/cats/images?page=1&per_page=2', 'GET');

        $response = $this->controller->listImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(2, $data['per_page']);
        $this->assertCount(2, $data['images']);
        $this->assertSame(2, $data['total_pages']);
    }

    public function testListAllImagesReturnsAllFiles(): void
    {
        $request = Request::create('/json/cats', 'GET');

        $response = $this->controller->listAllImages($request, ['category' => 'cats']);

        $data = json_decode($response->getContent(), true);
        $this->assertSame('cats', $data['category']);
        $this->assertSame(3, $data['total']);
        $this->assertFalse($data['truncated']);
    }

    public function testRandomImageReturnsBinaryFileResponse(): void
    {
        $request = Request::create('/api/v2/cats/random', 'GET');

        $response = $this->controller->randomImage($request, ['category' => 'cats']);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertContains($response->headers->get('Content-Type'), ['image/jpeg', 'image/png', 'image/gif']);
    }

    public function testRandomImageAnyReturnsBinaryFileResponse(): void
    {
        $request = Request::create('/api/v1/', 'GET');

        $response = $this->controller->randomImageAny($request);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function testListImagesThrowsForInvalidCategory(): void
    {
        $request = Request::create('/api/v2/missing/images', 'GET');

        $this->expectException(CategoryNotFoundException::class);

        $this->controller->listImages($request, ['category' => 'missing']);
    }

    public function testRandomImageThrowsForInvalidCategory(): void
    {
        $request = Request::create('/api/v2/missing/random', 'GET');

        $this->expectException(CategoryNotFoundException::class);

        $this->controller->randomImage($request, ['category' => 'missing']);
    }
}
