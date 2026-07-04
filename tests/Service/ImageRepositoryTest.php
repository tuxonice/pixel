<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\CategoryNotFoundException;
use App\Service\ImageRepository;
use PHPUnit\Framework\TestCase;

class ImageRepositoryTest extends TestCase
{
    private string $root;
    private ImageRepository $repo;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/pixel_test_' . uniqid();
        $this->repo = new ImageRepository($this->root, 'http://localhost');
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->root);
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

    private function makeCategory(string $name, array $files = []): void
    {
        $dir = $this->root . '/' . $name;
        mkdir($dir, 0755, true);

        foreach ($files as $file) {
            file_put_contents($dir . '/' . $file, '');
        }
    }

    public function testGetCategoriesReturnsEmptyWhenNoneExist(): void
    {
        mkdir($this->root, 0755, true);

        $this->assertSame([], $this->repo->getCategories());
    }

    public function testGetCategoriesReturnsSortedDirectoryNames(): void
    {
        $this->makeCategory('cats');
        $this->makeCategory('dogs');

        $categories = $this->repo->getCategories();

        $this->assertContains('cats', $categories);
        $this->assertContains('dogs', $categories);
        $this->assertCount(2, $categories);
    }

    public function testCategoryExistsReturnsTrueForExistingCategory(): void
    {
        $this->makeCategory('cats');

        $this->assertTrue($this->repo->categoryExists('cats'));
    }

    public function testCategoryExistsReturnsFalseForMissingCategory(): void
    {
        mkdir($this->root, 0755, true);

        $this->assertFalse($this->repo->categoryExists('missing'));
    }

    public function testGetAllImagesReturnsAllFiles(): void
    {
        $this->makeCategory('cats', ['a.jpg', 'b.png', 'c.gif', 'ignore.txt']);

        $result = $this->repo->getAllImages('cats');

        $this->assertSame('cats', $result['category']);
        $this->assertSame(3, $result['total']);
        $this->assertCount(3, $result['images']);
    }

    public function testGetAllImagesThrowsForMissingCategory(): void
    {
        mkdir($this->root, 0755, true);

        $this->expectException(CategoryNotFoundException::class);

        $this->repo->getAllImages('missing');
    }

    public function testGetImagesReturnsPaginatedResult(): void
    {
        $this->makeCategory('cats', ['a.jpg', 'b.jpg', 'c.jpg', 'd.jpg', 'e.jpg']);

        $result = $this->repo->getImages('cats', 1, 2);

        $this->assertSame(1, $result['page']);
        $this->assertSame(2, $result['per_page']);
        $this->assertSame(5, $result['total']);
        $this->assertSame(3, $result['total_pages']);
        $this->assertCount(2, $result['images']);
    }

    public function testGetImagesSecondPage(): void
    {
        $this->makeCategory('cats', ['a.jpg', 'b.jpg', 'c.jpg']);

        $result = $this->repo->getImages('cats', 2, 2);

        $this->assertCount(1, $result['images']);
    }

    public function testGetImagesThrowsForMissingCategory(): void
    {
        mkdir($this->root, 0755, true);

        $this->expectException(CategoryNotFoundException::class);

        $this->repo->getImages('missing', 1, 20);
    }

    public function testGetRandomImageFileReturnsValidStructure(): void
    {
        $this->makeCategory('cats', ['photo.jpg']);

        $result = $this->repo->getRandomImageFile('cats');

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('mime', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertSame('photo.jpg', $result['filename']);
        $this->assertSame('image/jpeg', $result['mime']);
    }

    public function testGetRandomImageFileThrowsWhenCategoryEmpty(): void
    {
        $this->makeCategory('empty');

        $this->expectException(CategoryNotFoundException::class);

        $this->repo->getRandomImageFile('empty');
    }

    public function testGetRandomImageFileThrowsForMissingCategory(): void
    {
        mkdir($this->root, 0755, true);

        $this->expectException(CategoryNotFoundException::class);

        $this->repo->getRandomImageFile('missing');
    }

    public function testGetRandomImageFileFromAnyPicksFromAvailableCategories(): void
    {
        $this->makeCategory('cats', ['photo.png']);

        $result = $this->repo->getRandomImageFileFromAny();

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('mime', $result);
        $this->assertArrayHasKey('filename', $result);
    }

    public function testGetRandomImageFileFromAnyThrowsWhenNoCategoriesExist(): void
    {
        mkdir($this->root, 0755, true);

        $this->expectException(CategoryNotFoundException::class);

        $this->repo->getRandomImageFileFromAny();
    }

    public function testIgnoresNonImageFiles(): void
    {
        $this->makeCategory('cats', ['photo.jpg', 'readme.txt', 'script.php', '.hidden.jpg']);

        $result = $this->repo->getAllImages('cats');

        $this->assertSame(1, $result['total']);
        $this->assertSame('photo.jpg', $result['images'][0]['filename']);
    }

    public function testImageEntryContainsExpectedFields(): void
    {
        $this->makeCategory('cats', ['photo.jpg']);

        $result = $this->repo->getAllImages('cats');
        $image  = $result['images'][0];

        $this->assertArrayHasKey('filename', $image);
        $this->assertArrayHasKey('url', $image);
        $this->assertArrayHasKey('size', $image);
        $this->assertArrayHasKey('mime', $image);
        $this->assertStringContainsString('http://localhost/images/cats/photo.jpg', $image['url']);
        $this->assertSame('image/jpeg', $image['mime']);
    }
}
