<?php

declare(strict_types=1);

namespace App\Tests\Exception;

use App\Exception\CategoryNotFoundException;
use PHPUnit\Framework\TestCase;

class CategoryNotFoundExceptionTest extends TestCase
{
    public function testMessageContainsCategoryName(): void
    {
        $e = new CategoryNotFoundException('dogs');

        $this->assertStringContainsString('dogs', $e->getMessage());
    }

    public function testIsRuntimeException(): void
    {
        $e = new CategoryNotFoundException('cats');

        $this->assertInstanceOf(\RuntimeException::class, $e);
    }
}
