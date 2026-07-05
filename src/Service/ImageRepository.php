<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\CategoryNotFoundException;

class ImageRepository
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    private const MIME_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
    ];

    private const MAX_UNPAGINATED_IMAGES = 1000;

    public function __construct(
        private readonly string $imagesRoot,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getCategories(): array
    {
        $dirs = glob(rtrim($this->imagesRoot, '/') . '/*', GLOB_ONLYDIR);

        if ($dirs === false) {
            return [];
        }

        return array_map('basename', $dirs);
    }

    public function categoryExists(string $category): bool
    {
        return is_dir($this->categoryPath($category));
    }

    /**
     * @return array{category: string, total: int, truncated: bool, images: list<array<string, mixed>>}
     */
    public function getAllImages(string $category): array
    {
        $this->assertCategoryExists($category);

        $files = $this->scanCategory($category);
        $total = count($files);
        $limited = array_slice($files, 0, self::MAX_UNPAGINATED_IMAGES);

        return [
            'category'  => $category,
            'total'     => $total,
            'truncated' => $total > self::MAX_UNPAGINATED_IMAGES,
            'images'    => array_map(fn(string $file) => $this->buildImageEntry($category, $file), $limited),
        ];
    }

    /**
     * @return array{category: string, page: int, per_page: int, total: int, total_pages: int,
     *               images: list<array<string, mixed>>}
     */
    public function getImages(string $category, int $page, int $perPage): array
    {
        $this->assertCategoryExists($category);

        $files = $this->scanCategory($category);
        $total = count($files);
        $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

        $offset = ($page - 1) * $perPage;
        $slice = array_slice($files, $offset, $perPage);

        return [
            'category'    => $category,
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => $totalPages,
            'images'      => array_map(fn(string $file) => $this->buildImageEntry($category, $file), $slice),
        ];
    }

    public function getRandomImage(string $category): string
    {
        $this->assertCategoryExists($category);

        $files = $this->scanCategory($category);

        if (empty($files)) {
            throw new CategoryNotFoundException($category);
        }

        $file = $files[array_rand($files)];

        return $this->buildImageUrl($category, $file);
    }

    /**
     * @return array{path: string, mime: string, filename: string}
     */
    public function getRandomImageFileFromAny(): array
    {
        $categories = $this->getCategories();

        if (empty($categories)) {
            throw new CategoryNotFoundException('any');
        }

        $category = $categories[array_rand($categories)];

        return $this->getRandomImageFile($category);
    }

    /**
     * @return array{path: string, mime: string, filename: string}
     */
    public function getRandomImageFile(string $category): array
    {
        $this->assertCategoryExists($category);

        $files = $this->scanCategory($category);

        if (empty($files)) {
            throw new CategoryNotFoundException($category);
        }

        $file = $files[array_rand($files)];
        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return [
            'path' => $this->categoryPath($category) . '/' . $file,
            'mime' => self::MIME_MAP[$ext] ?? 'application/octet-stream',
            'filename' => $file,
        ];
    }

    /**
     * @return list<string>
     */
    private function scanCategory(string $category): array
    {
        $path = $this->categoryPath($category);
        $files = scandir($path);

        if ($files === false) {
            return [];
        }

        return array_values(array_filter($files, function (string $file) use ($path): bool {
            if (str_starts_with($file, '.')) {
                return false;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            return in_array($ext, self::ALLOWED_EXTENSIONS, true) && is_file($path . '/' . $file);
        }));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildImageEntry(string $category, string $file): array
    {
        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $size = filesize($this->categoryPath($category) . '/' . $file);

        return [
            'filename' => $file,
            'url'      => $this->buildImageUrl($category, $file),
            'size'     => $size !== false ? $size : 0,
            'mime'     => self::MIME_MAP[$ext] ?? 'application/octet-stream',
        ];
    }

    private function buildImageUrl(string $category, string $file): string
    {
        return rtrim($this->baseUrl, '/') . '/images/' . rawurlencode($category) . '/' . rawurlencode($file);
    }

    private function categoryPath(string $category): string
    {
        return rtrim($this->imagesRoot, '/') . '/' . $category;
    }

    private function assertCategoryExists(string $category): void
    {
        if (!$this->isValidCategoryName($category) || !$this->categoryExists($category)) {
            throw new CategoryNotFoundException($category);
        }
    }

    private function isValidCategoryName(string $category): bool
    {
        if ($category === '' || str_contains($category, '/') || str_contains($category, '\\')) {
            return false;
        }

        return !str_contains($category, '..');
    }
}
